<?php

namespace JobMetric\Location\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class GenerateCountriesData extends Command
{
    private const LABEL_WIDTH = 26;
    private const BAR_WIDTH = 28;

    protected $signature = 'location:generate-countries
                            {--out= : Output file path (defaults to package database/data/countries.json)}
                            {--source= : Source URL (defaults to RestCountries countriesV3.1.json on GitHub)}
                            {--phone-metadata= : Phone metadata URL (defaults to Google libphonenumber PhoneNumberMetadata.xml)}
                            {--pretty : Pretty-print JSON output}';

    protected $description = 'Generate data/countries.json from an upstream dataset (English-only, rich metadata).';

    public function handle(): int
    {
        $source = (string) ($this->option('source') ?: 'https://raw.githubusercontent.com/restcountries/restcountries/master/src/main/resources/countriesV3.1.json');
        $phoneMetadataUrl = (string) ($this->option('phone-metadata') ?: 'https://raw.githubusercontent.com/google/libphonenumber/master/resources/PhoneNumberMetadata.xml');
        $outPath = (string) ($this->option('out') ?: $this->defaultOutPath());
        $pretty = (bool) $this->option('pretty');

        $raw = $this->downloadWithProgress($source, 'Countries source');

        $decoded = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            throw new RuntimeException('Invalid JSON received from source: ' . json_last_error_msg());
        }

        $phoneXml = $this->downloadWithProgress($phoneMetadataUrl, 'Phone metadata');
        $mobileRegexByCca2 = $this->extractMobileRegexByCca2($phoneXml);

        $addressFormatByCca2 = $this->fetchAddressFormatsByCca2($decoded);

        // Make sure the next output starts on a clean line after progress bars.
        $this->newLine();
        $this->info('Building output...');

        $countries = [];

        foreach ($decoded as $item) {
            if (! is_array($item)) {
                continue;
            }

            $cca2 = Str::of((string) Arr::get($item, 'cca2'))->upper()->trim()->toString();
            $cca3 = Str::of((string) Arr::get($item, 'cca3'))->upper()->trim()->toString();

            if ($cca2 === '' || $cca3 === '') {
                continue;
            }

            $key = Str::of($cca2)->lower()->toString();

            $nameCommon = (string) Arr::get($item, 'name.common', '');
            $nameOfficial = (string) Arr::get($item, 'name.official', '');

            // Calling code normalization:
            // - For NANP (+1...), store 1
            // - For most countries, root + first suffix becomes the calling code (e.g. +98)
            // - If unavailable, keep null
            $iddRoot = (string) Arr::get($item, 'idd.root', '');
            $iddSuffixes = Arr::get($item, 'idd.suffixes', []);

            $mobilePrefix = $this->computeCallingCode($iddRoot, $iddSuffixes);

            $flagsSvg = (string) Arr::get($item, 'flags.svg', '');
            $flagsPng = (string) Arr::get($item, 'flags.png', '');

            $validation = $mobileRegexByCca2[$cca2] ?? [];
            if (count($validation) === 0) {
                // Some territories have no dedicated mobile numbering plan in libphonenumber metadata.
                // Provide a conservative fallback pattern to avoid null/empty validations.
                $validation = [
                    '/^(?:\\d{4,15})$/',
                ];
            }

            $fmt = Arr::get($addressFormatByCca2, "{$cca2}.fmt");
            $addressOnLetter = is_string($fmt) && trim($fmt) !== '' ? $this->buildAddressOnLetterFromFmt($fmt) : $this->fallbackAddressOnLetterTemplate();

            $countries[$key] = [
                // Fields used by laravel-location import (DB-backed)
                'key'               => $key,
                'name'              => $nameCommon !== '' ? $nameCommon : $cca2,
                'flag'              => $key . '.svg',
                'mobile_prefix'     => $mobilePrefix,
                'validation'        => $validation,
                'address_on_letter' => $addressOnLetter,
                'status'            => true,

                // Rich metadata (not persisted by default import)
                'cca2'              => $cca2,
                'cca3'              => $cca3,
                'ccn3'              => Arr::get($item, 'ccn3'),
                'cioc'              => Arr::get($item, 'cioc'),
                'fifa'              => Arr::get($item, 'fifa'),
                'official_name'     => $nameOfficial !== '' ? $nameOfficial : null,
                'independent'       => Arr::get($item, 'independent'),
                'un_member'         => Arr::get($item, 'unMember'),
                'region'            => Arr::get($item, 'region'),
                'subregion'         => Arr::get($item, 'subregion'),
                'continents'        => Arr::get($item, 'continents', []),
                'capital'           => Arr::get($item, 'capital', []),
                'timezones'         => Arr::get($item, 'timezones', []),
                'tld'               => Arr::get($item, 'tld', []),
                'borders'           => Arr::get($item, 'borders', []),
                'latlng'            => Arr::get($item, 'latlng', []),
                'area'              => Arr::get($item, 'area'),
                'population'        => Arr::get($item, 'population'),
                'languages'         => Arr::get($item, 'languages', []),
                'currencies'        => Arr::get($item, 'currencies', []),
                'maps'              => Arr::get($item, 'maps', []),
                'car'               => Arr::get($item, 'car', []),
                'postal_code'       => Arr::get($item, 'postalCode', []),
                'flag_emoji'        => Arr::get($item, 'flag'),
                'flag_svg_url'      => $flagsSvg !== '' ? $flagsSvg : null,
                'flag_png_url'      => $flagsPng !== '' ? $flagsPng : null,
            ];
        }

