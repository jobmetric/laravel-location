<?php

namespace JobMetric\Location;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use JobMetric\Location\Models\City;
use JobMetric\Location\Models\Country;
use JobMetric\Location\Models\District;
use JobMetric\Location\Models\Location;
use JobMetric\Location\Models\LocationRelation;
use JobMetric\Location\Models\Province;
use Throwable;

/**
 * Trait HasLocation
 *
 * Provides location functionality for models that can have a location.
 * This trait supports both single location (MorphOne) and multiple locations (MorphMany).
 *
 * Important: Location records are unique and never deleted.
 * - Each unique combination of country_id, province_id, city_id, district_id
 *   results in exactly one Location record
 * - Location records are created using firstOrCreate to prevent duplicates
 * - This ensures referential integrity and data consistency
 *
 * Model may define (optional):
 *   protected string $locationMode = 'single';  // 'single' or 'multiple'
 *
 * For single location models (e.g., Address):
 * - Use locationRelation() to get the single LocationRelation
 * - Use location, country, province, city, district accessors
 * - Use attachLocation() to attach a location
 * - Use detachLocation() to detach the location
 *
 * For multiple location models (e.g., GeoArea):
 * - Use locationRelations() to get the collection of LocationRelations
 * - Use locations, countries, provinces, cities, districts accessors
 * - Use attachLocation() to attach a location
 * - Use detachLocation() to detach a specific location
 * - Use detachAllLocations() to detach all locations
 *
 * @package JobMetric\Location
 *
 * @property LocationRelation|null $locationRelation
 * @property Location|null $location
 * @property Country|null $country
 * @property Province|null $province
 * @property City|null $city
 * @property District|null $district
 * @property Collection|LocationRelation[] $locationRelations
 * @property Collection|Location[] $locations
 * @property Collection|Country[] $countries
 * @property Collection|Province[] $provinces
 * @property Collection|City[] $cities
 * @property Collection|District[] $districts
 *
 * @method morphOne(string $class, string $string)
 * @method morphMany(string $class, string $string)
 */
trait HasLocation
{
    /**
     * Base location mode (source of truth inside the trait).
     * Initialized from model property (if present) in initializeHasLocation().
     * Default: 'single'
     *
     * @var string
     */
    private string $baseLocationMode = 'single';

    /**
     * Initialize HasLocation trait by reading locationMode from model.
     * Mirrors HasMeta and HasTranslation design.
     *
     * @return void
     * @throws Throwable
     */
    public function initializeHasLocation(): void
    {
        if (function_exists('hasPropertyInClass')) {
            if (hasPropertyInClass($this, 'locationMode')) {
                /** @var string $this ->locationMode */
                $mode = $this->locationMode;
                $this->baseLocationMode = in_array($mode, ['single', 'multiple'], true) ? $mode : 'single';
            }
        }
        else {
            if (property_exists($this, 'locationMode')) {
                /** @var string $this ->locationMode */
                $mode = $this->locationMode;
                $this->baseLocationMode = in_array($mode, ['single', 'multiple'], true) ? $mode : 'single';
            }
        }
    }

    /**
     * Boot HasLocation trait.
     * Initializes location mode when model is retrieved or created.
     *
     * @return void
     * @throws Throwable
     */
    public static function bootHasLocation(): void
    {
        // Initialize when model is retrieved from database
        static::retrieved(function (Model $model) {
            if (method_exists($model, 'initializeHasLocation')) {
                $model->initializeHasLocation();
            }
        });

        // Initialize when model is being created
        static::creating(function (Model $model) {
            if (method_exists($model, 'initializeHasLocation')) {
                $model->initializeHasLocation();
            }
        });
    }

    /**
     * Check if this model uses single location mode.
     *
     * @return bool
     */
    public function isLocationModeSingle(): bool
    {
        return $this->baseLocationMode === 'single';
    }

    /**
     * Check if this model uses multiple location mode.
     *
     * @return bool
     */
    public function isLocationModeMultiple(): bool
    {
        return $this->baseLocationMode === 'multiple';
    }

    /**
     * Get single location relation for this model (polymorphic).
     * Only available when locationMode is 'single'.
     *
     * @return MorphOne
     * @throws Throwable
     */
    public function locationRelation(): MorphOne
    {
        if (! $this->isLocationModeSingle()) {
            throw new BadMethodCallException('locationRelation() is only available when locationMode is "single".');
        }

        return $this->morphOne(LocationRelation::class, 'locationable');
    }

    /**
     * Get multiple location relations for this model (polymorphic).
     * Only available when locationMode is 'multiple'.
     *
     * @return MorphMany
     * @throws Throwable
     */
    public function locationRelations(): MorphMany
    {
        if (! $this->isLocationModeMultiple()) {
            throw new BadMethodCallException('locationRelations() is only available when locationMode is "multiple".');
        }

        return $this->morphMany(LocationRelation::class, 'locationable');
    }

