<?php

namespace JobMetric\Location\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use JobMetric\Location\Events\Address\AddressDeleteEvent;
use JobMetric\Location\Events\Address\AddressStoreEvent;
use JobMetric\Location\Events\Address\AddressUpdateEvent;
use JobMetric\Location\HasAddress;
use JobMetric\Location\Http\Requests\Address\StoreAddressRequest;
use JobMetric\Location\Http\Requests\Address\UpdateAddressRequest;
use JobMetric\Location\Http\Resources\AddressResource;
use JobMetric\Location\Models\Address as AddressModel;
use JobMetric\Location\Models\Location as LocationModel;
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
    protected static string $resourceClass = AddressResource::class;

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
     * Domain events mapping for CRUD lifecycle.
     *
     * @var class-string|null
     */
    protected static ?string $storeEventClass = AddressStoreEvent::class;
    protected static ?string $updateEventClass = AddressUpdateEvent::class;
    protected static ?string $deleteEventClass = AddressDeleteEvent::class;

    /**
     * Store a new address. Signature matches base store(array $data, array $with = []) for controller compatibility.
     *
     * @param array<string,mixed> $data Must contain owner_type and owner_id plus address fields.
     * @param array<string> $with       Eager-loaded relations after save.
     *
     * @return Response
     * @throws Throwable
     */
    public function doStore(array $data, array $with = []): Response
    {
        if (empty($data['owner_type']) || ! isset($data['owner_id'])) {
            return Response::make(false, trans('package-core::base.validation.errors'), null, 422, [
                trans('location::base.validation.address_owner_required'),
            ]);
        }

        $model = $data['owner_type']::findOrFail($data['owner_id']);

        if (! class_uses($model, HasAddress::class)) {
            return Response::make(false, trans('location::base.validation.model_not_use_trait', [
                'model' => get_class($model),
            ]), null, 422);
        }

        return DB::transaction(callback: function () use ($model, $data) {
            // Validate and normalize data
            $this->changeFieldStore($data);

            // Create new address
            $address = new AddressModel;
            $address->owner()->associate($model);
            $address->address = $data['address'] ?? [];
            $address->postcode = $data['postcode'] ?? null;
            $address->lat = $data['lat'] ?? null;
            $address->lng = $data['lng'] ?? null;
            $address->info = $data['info'] ?? [];

            // Save first so polymorphic relations can be created safely.
            $address->save();

            // Create location relation if location data provided
            if (isset($data['country_id'])) {
                if (! isset($data['province_id']) || ! isset($data['city_id'])) {
                    return Response::make(false, trans('package-core::base.validation.errors'), null, 422, [
                        trans('location::base.validation.province_and_city_required'),
                    ]);
                }

                // Find or create Location (ensures uniqueness)
                $location = LocationModel::firstOrCreate([
                    'country_id'  => $data['country_id'],
                    'province_id' => $data['province_id'],
                    'city_id'     => $data['city_id'],
                    'district_id' => $data['district_id'] ?? null,
                ]);

                // Create LocationRelation (single mode)
                $address->locationRelation()->create([
                    'location_id' => $location->id,
                ]);
            }

            // Fire event
            $this->fireStoreEvent($address, $data);

            return Response::make(true, trans('package-core::base.messages.created', [
                'entity' => trans($this->entityName),
            ]), static::$resourceClass::make($address), 201);
        });
    }

    /**
     * Update an address using versioning pattern.
     *
     * Only when at least one field (including location) has changed compared to the saved record:
     * 1. Soft delete the old record
     * 2. Create a new record with parent_id pointing to the old record
     * 3. Copy all data from old to new, then apply updates (including location if changed)
     *
     * If nothing changed, the existing record is returned without creating a new version.
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
            $oldAddress = AddressModel::query()->with(['locationRelation.location'])->where('id', $id)->first();

            if (! $oldAddress) {
                return Response::make(false, trans('package-core::base.validation.errors'), null, 404, [
                    trans('location::base.validation.object_not_found', [
                        'name' => trans($this->entityName),
                    ]),
                ]);
            }

            // Validate and normalize update data
            $this->changeFieldUpdate($oldAddress, $data);

            $oldLocation = $oldAddress->locationRelation?->location;

            // Detect if anything changed: only then do versioning (soft delete old + create new with parent_id)
            if (! $this->addressHasChanges($oldAddress, $oldLocation, $data)) {
                if (! empty($with)) {
                    $oldAddress->load($with);
                }

                return Response::make(true, trans('package-core::base.messages.updated', [
                    'entity' => trans($this->entityName),
                ]), static::$resourceClass::make($oldAddress));
            }

            // Versioning: soft delete the old address and create new with parent_id
            $oldAddress->delete();

            // Create new address with parent_id pointing to old address
            $newAddress = new AddressModel;
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
                if (! isset($data['province_id']) || ! isset($data['city_id'])) {
                    return Response::make(false, trans('package-core::base.validation.errors'), null, 422, [
                        trans('location::base.validation.province_and_city_required'),
                    ]);
                }

                // Find or create new Location
                $location = LocationModel::firstOrCreate([
                    'country_id'  => $data['country_id'],
                    'province_id' => $data['province_id'],
                    'city_id'     => $data['city_id'],
                    'district_id' => $data['district_id'] ?? $oldLocation?->district_id,
                ]);

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

            return Response::make(true, trans('package-core::base.messages.updated', [
                'entity' => trans($this->entityName),
            ]), static::$resourceClass::make($newAddress));
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
                return Response::make(false, trans('package-core::base.validation.errors'), null, 404, [
                    trans('location::base.validation.object_not_found', ['name' => trans($this->entityName)]),
                ]);
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

            return Response::make(true, trans('package-core::base.messages.deleted', [
                'entity' => trans($this->entityName),
            ]), $data);
        });
    }

    /**
     * Check if update data has any change compared to the saved address (and its location).
     *
     * @param AddressModel $oldAddress        Current address record
     * @param LocationModel|null $oldLocation Current location relation's location (if any)
     * @param array<string,mixed> $data       Validated update payload
     *
     * @return bool True when at least one field or location has changed
     */
    protected function addressHasChanges(AddressModel $oldAddress, ?LocationModel $oldLocation, array $data): bool
    {
        // Compare scalar/array fields (effective value after merge with old)
        $effectiveAddress = $data['address'] ?? $oldAddress->address;
        $effectivePostcode = $data['postcode'] ?? $oldAddress->postcode;
        $effectiveLat = $data['lat'] ?? $oldAddress->lat;
        $effectiveLng = $data['lng'] ?? $oldAddress->lng;
        $effectiveInfo = $data['info'] ?? $oldAddress->info;

        $effectiveAddress = is_array($effectiveAddress) ? $effectiveAddress : [];
        $oldAddressValue = is_array($oldAddress->address) ? $oldAddress->address : [];
        if (array_diff_assoc($effectiveAddress, $oldAddressValue) || array_diff_assoc($oldAddressValue, $effectiveAddress)) {
            return true;
        }

        if ((string) $effectivePostcode !== (string) ($oldAddress->postcode ?? '')) {
            return true;
        }

        if ((string) ($effectiveLat ?? '') !== (string) ($oldAddress->lat ?? '')) {
            return true;
        }

        if ((string) ($effectiveLng ?? '') !== (string) ($oldAddress->lng ?? '')) {
            return true;
        }

        $effectiveInfo = is_array($effectiveInfo) ? $effectiveInfo : [];
        $oldInfoValue = is_array($oldAddress->info) ? $oldAddress->info : [];
        if (array_diff_assoc($effectiveInfo, $oldInfoValue) || array_diff_assoc($oldInfoValue, $effectiveInfo)) {
            return true;
        }

        // Compare location: if payload sends location, compare with old location
        if (isset($data['country_id'])) {
            $newCountryId = (int) $data['country_id'];
            $newProvinceId = isset($data['province_id']) ? (int) $data['province_id'] : null;
            $newCityId = isset($data['city_id']) ? (int) $data['city_id'] : null;
            $newDistrictId = isset($data['district_id']) ? (int) $data['district_id'] : null;

            // old had no location, new has location
            if (! $oldLocation) {
                return true;
            }

            // Compare old vs new location ids
            if ((int) $oldLocation->country_id !== $newCountryId || (int) ($oldLocation->province_id ?? 0) !== ($newProvinceId ?? 0) || (int) ($oldLocation->city_id ?? 0) !== ($newCityId ?? 0) || (int) ($oldLocation->district_id ?? 0) !== ($newDistrictId ?? 0)) {
                return true;
            }
        }

        return false;
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
