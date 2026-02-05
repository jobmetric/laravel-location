<?php

namespace JobMetric\Location\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use JobMetric\Location\Models\City;
use JobMetric\Location\Models\Country;
use JobMetric\Location\Models\District;
use JobMetric\Location\Models\Province;
use RuntimeException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Throwable;

class ImportLocationData extends Command
{
    private const BAR_WIDTH = 28;
    private const LABEL_WIDTH = 22;

    /**
     * When command is executed without arguments, it imports all countries.
     * If one or more country keys are provided, it imports provinces/cities/districts for those keys.
     *
     * Data is read from package JSON files under:
     * - database/data/countries.json (master list)
     * - database/data/{country_key}/data.json (preferred; optional)
     * - database/data/{country_key}/subdivisions.json (legacy; optional)
     */
    protected $signature = 'location:import
                            {country?* : Country key(s) (e.g. ir, tr) to import provinces/cities/districts for}
                            {--data-path= : Override base data directory (defaults to package database/data/)}
                            {--force : Update existing records (otherwise only create/restore)}';

    protected $description = 'Import location base data (countries and optionally provinces/cities/districts) from JSON files.';

    public function handle(): int
    {
        $countryTable = config('location.tables.country');
        if (! Schema::hasTable($countryTable)) {
            $this->error("Missing table '{$countryTable}'. Run migrations first (php artisan migrate).");

            return self::FAILURE;
        }

        $basePath = $this->getDataPath();
        $countries = $this->discoverCountries($basePath);
        if (count($countries) === 0) {
            $this->error("No country data found in: {$basePath}");

            return self::FAILURE;
        }

        $countryKeys = array_values(array_filter(array_map('strval', (array) $this->argument('country'))));

        if (count($countryKeys) === 0) {
            return $this->importAllCountries($countries);
        }

        foreach (['province', 'city', 'district'] as $key) {
            $table = config("location.tables.{$key}");
            if (! Schema::hasTable($table)) {
                $this->error("Missing table '{$table}'. Run migrations first (php artisan migrate).");

                return self::FAILURE;
            }
        }

        return $this->importCountriesHierarchy($countries, $countryKeys, $basePath);
    }

    protected function importAllCountries(array $countries): int
    {
        $created = 0;
        $restored = 0;
        $updated = 0;

        foreach ($countries as $row) {
            if (! is_array($row)) {
                continue;
            }

            [$country, $action] = $this->upsertCountry($row);
            $created += ($action === 'created') ? 1 : 0;
            $restored += ($action === 'restored') ? 1 : 0;
            $updated += ($action === 'updated') ? 1 : 0;
        }

        $this->newLine();
        $this->line('<info>Countries imported</info>');
        $this->line($this->formatMetricLine('Total', count($countries)));
        $this->line($this->formatMetricLine('Created', $created));
        $this->line($this->formatMetricLine('Restored', $restored));
        $this->line($this->formatMetricLine('Updated', $updated));

        return self::SUCCESS;
    }

    protected function importCountriesHierarchy(array $countries, array $countryKeys, string $basePath): int
    {
        $countriesByKey = [];
        foreach ($countries as $row) {
            if (! is_array($row)) {
                continue;
            }

            $key = Str::of((string) Arr::get($row, 'key'))->lower()->trim()->toString();
            if ($key !== '') {
                $countriesByKey[$key] = $row;
            }
        }

        $anyFailure = false;

        foreach ($countryKeys as $keyRaw) {
            $key = Str::of($keyRaw)->lower()->trim()->toString();
            if ($key === '' || ! isset($countriesByKey[$key])) {
                $this->error("Country key not found in database/data/countries.json: {$keyRaw}");
                $anyFailure = true;
                continue;
            }

            // Ensure country exists
            [$country] = $this->upsertCountry($countriesByKey[$key]);

            // Load hierarchy file (preferred: data.json, fallback: subdivisions.json)
            $hierarchyFile = $this->findHierarchyFile($basePath, $key);
            if ($hierarchyFile === null) {
                // hierarchy file is optional; importing hierarchy is only possible if provided
                $this->warn("Hierarchy file not found for '{$key}'. Skipped provinces/cities/districts import.");
                continue;
            }

            $payload = $this->readJson($hierarchyFile);
            if (is_array($payload) && array_key_exists('provinces', $payload)) {
                $provinces = $payload['provinces'];
            }
            else {
                // Allow the file to be either: {"provinces":[...]} or a plain list: [...]
                $provinces = $payload;
            }

            if (! is_array($provinces)) {
                $this->error("Invalid hierarchy JSON structure for '{$key}' in {$hierarchyFile}");
                $anyFailure = true;
                continue;
            }

            $this->importProvinceCityDistrictTree($country, $provinces);
        }

        return $anyFailure ? self::FAILURE : self::SUCCESS;
    }