        ksort($countries);

        $jsonFlags = $pretty ? (JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : JSON_UNESCAPED_SLASHES;
        $json = json_encode(array_values($countries), $jsonFlags);

        if ($json === false) {
            throw new RuntimeException('Failed to encode JSON: ' . json_last_error_msg());
        }

        $this->ensureDirectoryExists(dirname($outPath));
        File::put($outPath, $json . ($pretty ? "\n" : ''));

        $this->newLine();
        $this->info('Countries dataset generated.');
        $this->line($this->padLabel('Output file') . $outPath);
        $this->line($this->padLabel('Countries') . number_format(count($countries)));

        return self::SUCCESS;
    }

    protected function computeCallingCode(string $root, $suffixes): ?int
    {
        $root = trim($root);
        if ($root === '' || $root === '+') {
            return null;
        }

        $rootDigits = preg_replace('/\\D+/', '', $root) ?: '';
        if ($rootDigits === '') {
            return null;
        }

        // NANP: keep "1" as the country calling code
        if ($rootDigits === '1') {
            return 1;
        }

        $suffixesArr = is_array($suffixes) ? $suffixes : [];
        $suffix = '';
        if (count($suffixesArr) === 1) {
            $suffix = preg_replace('/\\D+/', '', (string) $suffixesArr[0]) ?: '';
        }

        // If suffix is not available (or multiple), fall back to root only
        $full = $suffix !== '' ? ($rootDigits . $suffix) : $rootDigits;

        // keep within unsigned int range
        $code = (int) $full;

        return $code > 0 ? $code : null;
    }

    protected function download(string $url): string
    {
        $response = Http::timeout(60)->retry(2, 500)->get($url);

        if (! $response->ok()) {
            throw new RuntimeException("Failed to download source dataset: HTTP {$response->status()}");
        }

        return (string) $response->body();
    }

    /**
     * Download a resource and show a progress bar based on downloaded bytes.
     */
    protected function downloadWithProgress(string $url, string $label): string
    {
        $bar = $this->output->createProgressBar(0);
        $bar->setBarWidth(self::BAR_WIDTH);
        $bar->setFormat(' %message%[%bar%] %percent:3s%%');
        $bar->setMessage($this->padLabel($label));
        $bar->setRedrawFrequency(10);
        $bar->start();

        $lastBytes = 0;

        $response = Http::timeout(120)->retry(2, 500)->withOptions([
                'progress' => function ($downloadTotal, $downloadedBytes) use ($bar, &$lastBytes) {
                    $total = is_numeric($downloadTotal) ? (int) $downloadTotal : 0;
                    $done = is_numeric($downloadedBytes) ? (int) $downloadedBytes : 0;

                    if ($total > 0 && $bar->getMaxSteps() !== $total) {
                        $bar->setMaxSteps($total);
                    }

                    // Throttle redraws on large payloads.
                    if ($done - $lastBytes < 65536 && $done !== $total) {
                        return;
                    }

                    $lastBytes = $done;
                    $bar->setProgress($done);
                },
            ])->get($url);

        if (! $response->ok()) {
            $bar->clear();
            throw new RuntimeException("Failed to download resource: HTTP {$response->status()}");
        }

        // Ensure bar completes (especially when content-length is missing).
        if ($bar->getMaxSteps() > 0) {
            $bar->finish();
        }
        else {
            // Unknown total size: just finish the line cleanly.
            $bar->setMessage($this->padLabel($label));
            $bar->finish();
        }

        $this->newLine();

        return (string) $response->body();
    }

