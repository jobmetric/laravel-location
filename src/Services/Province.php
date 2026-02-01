<?php

namespace JobMetric\Location\Services;

use Illuminate\Database\Eloquent\Model;
use JobMetric\Location\Events\Province\ProvinceDeleteEvent;
use JobMetric\Location\Events\Province\ProvinceForceDeleteEvent;
use JobMetric\Location\Events\Province\ProvinceRestoreEvent;
use JobMetric\Location\Events\Province\ProvinceStoreEvent;
use JobMetric\Location\Events\Province\ProvinceUpdateEvent;
use JobMetric\Location\Http\Requests\Province\StoreProvinceRequest;
use JobMetric\Location\Http\Requests\Province\UpdateProvinceRequest;
use JobMetric\Location\Http\Resources\ProvinceResource;
use JobMetric\Location\Models\Province as ProvinceModel;
use JobMetric\PackageCore\Services\AbstractCrudService;
use Throwable;

/**
 * Class Province
 *
 * CRUD and management service for Province entities.
 * Responsibilities:
 * - Validate & normalize payloads via DTO helpers
 * - Fire domain events and invalidate caches on mutations
 * - Provide helpers for status toggling
 *
 * @package JobMetric\Location
 */
class Province extends AbstractCrudService
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
    protected string $entityName = 'location::base.model_name.province';

    /**
     * Bound model/resource classes for the base CRUD.
     *
     * @var class-string
     */
    protected static string $modelClass = ProvinceModel::class;
    protected static string $resourceClass = ProvinceResource::class;

    /**
     * Allowed fields for selection/filter/sort in QueryBuilder.
     *
     * @var string[]
     */
    protected static array $fields = [
        'id',
        'country_id',
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
    protected static ?string $storeEventClass = ProvinceStoreEvent::class;
    protected static ?string $updateEventClass = ProvinceUpdateEvent::class;
    protected static ?string $deleteEventClass = ProvinceDeleteEvent::class;
    protected static ?string $restoreEventClass = ProvinceRestoreEvent::class;
    protected static ?string $forceDeleteEventClass = ProvinceForceDeleteEvent::class;

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
        $data = dto($data, StoreProvinceRequest::class);
    }

    /**
     * Mutate/validate payload before update.
     *
     * Role: aligns input with update rules for the specific Province.
     *
     * @param Model $model
     * @param array<string,mixed> $data
     *
     * @return void
     * @throws Throwable
     */
    protected function changeFieldUpdate(Model $model, array &$data): void
    {
        /** @var ProvinceModel $province */
        $province = $model;

        $data = dto($data, UpdateProvinceRequest::class, [
            'province_id' => $province->id,
            'country_id'  => $data['country_id'] ?? $province->country_id,
        ]);
    }
}