    /**
     * Get single location through location relation.
     * Only works when locationMode is 'single'.
     *
     * @return Location|null
     */
    public function getLocationAttribute(): ?Location
    {
        if (! $this->isLocationModeSingle()) {
            return null;
        }

        return $this->locationRelation?->location;
    }

    /**
     * Get multiple locations through location relations.
     * Only works when locationMode is 'multiple'.
     *
     * Note: For better performance, eager load 'locationRelations.location' when querying.
     *
     * @return \Illuminate\Support\Collection
     * @throws Throwable
     */
    public function getLocationsAttribute(): \Illuminate\Support\Collection
    {
        if (! $this->isLocationModeMultiple()) {
            return collect();
        }

        if ($this->relationLoaded('locationRelations')) {
            return $this->locationRelations->map(fn (LocationRelation $relation) => $relation->location)->filter();
        }

        return $this->locationRelations()->with('location')->get()->map(fn (LocationRelation $relation
        ) => $relation->location)->filter();
    }

    /**
     * Get country through single location.
     * Only works when locationMode is 'single'.
     *
     * @return Country|null
     */
    public function getCountryAttribute(): ?Country
    {
        if (! $this->isLocationModeSingle()) {
            return null;
        }

        return $this->location?->country;
    }

    /**
     * Get countries through multiple locations.
     * Only works when locationMode is 'multiple'.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCountriesAttribute(): \Illuminate\Support\Collection
    {
        if (! $this->isLocationModeMultiple()) {
            return collect();
        }

        return $this->locations->map(fn (Location $location) => $location->country)->filter()->unique('id')->values();
    }

    /**
     * Get province through single location.
     * Only works when locationMode is 'single'.
     *
     * @return Province|null
     */
    public function getProvinceAttribute(): ?Province
    {
        if (! $this->isLocationModeSingle()) {
            return null;
        }

        return $this->location?->province;
    }

    /**
     * Get provinces through multiple locations.
     * Only works when locationMode is 'multiple'.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getProvincesAttribute(): \Illuminate\Support\Collection
    {
        if (! $this->isLocationModeMultiple()) {
            return collect();
        }

        return $this->locations->map(fn (Location $location) => $location->province)->filter()->unique('id')->values();
    }

    /**
     * Get city through single location.
     * Only works when locationMode is 'single'.
     *
     * @return City|null
     */
    public function getCityAttribute(): ?City
    {
        if (! $this->isLocationModeSingle()) {
            return null;
        }

        return $this->location?->city;
    }

    /**
     * Get cities through multiple locations.
     * Only works when locationMode is 'multiple'.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCitiesAttribute(): \Illuminate\Support\Collection
    {
        if (! $this->isLocationModeMultiple()) {
            return collect();
        }

        return $this->locations->map(fn (Location $location) => $location->city)->filter()->unique('id')->values();
    }

    /**
     * Get district through single location.
     * Only works when locationMode is 'single'.
     *
     * @return District|null
     */
    public function getDistrictAttribute(): ?District
    {
        if (! $this->isLocationModeSingle()) {
            return null;
        }

        return $this->location?->district;
    }

    /**
     * Get districts through multiple locations.
     * Only works when locationMode is 'multiple'.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getDistrictsAttribute(): \Illuminate\Support\Collection
    {
        if (! $this->isLocationModeMultiple()) {
            return collect();
        }

        return $this->locations->map(fn (Location $location) => $location->district)->filter()->unique('id')->values();
    }

    /**
     * Attach a location to this model.
     * For single mode: replaces existing location if any.
     * For multiple mode: adds a new location relation.
     *
     * Note: The locationId must reference an existing Location record.
     * Location records are unique and never deleted, ensuring data integrity.
     *
     * @param int $locationId Location ID to attach (must exist in locations table)
     *
     * @return LocationRelation
     * @throws Throwable
     */
    public function attachLocation(int $locationId): LocationRelation
    {
        // Ensure location mode is initialized
        $this->initializeHasLocation();

        return DB::transaction(function () use ($locationId) {
            // Verify that the location exists (locations are unique and never deleted)
            $location = Location::find($locationId);
            if (! $location) {
                throw new InvalidArgumentException("Location with ID $locationId does not exist.");
            }

            if ($this->isLocationModeSingle()) {
                // For single mode: delete existing and create new
                $existing = $this->locationRelation;
                $existing?->delete();

                return $this->locationRelation()->create([
                    'location_id' => $locationId,
                ]);
            }

            // For multiple mode: check if already exists
            $existing = $this->locationRelations()->where('location_id', $locationId)->first();

            if ($existing) {
                return $existing;
            }

            return $this->locationRelations()->create([
                'location_id' => $locationId,
            ]);
        });
    }