    protected function importProvinceCityDistrictTree(Country $country, array $provinces): void
    {
        $pCreated = $pRestored = $pUpdated = 0;
        $cCreated = $cRestored = $cUpdated = 0;
        $dCreated = $dRestored = $dUpdated = 0;

        [$pTotal, $cTotal, $dTotal] = $this->countHierarchyItems($provinces);
        $totalSteps = $pTotal + $cTotal + $dTotal;

        $section = null;
        $bar = null;
        if ($totalSteps > 0 && $this->isInteractiveOutput()) {
            $baseOutput = method_exists($this->output, 'getOutput') ? $this->output->getOutput() : null;
            if ($baseOutput && method_exists($baseOutput, 'section')) {
                /** @var ConsoleSectionOutput $section */
                $section = $baseOutput->section();
            }

            // Fallback: render progress on the current output (no "replace" effect).
            $progressOutput = $section ?: $this->output;

            $bar = new ProgressBar($progressOutput, $totalSteps);
            $bar->setBarWidth(self::BAR_WIDTH);
            $bar->setFormat(' %message%[%bar%] %percent:3s%%');
            $bar->setMessage($this->padLabel("Import {$country->name}"));
            $bar->setOverwrite(true);

            // Keep output clean in non-TTY environments by throttling redraws.
            $bar->setRedrawFrequency(max(1, (int) ceil($totalSteps / 100)));
            if (method_exists($bar, 'minSecondsBetweenRedraws')) {
                $bar->minSecondsBetweenRedraws(0.08);
            }

            $bar->start();
        }
        else if ($totalSteps > 0) {
            $this->line(sprintf(' <comment>Importing hierarchy</comment> <info>%s</info> (%d items)', $country->name, $totalSteps));
        }

        foreach ($provinces as $pRow) {
            if (! is_array($pRow)) {
                continue;
            }

            [$province, $pAction] = $this->upsertProvince($country->id, $pRow);
            $pCreated += ($pAction === 'created') ? 1 : 0;
            $pRestored += ($pAction === 'restored') ? 1 : 0;
            $pUpdated += ($pAction === 'updated') ? 1 : 0;
            $bar?->advance();

            $cities = Arr::get($pRow, 'cities', []);
            if (! is_array($cities)) {
                $cities = [];
            }

            foreach ($cities as $cRow) {
                if (! is_array($cRow)) {
                    continue;
                }

                [$city, $cAction] = $this->upsertCity($province->id, $cRow);
                $cCreated += ($cAction === 'created') ? 1 : 0;
                $cRestored += ($cAction === 'restored') ? 1 : 0;
                $cUpdated += ($cAction === 'updated') ? 1 : 0;
                $bar?->advance();

                $districts = Arr::get($cRow, 'districts', []);
                if (! is_array($districts)) {
                    $districts = [];
                }

                foreach ($districts as $dRow) {
                    if (! is_array($dRow)) {
                        continue;
                    }

                    [, $dAction] = $this->upsertDistrict($city->id, $dRow);
                    $dCreated += ($dAction === 'created') ? 1 : 0;
                    $dRestored += ($dAction === 'restored') ? 1 : 0;
                    $dUpdated += ($dAction === 'updated') ? 1 : 0;
                    $bar?->advance();
                }
            }
        }

        $reportLines = $this->buildHierarchyReportLines(country: $country, pTotal: $pTotal, cTotal: $cTotal, dTotal: $dTotal, pCreated: $pCreated, pRestored: $pRestored, pUpdated: $pUpdated, cCreated: $cCreated, cRestored: $cRestored, cUpdated: $cUpdated, dCreated: $dCreated, dRestored: $dRestored, dUpdated: $dUpdated);

        if ($bar instanceof ProgressBar && $section instanceof ConsoleSectionOutput) {
            $bar->finish();
            $section->clear();
            $section->writeln($reportLines);

            return;
        }

        foreach ($reportLines as $line) {
            $this->line($line);
        }
    }

