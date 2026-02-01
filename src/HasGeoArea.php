<?php

namespace JobMetric\Location;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use JobMetric\Location\Http\Resources\GeoAreaResource;
use JobMetric\Location\Models\GeoArea;
use JobMetric\Location\Models\GeoAreaRelation;

/**
 * Trait HasGeoArea
 *
 * Provides geo area management functionality to Eloquent models via geo_area_relations pivot table.
 * A model can have multiple geo areas attached.
 *
 * @property-read Collection<int, GeoAreaRelation> $geoAreaRelations
 *
 * @method MorphMany morphMany(string $class, string $string)
 *
 * @package JobMetric\Location
 */
trait HasGeoArea
{
    /**
     * Geo area relations - pivot table connecting geo areas to this model.
     *
     * @return MorphMany
     */
    public function geoAreaRelations(): MorphMany
    {
        return $this->morphMany(GeoAreaRelation::class, 'geographical');
    }

    /**
     * Get all geo areas for this model (through geo_area_relations).
     *
     * @param bool $withTrashed Include soft-deleted geo areas
     *
     * @return Collection<int, GeoArea>
     */
    public function geoAreas(bool $withTrashed = false): Collection
    {
        $query = $this->geoAreaRelations();

        if ($withTrashed) {
            $query->with(['geoArea' => fn ($q) => $q->withTrashed()]);
        }
        else {
            $query->with('geoArea');
        }

        return $query->get()->pluck('geoArea')->filter();
    }

    /**
     * Check if this model has a specific geo area attached.
     *
     * @param int $geo_area_id
     *
     * @return bool
     */
    public function hasGeoArea(int $geo_area_id): bool
    {
        return $this->geoAreaRelations()->where('geo_area_id', $geo_area_id)->exists();
    }

    /**
     * Get all geo areas as resource collection.
     *
     * @param bool $withTrashed Include soft-deleted geo areas
     *
     * @return AnonymousResourceCollection
     */
    public function getGeoAreas(bool $withTrashed = false): AnonymousResourceCollection
    {
        return GeoAreaResource::collection($this->geoAreas($withTrashed));
    }

    /**
     * Get a specific geo area by ID (if attached to this model).
     *
     * @param int $geo_area_id
     * @param bool $withTrashed Include soft-deleted geo area
     *
     * @return GeoAreaResource|null
     */
    public function getGeoAreaById(int $geo_area_id, bool $withTrashed = false): ?GeoAreaResource
    {
        if (! $this->hasGeoArea($geo_area_id)) {
            return null;
        }

        $query = GeoArea::query();

        if ($withTrashed) {
            $query->withTrashed();
        }

        $geoArea = $query->find($geo_area_id);

        return $geoArea ? GeoAreaResource::make($geoArea) : null;
    }

    /**
     * Attach an existing geo area to this model.
     * Only non-deleted geo areas can be attached.
     *
     * @param int $geo_area_id
     *
     * @return static
     */
    public function attachGeoArea(int $geo_area_id): static
    {
        // Only attach if geo area exists and is NOT soft-deleted
        $geoArea = GeoArea::find($geo_area_id);

        if ($geoArea && ! $this->hasGeoArea($geo_area_id)) {
            $this->geoAreaRelations()->create([
                'geo_area_id' => $geo_area_id,
            ]);
        }

        return $this;
    }

    /**
     * Detach a geo area from this model (does not delete the geo area).
     *
     * @param int $geo_area_id
     *
     * @return static
     */
    public function detachGeoArea(int $geo_area_id): static
    {
        $this->geoAreaRelations()->where('geo_area_id', $geo_area_id)->delete();

        return $this;
    }

    /**
     * Detach all geo areas from this model.
     *
     * @return static
     */
    public function detachAllGeoAreas(): static
    {
        $this->geoAreaRelations()->delete();

        return $this;
    }

    /**
     * Sync geo areas - detach all and attach the given ones.
     *
     * @param array $geo_area_ids
     *
     * @return static
     */
    public function syncGeoAreas(array $geo_area_ids): static
    {
        $this->detachAllGeoAreas();

        foreach ($geo_area_ids as $geo_area_id) {
            $this->attachGeoArea($geo_area_id);
        }

        return $this;
    }

    /**
     * Check if this model is within any of the attached geo areas.
     * Useful for checking if a location matches any geo area.
     *
     * @param int $location_id
     *
     * @return bool
     */
    public function isInGeoArea(int $location_id): bool
    {
        return $this->geoAreas()->filter(fn (GeoArea $geoArea) => $geoArea->hasLocation($location_id))->isNotEmpty();
    }

    /**
     * Get the first geo area that contains the given location.
     *
     * @param int $location_id
     *
     * @return GeoArea|null
     */
    public function getGeoAreaForLocation(int $location_id): ?GeoArea
    {
        return $this->geoAreas()->first(fn (GeoArea $geoArea) => $geoArea->hasLocation($location_id));
    }
}