    /**
     * Build per-country mobile regex patterns from Google libphonenumber metadata.
     *
     * @return array<string, array<int, string>> keyed by CCA2 (ISO2, uppercase)
     */
    protected function extractMobileRegexByCca2(string $xmlContent): array
    {
        if (trim($xmlContent) === '') {
            return [];
        }

        $useErrors = libxml_use_internal_errors(true);

        try {
            $xml = simplexml_load_string($xmlContent);
        } finally {
            libxml_use_internal_errors($useErrors);
        }

        if ($xml === false) {
            return [];
        }

        $map = [];

        if (! isset($xml->territories) || ! isset($xml->territories->territory)) {
            return $map;
        }

        foreach ($xml->territories->territory as $territory) {
            $cca2 = (string) ($territory['id'] ?? '');
            $cca2 = Str::of($cca2)->upper()->trim()->toString();

            if ($cca2 === '' || strlen($cca2) !== 2) {
                continue;
            }

            // Prefer mobile national number pattern; fall back to general description if missing.
            $pattern = '';
            if (isset($territory->mobile->nationalNumberPattern)) {
                $pattern = (string) $territory->mobile->nationalNumberPattern;
            }
            if ($pattern === '' && isset($territory->generalDesc->nationalNumberPattern)) {
                $pattern = (string) $territory->generalDesc->nationalNumberPattern;
            }

            $pattern = $this->normalizeLibphonenumberPattern($pattern);
            if ($pattern === '') {
                $map[$cca2] = [];

                continue;
            }

            $subPatterns = $this->splitTopLevelAlternation($pattern);
            $regexes = [];

            foreach ($subPatterns as $sub) {
                $sub = $this->normalizeLibphonenumberPattern($sub);

                if ($sub === '') {
                    continue;
                }

                $regexes[] = $this->toPhpRegex($sub);
            }

            $map[$cca2] = array_values(array_unique($regexes));
        }

        return $map;
    }

    protected function normalizeLibphonenumberPattern(string $pattern): string
    {
        $pattern = trim($pattern);
        if ($pattern === '') {
            return '';
        }

        // Normalize whitespace/newlines.
        $pattern = preg_replace('/\\s+/', '', $pattern) ?: '';
        $pattern = trim($pattern);

        // Strip anchors if present (we will add our own).
        $pattern = preg_replace('/^\\^/', '', $pattern) ?: '';
        $pattern = preg_replace('/\\$$/', '', $pattern) ?: '';

        return trim($pattern);
    }

    /**
     * Split regex by top-level "|" operators, preserving nested groups.
     *
     * @return array<int, string>
     */
    protected function splitTopLevelAlternation(string $pattern): array
    {
        $parts = [];
        $buf = '';
        $depth = 0;
        $inClass = false;
        $len = strlen($pattern);

        for ($i = 0 ; $i < $len ; $i++) {
            $ch = $pattern[$i];

            if ($ch === '\\\\') {
                // Escape next char.
                $buf .= $ch;

                if ($i + 1 < $len) {
                    $buf .= $pattern[$i + 1];
                    $i++;
                }

                continue;
            }

            if ($ch === '[') {
                $inClass = true;
                $buf .= $ch;

                continue;
            }

            if ($ch === ']' && $inClass) {
                $inClass = false;
                $buf .= $ch;

                continue;
            }

            if (! $inClass) {
                if ($ch === '(') {
                    $depth++;
                    $buf .= $ch;

                    continue;
                }
                if ($ch === ')' && $depth > 0) {
                    $depth--;
                    $buf .= $ch;

                    continue;
                }
                if ($ch === '|' && $depth === 0) {
                    $parts[] = $buf;
                    $buf = '';

                    continue;
                }
            }

            $buf .= $ch;
        }

        if ($buf !== '') {
            $parts[] = $buf;
        }

        return count($parts) ? $parts : [$pattern];
    }

    protected function toPhpRegex(string $pattern): string
    {
        $pattern = $this->normalizeLibphonenumberPattern($pattern);
        $pattern = str_replace('/', '\\/', $pattern);

        return '/^(?:' . $pattern . ')$/';
    }

