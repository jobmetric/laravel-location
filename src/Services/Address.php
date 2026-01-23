<?php

namespace JobMetric\Location\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use JobMetric\Location\Events\Address\AddressDeleteEvent;
use JobMetric\Location\Events\Address\AddressStoreEvent;
use JobMetric\Location\Events\Address\AddressUpdateEvent;
use JobMetric\Location\HasAddress;
use JobMetric\Location\Http\Requests\StoreAddressRequest;
use JobMetric\Location\Http\Requests\UpdateAddressRequest;
use JobMetric\Location\Http\Resources\LocationAddressResource;
use JobMetric\Location\Models\Address as AddressModel;
use JobMetric\PackageCore\Output\Response;
use JobMetric\PackageCore\Services\AbstractCrudService;
use Throwable;

/**
 * Class Address
 *
 * CRUD and management service for Address entities.
 * Responsibilities:
 * - Validate & normalize payloads via DTO helpers
 * - Fire domain events and invalidate caches on mutations
 * - Implement versioning: updates create new records with parent_id
 * - Prevent permanent deletion: only soft delete is allowed
 *
 * @package JobMetric\Location
 */
class Address extends AbstractCrudService
{
    /**
     * Enable soft-deletes for delete() operation.
     * Note: forceDelete and restore are disabled for addresses.
     *
     * @var bool
     */
    protected bool $softDelete = true;

    /**
     * Disable restore operation for addresses.
     *
     * @var bool
     */
    protected bool $hasRestore = false;

    /**
     * Disable forceDelete operation for addresses.
     *
     * @var bool
     */
    protected bool $hasForceDelete = false;

    /**
     * Disable toggleStatus (addresses don't have status field).
     *
     * @var bool
     */
    protected bool $hasToggleStatus = false;

    /**
     * Human-readable entity name key used in response messages.
     *
     * @var string
     */
    protected string $entityName = 'location::base.model_name.address';

    /**
     * Bound model/resource classes for the base CRUD.
     *
     * @var class-string
     */
    protected static string $modelClass = AddressModel::class;
    protected static string $resourceClass = LocationAddressResource::class;

