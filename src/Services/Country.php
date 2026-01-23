<?php

namespace JobMetric\Location\Services;

use Illuminate\Database\Eloquent\Model;
use JobMetric\Location\Events\Country\CountryDeleteEvent;
use JobMetric\Location\Events\Country\CountryForceDeleteEvent;
use JobMetric\Location\Events\Country\CountryRestoreEvent;
use JobMetric\Location\Events\Country\CountryStoreEvent;
use JobMetric\Location\Events\Country\CountryUpdateEvent;
use JobMetric\Location\Http\Requests\StoreCountryRequest;
use JobMetric\Location\Http\Requests\UpdateCountryRequest;
use JobMetric\Location\Http\Resources\LocationCountryResource;
use JobMetric\Location\Models\Country as CountryModel;
use JobMetric\PackageCore\Services\AbstractCrudService;
use Throwable;

/**
 * Class Country
 *
 * CRUD and management service for Country entities.
 * Responsibilities:
 * - Validate & normalize payloads via DTO helpers
 * - Fire domain events and invalidate caches on mutations
 * - Provide helpers for status toggling
 *
 * @package JobMetric\Location\Services
 */
class Country extends AbstractCrudService
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
    protected string $entityName = 'location::base.model_name.country';

    /**
     * Bound model/resource classes for the base CRUD.
     *
     * @var class-string
     */
    protected static string $modelClass = CountryModel::class;
    protected static string $resourceClass = LocationCountryResource::class;

    /**
     * Allowed fields for selection/filter/sort in QueryBuilder.
     *
     * @var string[]
     */
    protected static array $fields = [
        'id',
        'name',
        'flag',
        'mobile_prefix',
        'validation',
        'address_on_letter',
        'status',
        'deleted_at',
        'created_at',
        'updated_at',
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
    protected static ?string $storeEventClass = CountryStoreEvent::class;
    protected static ?string $updateEventClass = CountryUpdateEvent::class;
    protected static ?string $deleteEventClass = CountryDeleteEvent::class;
    protected static ?string $restoreEventClass = CountryRestoreEvent::class;
    protected static ?string $forceDeleteEventClass = CountryForceDeleteEvent::class;

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
        $data = dto($data, StoreCountryRequest::class);
    }

    /**
     * Mutate/validate payload before update.
     *
     * Role: aligns input with update rules for the specific Country.
     *
     * @param Model $model
     * @param array<string,mixed> $data
     *
     * @return void
     * @throws Throwable
     */
    protected function changeFieldUpdate(Model $model, array &$data): void
    {
        /** @var CountryModel $country */
        $country = $model;

        $data = dto($data, UpdateCountryRequest::class, [
            'country_id' => $country->id,
        ]);
    }
}