    /**
     * Fetch per-country address formats from Google's address dataset (libaddressinput).
     *
     * @param array<int, mixed> $restCountriesDecoded
     *
     * @return array<string, array<string, mixed>> keyed by CCA2 (ISO2, uppercase)
     */
    protected function fetchAddressFormatsByCca2(array $restCountriesDecoded): array
    {
        $cca2List = [];
        foreach ($restCountriesDecoded as $item) {
            if (! is_array($item)) {
                continue;
            }

            $cca2 = Str::of((string) Arr::get($item, 'cca2'))->upper()->trim()->toString();
            if ($cca2 !== '' && strlen($cca2) === 2) {
                $cca2List[$cca2] = true;
            }
        }

        $cca2List = array_keys($cca2List);
        sort($cca2List);

        $baseUrl = 'https://chromium-i18n.appspot.com/ssl-address/data/';
        $results = [];

        $bar = $this->output->createProgressBar(count($cca2List));
        $bar->setBarWidth(self::BAR_WIDTH);
        $bar->setFormat(' %message%[%bar%] %percent:3s%%');
        $bar->setMessage($this->padLabel('Address formats'));
        $bar->start();
        $bar->setRedrawFrequency(10);

        // Use small chunks to be gentle with the upstream service.
        foreach (array_chunk($cca2List, 20) as $chunk) {
            $order = array_values($chunk);
            $responses = Http::pool(function ($pool) use ($order, $baseUrl) {
                $reqs = [];
                foreach ($order as $cca2) {
                    $reqs[] = $pool->timeout(30)->retry(2, 300)->get($baseUrl . $cca2);
                }

                return $reqs;
            });

            foreach ($responses as $idx => $response) {
                $cca2 = $order[$idx] ?? '';
                if (is_string($cca2) && $cca2 !== '') {
                    $bar->setMessage($this->padLabel('Address formats', $cca2));
                }

                if (! $response || ! $response->ok()) {
                    $bar->advance();
                    continue;
                }

                $json = $response->json();
                if (is_array($json)) {
                    if (! is_string($cca2) || $cca2 === '') {
                        $bar->advance();
                        continue;
                    }
                    $results[$cca2] = $json;
                }

                $bar->advance();
            }
        }

        // Ensure we display the final 100% line before anything else prints.
        $bar->setRedrawFrequency(1);
        $bar->setMessage($this->padLabel('Address formats', 'done'));
        $bar->setProgress($bar->getMaxSteps());
        $bar->display();
        $bar->finish();
        $this->newLine();

        return $results;
    }

    protected function padLabel(string $label, ?string $suffix = null): string
    {
        $text = $suffix ? ($label . ': ' . $suffix) : ($label . ':');
        $text = trim($text);

        if (strlen($text) > self::LABEL_WIDTH) {
            $text = substr($text, 0, self::LABEL_WIDTH);
        }

        return str_pad($text, self::LABEL_WIDTH, ' ', STR_PAD_RIGHT) . ' ';
    }

    /**
     * Convert Google address "fmt" pattern to our letter template placeholders.
     *
     * Common fmt tokens:
     * - %N recipient
     * - %O organization
     * - %A street address (address lines)
     * - %C locality (city)
     * - %S administrative area (state/province)
     * - %D dependent locality (district/neighborhood)
     * - %Z postal code
     * - %n newline
     */
    protected function buildAddressOnLetterFromFmt(string $fmt): string
    {
        $template = $fmt;

        // Newlines first.
        $template = str_replace('%n', "\n", $template);

        // Replace known tokens.
        $template = str_replace('%N', "{receiver_name}\n{receiver_number}", $template);
        $template = str_replace('%O', '', $template);
        $template = str_replace('%A', "{blvd} {street} {alley} {number}\n{floor} {unit}", $template);
        $template = str_replace('%C', '{city}', $template);
        $template = str_replace('%S', '{province}', $template);
        $template = str_replace('%D', '{district}', $template);
        $template = str_replace('%Z', '{postcode}', $template);

        // Remove any remaining unknown tokens like %X.
        $template = preg_replace('/%[A-Z]/', '', $template) ?: $template;

        // Clean up extra whitespace and empty lines.
        $lines = preg_split("/\\r\\n|\\r|\\n/", $template) ?: [];
        $clean = [];

        foreach ($lines as $line) {
            $line = trim((string) $line);
            $line = trim($line, " \t,");

            if ($line === '') {
                continue;
            }

            // Avoid lines that became only punctuation.
            if (preg_match('/^[,]+$/', $line)) {
                continue;
            }

            $clean[] = $line;
        }

        $template = implode("\n", $clean);

        // Ensure the country placeholder exists.
        if (! str_contains($template, '{country}')) {
            $template = rtrim($template) . "\n{country}";
        }

        return $template;
    }

    /**
     * Fallback template used only when address format is unavailable.
     */
    protected function fallbackAddressOnLetterTemplate(): string
    {
        return "{receiver_name}\n{receiver_number}\n{blvd} {street} {alley} {number}\n{floor} {unit}\n{district}, {city}, {province}\n{postcode}\n{country}";
    }

    protected function defaultOutPath(): string
    {
        // src/Commands -> package root
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'countries.json';
    }

    protected function ensureDirectoryExists(string $dir): void
    {
        if ($dir === '' || File::isDirectory($dir)) {
            return;
        }

        File::makeDirectory($dir, 0755, true);
    }
}
