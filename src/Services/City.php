<?php

namespace JobMetric\Location\Services;

use Illuminate\Database\Eloquent\Model;
use JobMetric\Location\Events\City\CityDeleteEvent;
use JobMetric\Location\Events\City\CityForceDeleteEvent;
use JobMetric\Location\Events\City\CityRestoreEvent;
use JobMetric\Location\Events\City\CityStoreEvent;
use JobMetric\Location\Events\City\CityUpdateEvent;
use JobMetric\Location\Http\Requests\City\StoreCityRequest;
use JobMetric\Location\Http\Requests\City\UpdateCityRequest;
use JobMetric\Location\Http\Resources\CityResource;
use JobMetric\Location\Models\City as CityModel;
use JobMetric\PackageCore\Services\AbstractCrudService;
use Throwable;

/**
 * Class City
 *
 * CRUD and management service for City entities.
 * Responsibilities:
 * - Validate & normalize payloads via DTO helpers
 * - Fire domain events and invalidate caches on mutations
 * - Provide helpers for status toggling
 *
 * @package JobMetric\Location
 */
class City extends AbstractCrudService
{
    /**
     * Enable soft-deletes + restore/forceDelete APIs.
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
    protected string $entityName = 'location::base.model_name.city';

    /**
     * Bound model/resource classes for the base CRUD.
     *
     * @var class-string
     */
    protected static string $modelClass = CityModel::class;
    protected static string $resourceClass = CityResource::class;

    /**
     * Allowed fields for selection/filter/sort in QueryBuilder.
     *
     * @var string[]
     */
    protected static array $fields = [
        'id',
        'province_id',
        'name',
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
    protected static ?string $storeEventClass = CityStoreEvent::class;
    protected static ?string $updateEventClass = CityUpdateEvent::class;
    protected static ?string $deleteEventClass = CityDeleteEvent::class;
    protected static ?string $restoreEventClass = CityRestoreEvent::class;
    protected static ?string $forceDeleteEventClass = CityForceDeleteEvent::class;

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
        $data = dto($data, StoreCityRequest::class);
    }

    /**
     * Mutate/validate payload before update.
     *
     * Role: aligns input with update rules for the specific City.
     *
     * @param Model $model
     * @param array<string,mixed> $data
     *
     * @return void
     * @throws Throwable
     */
    protected function changeFieldUpdate(Model $model, array &$data): void
    {
        /** @var CityModel $city */
        $city = $model;

        $data = dto($data, UpdateCityRequest::class, [
            'city_id'     => $city->id,
            'province_id' => $data['province_id'] ?? $city->province_id,
        ]);
    }
}
