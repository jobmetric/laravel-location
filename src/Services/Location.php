<?php

namespace JobMetric\Location\Services;

use Illuminate\Database\Eloquent\Model;
use JobMetric\Location\Events\Location\LocationDeleteEvent;
use JobMetric\Location\Events\Location\LocationStoreEvent;
use JobMetric\Location\Events\Location\LocationUpdateEvent;
use JobMetric\Location\Http\Requests\StoreLocationRequest;
use JobMetric\Location\Http\Requests\UpdateLocationRequest;
use JobMetric\Location\Http\Resources\LocationLocationResource;
use JobMetric\Location\Models\Location as LocationModel;
use JobMetric\PackageCore\Services\AbstractCrudService;
use Throwable;

/**
 * Class Location
 *
 * CRUD and management service for Location entities.
 * Responsibilities:
 * - Validate & normalize payloads via DTO helpers
 * - Fire domain events and invalidate caches on mutations
 * - Manage unique location combinations (country, province, city, district)
 *
 * @package JobMetric\Location
 */
class Location extends AbstractCrudService
{
    /**
     * Disable soft-deletes (Location model doesn't use SoftDeletes).
     *
     * @var bool
     */
    protected bool $softDelete = false;

    /**
     * Disable toggleStatus (Location doesn't have status field).
     *
     * @var bool
     */
    protected bool $hasToggleStatus = false;

    /**
     * Human-readable entity name key used in response messages.
     *
     * @var string
     */
    protected string $entityName = 'location::base.model_name.location';

    /**
     * Bound model/resource classes for the base CRUD.
     *
     * @var class-string
     */
    protected static string $modelClass = LocationModel::class;
    protected static string $resourceClass = LocationLocationResource::class;

    /**
     * Allowed fields for selection/filter/sort in QueryBuilder.
     *
     * @var string[]
     */
    protected static array $fields = [
        'id',
        'country_id',
        'province_id',
        'city_id',
        'district_id',
        'created_at',
    ];

    /**
     * Default sort applied by QueryBuilder.
     *
     * @var string[]
     */
    protected static array $defaultSort = ['-id'];

    /**
     * Domain events mapping for CRUD lifecycle.
     *
     * @var class-string|null
     */
    protected static ?string $storeEventClass = LocationStoreEvent::class;
    protected static ?string $updateEventClass = LocationUpdateEvent::class;
    protected static ?string $deleteEventClass = LocationDeleteEvent::class;
    protected static ?string $restoreEventClass = null;
    protected static ?string $forceDeleteEventClass = null;

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
        $data = dto($data, StoreLocationRequest::class);
    }

    /**
     * Mutate/validate payload before update.
     *
     * Role: aligns input with update rules for the specific Location.
     *
     * @param Model $model
     * @param array<string,mixed> $data
     *
     * @return void
     * @throws Throwable
     */
    protected function changeFieldUpdate(Model $model, array &$data): void
    {
        /** @var LocationModel $location */
        $location = $model;

        $data = dto($data, UpdateLocationRequest::class, [
            'location_id' => $location->id,
        ]);
    }
}
