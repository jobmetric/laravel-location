<?php

namespace JobMetric\Location\Services;

use Illuminate\Database\Eloquent\Model;
use JobMetric\Location\Events\GeoArea\GeoAreaDeleteEvent;
use JobMetric\Location\Events\GeoArea\GeoAreaForceDeleteEvent;
use JobMetric\Location\Events\GeoArea\GeoAreaRestoreEvent;
use JobMetric\Location\Events\GeoArea\GeoAreaStoreEvent;
use JobMetric\Location\Events\GeoArea\GeoAreaUpdateEvent;
use JobMetric\Location\Http\Requests\StoreGeoAreaRequest;
use JobMetric\Location\Http\Requests\UpdateGeoAreaRequest;
use JobMetric\Location\Http\Resources\GeoAreaResource;
use JobMetric\Location\Models\GeoArea as GeoAreaModel;
use JobMetric\PackageCore\Services\AbstractCrudService;
use Throwable;

/**
 * Class GeoArea
 *
 * CRUD and management service for GeoArea entities.
 * Responsibilities:
 * - Validate & normalize payloads via DTO helpers
 * - Fire domain events on mutations
 * - Handle soft delete operations (delete, restore, forceDelete)
 *
 * @package JobMetric\Location
 */
class GeoArea extends AbstractCrudService
{
    /**
     * Enable soft-deletes; restore and forceDelete are enabled for this service.
     *
     * @var bool
     */
    protected bool $softDelete = true;

    /**
     * Enable toggleStatus API.
     *
     * @var bool
     */
    protected bool $hasToggleStatus = true;

    /**
     * Human-readable entity name key used in response messages.
     *
     * @var string
     */
    protected string $entityName = 'location::base.model_name.geo_area';

    /**
     * Bound model/resource classes for the base CRUD.
     *
     * @var class-string
     */
    protected static string $modelClass = GeoAreaModel::class;
    protected static string $resourceClass = GeoAreaResource::class;

    /**
     * Allowed fields for selection/filter/sort in QueryBuilder.
     *
     * @var string[]
     */
    protected static array $fields = [
        'id',
        'status',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Domain events mapping for CRUD lifecycle.
     *
     * @var class-string|null
     */
    protected static ?string $storeEventClass = GeoAreaStoreEvent::class;
    protected static ?string $updateEventClass = GeoAreaUpdateEvent::class;
    protected static ?string $deleteEventClass = GeoAreaDeleteEvent::class;
    protected static ?string $restoreEventClass = GeoAreaRestoreEvent::class;
    protected static ?string $forceDeleteEventClass = GeoAreaForceDeleteEvent::class;

    /**
     * Mutate/validate payload before create.
     *
     * Role: ensures a clean, validated input for store().
     *
     * @param array<string,mixed> $data
     *
     * @return void
     * @throws Throwable
     */
    protected function changeFieldStore(array &$data): void
    {
        $data = dto($data, StoreGeoAreaRequest::class);
    }

    /**
     * Mutate/validate payload before update.
     *
     * Role: aligns input with update rules for the specific GeoArea.
     *
     * @param Model $model
     * @param array<string,mixed> $data
     *
     * @return void
     * @throws Throwable
     */
    protected function changeFieldUpdate(Model $model, array &$data): void
    {
        /** @var GeoAreaModel $geoArea */
        $geoArea = $model;

        $data = dto($data, UpdateGeoAreaRequest::class, [
            'geo_area_id' => $geoArea->id,
        ]);
    }

    /**
     * Hook after create: attach locations if provided.
     *
     * @param Model $model
     * @param array<string,mixed> $data
     *
     * @return void
     * @throws Throwable
     */
    protected function afterStore(Model $model, array &$data): void
    {
        /** @var GeoAreaModel $geoArea */
        $geoArea = $model;

        // Attach locations if provided
        if (isset($data['locations']) && is_array($data['locations'])) {
            foreach ($data['locations'] as $locationData) {
                $geoArea->attachLocationByData($locationData);
            }
        }
    }

    /**
     * Hook after update: sync locations if provided.
     *
     * @param Model $model
     * @param array<string,mixed> $data
     *
     * @return void
     * @throws Throwable
     */
    protected function afterUpdate(Model $model, array &$data): void
    {
        /** @var GeoAreaModel $geoArea */
        $geoArea = $model;

        // Sync locations if provided (replace existing)
        if (isset($data['locations']) && is_array($data['locations'])) {
            // Remove existing locations
            $geoArea->detachAllLocations();

            // Attach new locations
            foreach ($data['locations'] as $locationData) {
                $geoArea->attachLocationByData($locationData);
            }
        }
    }
}