    /**
     * Attach a location by providing location data (country_id, province_id, etc.).
     * Creates the Location if it doesn't exist (ensuring uniqueness), then attaches it.
     *
     * This method ensures that:
     * - No duplicate Location records are created (uses firstOrCreate)
     * - Each unique combination of country_id, province_id, city_id, district_id
     *   results in exactly one Location record
     * - Location records are never deleted, maintaining referential integrity
     *
     * @param array<string,mixed> $locationData Location data (country_id, province_id, city_id, district_id)
     *
     * @return LocationRelation
     * @throws Throwable
     */
    public function attachLocationByData(array $locationData): LocationRelation
    {
        return DB::transaction(function () use ($locationData) {
            // Find or create Location (ensures uniqueness via firstOrCreate)
            // This guarantees that each unique combination of location data
            // results in exactly one Location record, preventing duplicates
            $location = Location::firstOrCreate([
                'country_id'  => $locationData['country_id'],
                'province_id' => $locationData['province_id'] ?? null,
                'city_id'     => $locationData['city_id'] ?? null,
                'district_id' => $locationData['district_id'] ?? null,
            ]);

            // Attach the location (which may be newly created or existing)
            return $this->attachLocation($location->id);
        });
    }

    /**
     * Detach a location from this model.
     * For single mode: detaches the single location.
     * For multiple mode: detaches the specified location.
     *
     * @param int|null $locationId Location ID to detach (required for multiple mode)
     *
     * @return bool
     * @throws Throwable
     */
    public function detachLocation(?int $locationId = null): bool
    {
        // Ensure location mode is initialized
        $this->initializeHasLocation();

        return DB::transaction(function () use ($locationId) {
            if ($this->isLocationModeSingle()) {
                $relation = $this->locationRelation;
                if ($relation) {
                    return $relation->delete();
                }

                return false;
            }

            // For multiple mode: locationId is required
            if ($locationId === null) {
                throw new InvalidArgumentException('locationId is required when locationMode is "multiple".');
            }

            $relation = $this->locationRelations()->where('location_id', $locationId)->first();

            if ($relation) {
                return $relation->delete();
            }

            return false;
        });
    }

    /**
     * Detach all locations from this model.
     * Only works when locationMode is 'multiple'.
     *
     * @return int Number of detached locations
     * @throws Throwable
     */
    public function detachAllLocations(): int
    {
        // Ensure location mode is initialized
        $this->initializeHasLocation();

        if (! $this->isLocationModeMultiple()) {
            throw new BadMethodCallException('detachAllLocations() is only available when locationMode is "multiple".');
        }

        return DB::transaction(function () {
            $count = $this->locationRelations()->count();
            $this->locationRelations()->delete();

            return $count;
        });
    }

    /**
     * Sync locations: detach all and attach the provided ones.
     * Only works when locationMode is 'multiple'.
     *
     * Note: All locationIds must reference existing Location records.
     * Location records are unique and never deleted, ensuring data integrity.
     *
     * @param array<int> $locationIds Array of location IDs to sync (must exist in locations table)
     *
     * @return Collection
     * @throws Throwable
     */
    public function syncLocations(array $locationIds): Collection
    {
        // Ensure location mode is initialized
        $this->initializeHasLocation();

        if (! $this->isLocationModeMultiple()) {
            throw new BadMethodCallException('syncLocations() is only available when locationMode is "multiple".');
        }

        return DB::transaction(function () use ($locationIds) {
            // Verify all locations exist before proceeding
            $existingLocationIds = Location::whereIn('id', $locationIds)->pluck('id')->toArray();
            $missingIds = array_diff($locationIds, $existingLocationIds);

            if (! empty($missingIds)) {
                throw new InvalidArgumentException('The following location IDs do not exist: ' . implode(', ', $missingIds));
            }

            // Detach all existing
            $this->detachAllLocations();

            // Attach new ones (all verified to exist)
            $relations = collect();
            foreach ($locationIds as $locationId) {
                $relations->push($this->attachLocation($locationId));
            }

            return $relations;
        });
    }

    /**
     * Check if this model has a specific location attached.
     *
     * Note: This method checks if the location is attached to this model,
     * not whether the location exists in the locations table.
     * Location records are unique and never deleted.
     *
     * @param int $locationId Location ID to check
     *
     * @return bool
     * @throws Throwable
     */
    public function hasLocation(int $locationId): bool
    {
        // Ensure location mode is initialized
        $this->initializeHasLocation();

        if ($this->isLocationModeSingle()) {
            return $this->locationRelation?->location_id === $locationId;
        }

        return $this->locationRelations()->where('location_id', $locationId)->exists();
    }
}