    /**
     * @return array{0: Country, 1: 'created'|'restored'|'updated'|'none'}
     */
    protected function upsertCountry(array $row): array
    {
        $name = trim((string) Arr::get($row, 'name', ''));
        if ($name === '') {
            throw new RuntimeException('Country row must have a non-empty "name".');
        }

        /** @var Country|null $country */
        $country = Country::withTrashed()->where('name', $name)->first();

        $data = Arr::only($row, [
            'name',
            'flag',
            'mobile_prefix',
            'validation',
            'address_on_letter',
            'status',
        ]);

        if (! $country) {
            /** @var Country $created */
            $created = Country::create($data);

            return [$created, 'created'];
        }

        if (method_exists($country, 'trashed') && $country->trashed()) {
            $country->restore();
            if ($this->option('force')) {
                $country->fill($data)->save();

                return [$country, 'updated'];
            }

            return [$country, 'restored'];
        }

        if ($this->option('force')) {
            $country->fill($data)->save();

            return [$country, 'updated'];
        }

        return [$country, 'none'];
    }

    /**
     * @return array{0: Province, 1: 'created'|'restored'|'updated'|'none'}
     */
    protected function upsertProvince(int $countryId, array $row): array
    {
        $name = trim((string) Arr::get($row, 'name', ''));
        if ($name === '') {
            throw new RuntimeException('Province row must have a non-empty "name".');
        }

        /** @var Province|null $province */
        $province = Province::withTrashed()->where('country_id', $countryId)->where('name', $name)->first();

        $data = [
            'country_id' => $countryId,
            'name'       => $name,
            'status'     => Arr::get($row, 'status', true),
        ];

        if (! $province) {
            /** @var Province $created */
            $created = Province::create($data);

            return [$created, 'created'];
        }

        if ($province->trashed()) {
            $province->restore();
            if ($this->option('force')) {
                $province->fill($data)->save();

                return [$province, 'updated'];
            }

            return [$province, 'restored'];
        }

        if ($this->option('force')) {
            $province->fill($data)->save();

            return [$province, 'updated'];
        }

        return [$province, 'none'];
    }

    /**
     * @return array{0: City, 1: 'created'|'restored'|'updated'|'none'}
     */
    protected function upsertCity(int $provinceId, array $row): array
    {
        $name = trim((string) Arr::get($row, 'name', ''));
        if ($name === '') {
            throw new RuntimeException('City row must have a non-empty "name".');
        }

        /** @var City|null $city */
        $city = City::withTrashed()->where('province_id', $provinceId)->where('name', $name)->first();

        $data = [
            'province_id' => $provinceId,
            'name'        => $name,
            'status'      => Arr::get($row, 'status', true),
        ];

        if (! $city) {
            /** @var City $created */
            $created = City::create($data);

            return [$created, 'created'];
        }

        if ($city->trashed()) {
            $city->restore();
            if ($this->option('force')) {
                $city->fill($data)->save();

                return [$city, 'updated'];
            }

            return [$city, 'restored'];
        }

        if ($this->option('force')) {
            $city->fill($data)->save();

            return [$city, 'updated'];
        }

        return [$city, 'none'];
    }