    /**
     * Allowed fields for selection/filter/sort in QueryBuilder.
     *
     * @var string[]
     */
    protected static array $fields = [
        'id',
        'parent_id',
        'owner_type',
        'owner_id',
        'address',
        'postcode',
        'lat',
        'lng',
        'info',
        'deleted_at',
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
    protected static ?string $storeEventClass = AddressStoreEvent::class;
    protected static ?string $updateEventClass = AddressUpdateEvent::class;
    protected static ?string $deleteEventClass = AddressDeleteEvent::class;
    protected static ?string $restoreEventClass = null;
    protected static ?string $forceDeleteEventClass = null;

    /**
     * Store a new address for the given model.
     *
     * Override base store() to accept a Model parameter for polymorphic relation.
     *
     * @param Model $model              The model that owns this address
     * @param array<string,mixed> $data Address data
     *
     * @return Response
     * @throws Throwable
     */
    public function doStore(Model $model, array $data): Response
    {
        if (! class_uses($model, HasAddress::class)) {
            return Response::make()
                ->setOk(false)
                ->setMessage(trans('location::base.validation.model_not_use_trait', ['model' => get_class($model)]))
                ->setErrors([])
                ->setStatus(422);
        }

        return DB::transaction(function () use ($model, $data) {
            // Validate and normalize data
            $this->changeFieldStore($data);

            // Create new address
            $address = new AddressModel();
            $address->owner()->associate($model);
            $address->address = $data['address'] ?? [];
            $address->postcode = $data['postcode'] ?? null;
            $address->lat = $data['lat'] ?? null;
            $address->lng = $data['lng'] ?? null;
            $address->info = $data['info'] ?? [];

            // Create location relation if location data provided
            if (isset($data['country_id'])) {
                // Find or create Location
                $location = \JobMetric\Location\Models\Location::firstOrCreate([
                    'country_id'  => $data['country_id'],
                    'province_id' => $data['province_id'] ?? null,
                    'city_id'     => $data['city_id'] ?? null,
                    'district_id' => $data['district_id'] ?? null,
                ]);

                // Create LocationRelation
                $address->locationRelation()->create([
                    'location_id' => $location->id,
                ]);
            }

            $address->save();

            // Fire event
            $this->fireStoreEvent($address, $data);

            return Response::make()
                ->setOk(true)
                ->setMessage(trans('location::base.messages.created', ['name' => trans($this->entityName)]))
                ->setData(static::$resourceClass::make($address))
                ->setStatus(201);
        });
    }

    /**
     * Update an address using versioning pattern.
     *
     * Instead of updating the existing record, we:
     * 1. Soft delete the old record
     * 2. Create a new record with parent_id pointing to the old record
     * 3. Copy all data from old to new, then apply updates
     *
     * @param int $id                   Address ID to update
     * @param array<string,mixed> $data Update data
     * @param array<string> $with       Relations to eager load
     *
     * @return Response
     * @throws Throwable
     */
    public function doUpdate(int $id, array $data, array $with = []): Response
    {
        return DB::transaction(function () use ($id, $data, $with) {
            /** @var AddressModel|null $oldAddress */
            $oldAddress = AddressModel::query()->where('id', $id)->first();

            if (! $oldAddress) {
                return Response::make()
                    ->setOk(false)
                    ->setMessage(trans('package-core::base.validation.errors'))
                    ->setErrors([trans('location::base.validation.object_not_found', ['name' => trans($this->entityName)])])
                    ->setStatus(404);
            }

            // Validate and normalize update data
            $this->changeFieldUpdate($oldAddress, $data);

            // Always create new version when update is called (versioning pattern)
            // Soft delete the old address
            $oldAddress->delete();

            // Get old location data if exists
            $oldLocation = $oldAddress->locationRelation?->location;

            // Create new address with parent_id pointing to old address
            $newAddress = new AddressModel();
            $newAddress->parent_id = $oldAddress->id;
            $newAddress->owner_type = $oldAddress->owner_type;
            $newAddress->owner_id = $oldAddress->owner_id;

            // Copy existing data, then apply updates
            $newAddress->address = $data['address'] ?? $oldAddress->address;
            $newAddress->postcode = $data['postcode'] ?? $oldAddress->postcode;
            $newAddress->lat = $data['lat'] ?? $oldAddress->lat;
            $newAddress->lng = $data['lng'] ?? $oldAddress->lng;
            $newAddress->info = $data['info'] ?? $oldAddress->info;

            $newAddress->save();

            // Update location relation if provided
            if (isset($data['country_id'])) {
                // Find or create new Location
                $location = \JobMetric\Location\Models\Location::firstOrCreate([
                    'country_id'  => $data['country_id'],
                    'province_id' => $data['province_id'] ?? $oldLocation?->province_id,
                    'city_id'     => $data['city_id'] ?? $oldLocation?->city_id,
                    'district_id' => $data['district_id'] ?? $oldLocation?->district_id,
                ]);

                // Delete old location relation
                $oldAddress->locationRelation?->delete();

                // Create new location relation
                $newAddress->locationRelation()->create([
                    'location_id' => $location->id,
                ]);
            }
            else if ($oldAddress->locationRelation) {
                // If no location data provided but old address had location, copy it
                $newAddress->locationRelation()->create([
                    'location_id' => $oldLocation->id,
                ]);
            }

            // Fire update event with new address
            $this->fireUpdateEvent($newAddress, $data);

            // Eager load relations if requested
            if (! empty($with)) {
                $newAddress->load($with);
            }

            return Response::make()
                ->setOk(true)
                ->setMessage(trans('location::base.messages.updated', ['name' => trans($this->entityName)]))
                ->setData(static::$resourceClass::make($newAddress))
                ->setStatus(200);

        });
    }

    /**
     * Delete an address (soft delete only).
     *
     * Override to ensure only soft delete is performed, never force delete.
     *
     * @param int $id             Address ID to delete
     * @param array<string> $with Relations to eager load
     *
     * @return Response
     * @throws Throwable
     */
    public function doDestroy(int $id, array $with = []): Response
    {
        return DB::transaction(function () use ($id, $with) {
            /** @var AddressModel|null $address */
            $address = AddressModel::query()->where('id', $id)->first();

            if (! $address) {
                return Response::make()
                    ->setOk(false)
                    ->setMessage(trans('package-core::base.validation.errors'))
                    ->setErrors([trans('location::base.validation.object_not_found', ['name' => trans($this->entityName)])])
                    ->setStatus(404);
            }

            // Eager load relations before deletion
            if (! empty($with)) {
                $address->load($with);
            }

            $data = static::$resourceClass::make($address);

            // Fire delete event
            $this->fireDeleteEvent($address);

            // Soft delete only (never force delete)
            $address->delete();

            return Response::make()
                ->setOk(true)
                ->setMessage(trans('location::base.messages.deleted', ['name' => trans($this->entityName)]))
                ->setData($data)
                ->setStatus(200);
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
        $data = dto($data, StoreAddressRequest::class);
    }

    /**
     * Mutate/validate payload before update.
     *
     * Role: aligns input with update rules for the specific Address.
     *
     * @param Model $model
     * @param array<string,mixed> $data
     *
     * @return void
     * @throws Throwable
     */
    protected function changeFieldUpdate(Model $model, array &$data): void
    {
        /** @var AddressModel $address */
        $address = $model;

        $data = dto($data, UpdateAddressRequest::class, [
            'address_id' => $address->id,
        ]);
    }
}
