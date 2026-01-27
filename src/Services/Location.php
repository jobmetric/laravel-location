<?php

namespace JobMetric\Location\Services;

use Illuminate\Support\Facades\DB;
use JobMetric\Location\Events\Location\LocationStoreEvent;
use JobMetric\Location\Http\Requests\StoreLocationRequest;
use JobMetric\Location\Http\Resources\LocationResource;
use JobMetric\Location\Models\Location as LocationModel;
use JobMetric\PackageCore\Output\Response;
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
     * Disable update operations (Location records should never be updated).
     *
     * @var bool
     */
    protected bool $hasUpdate = false;

    /**
     * Disable delete operations (Location records should never be deleted).
     *
     * @var bool
     */
    protected bool $hasDelete = false;

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
    protected static string $resourceClass = LocationResource::class;

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
     * Domain events mapping for CRUD lifecycle.
     *
     * @var class-string|null
     */
    protected static ?string $storeEventClass = LocationStoreEvent::class;

    /**
     * Override store() to ensure uniqueness: use firstOrCreate to prevent duplicates.
     *
     * Since Location records are never deleted, we must check for existing records
     * with the same combination of country_id, province_id, city_id, district_id.
     *
     * @param array<string,mixed> $data Location data
     * @param array<string> $with       Relations to eager load
     *
     * @return Response
     * @throws Throwable
     */
    protected function doStore(array $data, array $with = []): Response
    {
        return DB::transaction(function () use ($data, $with) {
            $this->changeFieldStore($data);

            // Check if location already exists with the same combination
            $location = LocationModel::firstOrCreate([
                'country_id'  => $data['country_id'],
                'province_id' => $data['province_id'] ?? null,
                'city_id'     => $data['city_id'] ?? null,
                'district_id' => $data['district_id'] ?? null,
            ]);

            // If location was just created, fire store event
            if ($location->wasRecentlyCreated) {
                $this->beforeCommon('store', $location, $data);
                $this->beforeStore($location, $data);
                $this->afterStore($location, $data);
                $this->afterCommon('store', $location, $data);

                $this->fireStoreEvent($location, $data);
            }

            $resourceInstance = $this->resource::make($location->load($with));

            $additional = $this->additionalForMutation($location, $data, 'store');
            if (! is_null($additional)) {
                $resourceInstance = $resourceInstance->additional($additional);
            }

            if ($location->wasRecentlyCreated) {
                return Response::make(true, trans('package-core::base.messages.created', [
                    'entity' => trans($this->entityName),
                ]), $resourceInstance, 201);
            }

            // Location already exists, return it (no event fired, no hooks called)
            return Response::make(true, trans('location::base.messages.found', [
                'name' => trans($this->entityName),
            ]), $resourceInstance);
        });
    }

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
}