    /**
     * @return array{0: District, 1: 'created'|'restored'|'updated'|'none'}
     */
    protected function upsertDistrict(int $cityId, array $row): array
    {
        $name = trim((string) Arr::get($row, 'name', ''));
        if ($name === '') {
            throw new RuntimeException('District row must have a non-empty "name".');
        }

        /** @var District|null $district */
        $district = District::withTrashed()->where('city_id', $cityId)->where('name', $name)->first();

        $subtitle = Arr::get($row, 'subtitle');
        $subtitle = is_string($subtitle) ? trim($subtitle) : null;
        $subtitle = ($subtitle === '') ? null : $subtitle;

        // Support both keys:
        // - subtitle => subtitle
        // - search_keywords (dataset) => keywords (DB)
        $rawKeywords = Arr::get($row, 'keywords', Arr::get($row, 'search_keywords'));
        $keywords = $this->normalizeKeywords($rawKeywords);

        $data = [
            'city_id' => $cityId,
            'name'    => $name,
            'subtitle' => $subtitle,
            'keywords' => $keywords,
            'status'  => Arr::get($row, 'status', true),
        ];

        if (! $district) {
            /** @var District $created */
            $created = District::create($data);

            return [$created, 'created'];
        }

        if ($district->trashed()) {
            $district->restore();
            if ($this->option('force')) {
                $district->fill($data)->save();

                return [$district, 'updated'];
            }

            return [$district, 'restored'];
        }

        if ($this->option('force')) {
            $district->fill($data)->save();

            return [$district, 'updated'];
        }

        return [$district, 'none'];
    }

    /**
     * Normalize keywords for storage.
     *
     * Datasets may provide `search_keywords` as a comma-separated string (often using Persian comma "،").
     * We store keywords as an array of trimmed strings.
     *
     * @param mixed $raw
     * @return array<int,string>|null
     */
    protected function normalizeKeywords(mixed $raw): ?array
    {
        $items = [];

        $push = static function (string $value) use (&$items): void {
            $v = trim($value);
            if ($v !== '') {
                $items[] = $v;
            }
        };

        $splitAndPush = static function (string $value) use ($push): void {
            $v = trim($value);
            if ($v === '') {
                return;
            }

            // Split on English/Persian comma and semicolon.
            $parts = preg_split('/[,\x{060C}\x{061B};]+/u', $v) ?: [];
            foreach ($parts as $p) {
                $push((string) $p);
            }
        };

        if (is_string($raw)) {
            $splitAndPush($raw);
        }
        elseif (is_array($raw)) {
            foreach ($raw as $v) {
                if (is_string($v)) {
                    $splitAndPush($v);
                }
            }
        }

        if (count($items) === 0) {
            return null;
        }

        // De-duplicate while keeping order.
        $seen = [];
        $unique = [];
        foreach ($items as $v) {
            $k = mb_strtolower($v);
            if (isset($seen[$k])) {
                continue;
            }
            $seen[$k] = true;
            $unique[] = $v;
        }

        return $unique;
    }

    protected function getDataPath(): string
    {
        $override = (string) $this->option('data-path');
        if (trim($override) !== '') {
            return rtrim($override, "\\/ ");
        }

        // src/Commands -> package root
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'data';
    }

    /**
     * @param string $path
     *
     * @return mixed
     */
    protected function readJson(string $path): mixed
    {
        try {
            $raw = File::get($path);
        } catch (Throwable $e) {
            throw new RuntimeException("Unable to read JSON file: {$path}", 0, $e);
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Invalid JSON in {$path}: " . json_last_error_msg());
        }

        return $decoded;
    }

    /**
     * Find the hierarchy JSON file for a country.
     *
     * Preferred: {basePath}/{key}/data.json
     * Fallback:  {basePath}/{key}/subdivisions.json
     */
    protected function findHierarchyFile(string $basePath, string $key): ?string
    {
        $dir = $basePath . DIRECTORY_SEPARATOR . $key;

        $preferred = $dir . DIRECTORY_SEPARATOR . 'data.json';
        if (File::exists($preferred)) {
            return $preferred;
        }

        $legacy = $dir . DIRECTORY_SEPARATOR . 'subdivisions.json';
        if (File::exists($legacy)) {
            return $legacy;
        }

        return null;
    }

