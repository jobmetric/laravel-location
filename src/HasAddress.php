<?php

namespace JobMetric\Location;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use JobMetric\Location\Facades\Address as AddressFacade;
use JobMetric\Location\Http\Resources\AddressResource;
use JobMetric\Location\Models\Address;
use JobMetric\Location\Models\AddressRelation;
use Throwable;

/**
 * Trait HasAddress
 *
 * Provides address management functionality to Eloquent models via address_relations pivot table.
 * A model can have multiple addresses attached with optional collection categorization.
 *
 * @property-read Collection<int, AddressRelation> $addressRelations
 *
 * @method MorphMany morphMany(string $class, string $string)
 *
 * @package JobMetric\Location
 */
trait HasAddress
{
    /**
     * Address relations - pivot table connecting addresses to this model.
     *
     * @return MorphMany
     */
    public function addressRelations(): MorphMany
    {
        return $this->morphMany(AddressRelation::class, 'addressable');
    }

    /**
     * Get all addresses for this model (through address_relations).
     *
     * @param string|null $collection Filter by collection name (null = all)
     * @param bool $withTrashed Include soft-deleted addresses
     *
     * @return Collection
     */
    public function addresses(?string $collection = null, bool $withTrashed = false): Collection
    {
        $query = $this->addressRelations();

        if (! is_null($collection)) {
            $query->where('collection', $collection);
        }

        if ($withTrashed) {
            $query->with(['address' => fn($q) => $q->withTrashed()]);
        } else {
            $query->with('address');
        }

        return $query->get()->pluck('address')->filter();
    }

    /**
     * Check if this model has a specific address attached.
     *
     * @param int $address_id
     * @param string|null $collection
     *
     * @return bool
     */
    public function hasAddress(int $address_id, ?string $collection = null): bool
    {
        $query = $this->addressRelations()->where('address_id', $address_id);

        if (! is_null($collection)) {
            $query->where('collection', $collection);
        }

        return $query->exists();
    }

    /**
     * Get all addresses as resource collection.
     *
     * @param string|null $collection
     * @param bool $withTrashed Include soft-deleted addresses
     *
     * @return AnonymousResourceCollection
     */
    public function getAddresses(?string $collection = null, bool $withTrashed = false): AnonymousResourceCollection
    {
        return AddressResource::collection($this->addresses($collection, $withTrashed));
    }

    /**
     * Get a specific address by ID (if attached to this model).
     *
     * @param int $address_id
     * @param string|null $collection
     * @param bool $withTrashed Include soft-deleted address
     *
     * @return AddressResource|null
     */
    public function getAddressById(int $address_id, ?string $collection = null, bool $withTrashed = false): ?AddressResource
    {
        if (! $this->hasAddress($address_id, $collection)) {
            return null;
        }

        $query = Address::query();

        if ($withTrashed) {
            $query->withTrashed();
        }

        $address = $query->find($address_id);

        return $address ? AddressResource::make($address) : null;
    }

    /**
     * Store a new address and attach it to this model.
     *
     * @param array $data             Address data
     * @param string|null $collection Collection name (billing, shipping, etc.)
     *
     * @return static
     * @throws Throwable
     */
    public function storeAddress(array $data, ?string $collection = null): static
    {
        $payload = array_merge([
            'owner_type' => get_class($this),
            'owner_id'   => $this->getKey(),
        ], $data);

        $response = AddressFacade::store($payload);

        if ($response->ok && $response->data) {
            $this->attachAddress($response->data->id, $collection);
        }

        return $this;
    }

    /**
     * Attach an existing address to this model.
     * Only non-deleted addresses can be attached.
     *
     * @param int $address_id
     * @param string|null $collection
     *
     * @return static
     */
    public function attachAddress(int $address_id, ?string $collection = null): static
    {
        // Only attach if address exists and is NOT soft-deleted
        $address = Address::find($address_id);

        if ($address && ! $this->hasAddress($address_id, $collection)) {
            $this->addressRelations()->create([
                'address_id' => $address_id,
                'collection' => $collection,
            ]);
        }

        return $this;
    }

    /**
     * Detach an address from this model (does not delete the address).
     *
     * @param int $address_id
     * @param string|null $collection
     *
     * @return static
     */
    public function detachAddress(int $address_id, ?string $collection = null): static
    {
        $query = $this->addressRelations()->where('address_id', $address_id);

        if (! is_null($collection)) {
            $query->where('collection', $collection);
        }

        $query->delete();

        return $this;
    }

    /**
     * Detach all addresses from this model.
     *
     * @param string|null $collection If provided, only detach from this collection
     *
     * @return static
     */
    public function detachAllAddresses(?string $collection = null): static
    {
        $query = $this->addressRelations();

        if (! is_null($collection)) {
            $query->where('collection', $collection);
        }

        $query->delete();

        return $this;
    }

    /**
     * Update an existing address (if attached to this model).
     *
     * @param int $address_id
     * @param array $data
     *
     * @return static
     * @throws Throwable
     */
    public function updateAddress(int $address_id, array $data): static
    {
        if ($this->hasAddress($address_id)) {
            AddressFacade::update($address_id, $data);
        }

        return $this;
    }

    /**
     * Delete an address (if attached to this model).
     * This soft-deletes the address and removes the relation.
     *
     * @param int $address_id
     *
     * @return static
     * @throws Throwable
     */
    public function deleteAddress(int $address_id): static
    {
        if ($this->hasAddress($address_id)) {
            AddressFacade::destroy($address_id);
            $this->detachAddress($address_id);
        }

        return $this;
    }

    /**
     * Sync addresses for a collection - detach all and attach the given ones.
     *
     * @param array $address_ids
     * @param string|null $collection
     *
     * @return static
     */
    public function syncAddresses(array $address_ids, ?string $collection = null): static
    {
        $this->detachAllAddresses($collection);

        foreach ($address_ids as $address_id) {
            $this->attachAddress($address_id, $collection);
        }

        return $this;
    }

    /**
     * Get address by collection (first match).
     *
     * @param string $collection
     * @param bool $withTrashed Include soft-deleted address
     *
     * @return Address|null
     */
    public function getAddressByCollection(string $collection, bool $withTrashed = false): ?Address
    {
        $query = $this->addressRelations()->where('collection', $collection);

        if ($withTrashed) {
            $query->with(['address' => fn($q) => $q->withTrashed()]);
        } else {
            $query->with('address');
        }

        $relation = $query->first();

        return $relation?->address;
    }

    /**
     * Alias for getAddresses() for backward compatibility.
     *
     * @return AnonymousResourceCollection
     * @deprecated Use getAddresses() instead
     */
    public function getAddress(): AnonymousResourceCollection
    {
        return $this->getAddresses();
    }

    /**
     * Alias for deleteAddress() for backward compatibility.
     *
     * @param int $address_id
     *
     * @return bool
     * @throws Throwable
     * @deprecated Use deleteAddress() or detachAddress() instead
     */
    public function forgetAddress(int $address_id): bool
    {
        if (! $this->hasAddress($address_id)) {
            return false;
        }

        $this->deleteAddress($address_id);

        return true;
    }
}
