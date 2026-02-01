<?php

namespace JobMetric\Location\Services;

use Illuminate\Database\Eloquent\Model;
use JobMetric\Location\Events\District\DistrictDeleteEvent;
use JobMetric\Location\Events\District\DistrictForceDeleteEvent;
use JobMetric\Location\Events\District\DistrictRestoreEvent;
use JobMetric\Location\Events\District\DistrictStoreEvent;
use JobMetric\Location\Events\District\DistrictUpdateEvent;
use JobMetric\Location\Http\Requests\District\StoreDistrictRequest;
use JobMetric\Location\Http\Requests\District\UpdateDistrictRequest;
use JobMetric\Location\Http\Resources\DistrictResource;
use JobMetric\Location\Models\District as DistrictModel;
use JobMetric\PackageCore\Services\AbstractCrudService;
use Throwable;

/**
 * Class District
 *
 * CRUD and management service for District entities.
 * Responsibilities:
 * - Validate & normalize payloads via DTO helpers
 * - Fire domain events and invalidate caches on mutations
 * - Provide helpers for status toggling
 *
 * @package JobMetric\Location
 */
class District extends AbstractCrudService
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
    protected string $entityName = 'location::base.model_name.district';

    /**
     * Bound model/resource classes for the base CRUD.
     *
     * @var class-string
     */
    protected static string $modelClass = DistrictModel::class;
    protected static string $resourceClass = DistrictResource::class;

    /**
     * Allowed fields for selection/filter/sort in QueryBuilder.
     *
     * @var string[]
     */
    protected static array $fields = [
        'id',
        'city_id',
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
    protected static ?string $storeEventClass = DistrictStoreEvent::class;
    protected static ?string $updateEventClass = DistrictUpdateEvent::class;
    protected static ?string $deleteEventClass = DistrictDeleteEvent::class;
    protected static ?string $restoreEventClass = DistrictRestoreEvent::class;
    protected static ?string $forceDeleteEventClass = DistrictForceDeleteEvent::class;

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
        $data = dto($data, StoreDistrictRequest::class);
    }

    /**
     * Mutate/validate payload before update.
     *
     * Role: aligns input with update rules for the specific District.
     *
     * @param Model $model
     * @param array<string,mixed> $data
     *
     * @return void
     * @throws Throwable
     */
    protected function changeFieldUpdate(Model $model, array &$data): void
    {
        /** @var DistrictModel $district */
        $district = $model;

        $data = dto($data, UpdateDistrictRequest::class, [
            'district_id' => $district->id,
            'city_id'     => $data['city_id'] ?? $district->city_id,
        ]);
    }
}