    /**
     * @return array{0:int,1:int,2:int} provinces, cities, districts
     */
    protected function countHierarchyItems(array $provinces): array
    {
        $pTotal = 0;
        $cTotal = 0;
        $dTotal = 0;

        foreach ($provinces as $pRow) {
            if (! is_array($pRow)) {
                continue;
            }

            $pTotal++;

            $cities = Arr::get($pRow, 'cities', []);
            if (! is_array($cities)) {
                continue;
            }

            foreach ($cities as $cRow) {
                if (! is_array($cRow)) {
                    continue;
                }

                $cTotal++;

                $districts = Arr::get($cRow, 'districts', []);
                if (! is_array($districts)) {
                    continue;
                }

                foreach ($districts as $dRow) {
                    if (! is_array($dRow)) {
                        continue;
                    }

                    $dTotal++;
                }
            }
        }

        return [$pTotal, $cTotal, $dTotal];
    }

    protected function padLabel(string $label): string
    {
        $clean = trim($label);
        if (mb_strlen($clean) > self::LABEL_WIDTH) {
            $clean = mb_substr($clean, 0, self::LABEL_WIDTH - 1) . '…';
        }

        return str_pad($clean . ' ', self::LABEL_WIDTH);
    }

    protected function formatMetricLine(string $label, int $value): string
    {
        return sprintf(' <comment>%s</comment> <info>%d</info>', $this->padLabel($label), $value);
    }

    /**
     * @return array<int, string>
     */
    protected function buildHierarchyReportLines(
        Country $country,
        int $pTotal,
        int $cTotal,
        int $dTotal,
        int $pCreated,
        int $pRestored,
        int $pUpdated,
        int $cCreated,
        int $cRestored,
        int $cUpdated,
        int $dCreated,
        int $dRestored,
        int $dUpdated,
    ): array {
        return array_values(array_filter([
            sprintf(' <info>Hierarchy imported</info> <comment>%s</comment>', $country->name),
            '',
            ...$this->buildEntityReportLines('Provinces', $pTotal, $pCreated, $pRestored, $pUpdated),
            '',
            ...$this->buildEntityReportLines('Cities', $cTotal, $cCreated, $cRestored, $cUpdated),
            '',
            ...$this->buildEntityReportLines('Districts', $dTotal, $dCreated, $dRestored, $dUpdated),
        ], static fn ($v) => $v !== null));
    }

    /**
     * @return array<int, string>
     */
    protected function buildEntityReportLines(
        string $title,
        int $total,
        int $created,
        int $restored,
        int $updated
    ): array {
        return [
            sprintf(' <info>%s</info>', $title),
            $this->formatMetricLine('Total', $total),
            $this->formatMetricLine('Created', $created),
            $this->formatMetricLine('Restored', $restored),
            $this->formatMetricLine('Updated', $updated),
        ];
    }

    protected function isInteractiveOutput(): bool
    {
        if (defined('STDOUT')) {
            if (function_exists('stream_isatty')) {
                return @stream_isatty(STDOUT);
            }

            if (function_exists('posix_isatty')) {
                return @posix_isatty(STDOUT);
            }
        }

        return method_exists($this->output, 'isDecorated') && (bool) $this->output->isDecorated();
    }

    /**
     * Discover available countries from base data directory.
     *
     * Expected structure:
     * - data/countries.json
     * - data/ir/data.json (optional; preferred hierarchy file)
     * - data/ir/subdivisions.json (optional; legacy hierarchy file)
     *
     * @return array<int, array<string, mixed>>
     */
    protected function discoverCountries(string $basePath): array
    {
        if (! File::isDirectory($basePath)) {
            return [];
        }

        $countriesFile = $basePath . DIRECTORY_SEPARATOR . 'countries.json';
        if (! File::exists($countriesFile)) {
            return [];
        }

        $list = $this->readJson($countriesFile);
        if (! is_array($list)) {
            throw new RuntimeException("Invalid countries JSON structure in {$countriesFile}");
        }

        // Normalize to map by key
        $byKey = [];
        foreach ($list as $row) {
            if (! is_array($row)) {
                continue;
            }

            $key = Str::of((string) Arr::get($row, 'key'))->lower()->trim()->toString();
            if ($key === '') {
                continue;
            }

            $row['key'] = $key;
            $byKey[$key] = $row;
        }

        return array_values($byKey);
    }
}
