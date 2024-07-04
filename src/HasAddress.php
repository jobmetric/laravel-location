<?php

namespace JobMetric\Location;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use JobMetric\Location\Facades\LocationAddress as LocationAddressFacade;
use JobMetric\Location\Http\Resources\LocationAddressResource;
use JobMetric\Location\Models\LocationAddress;
use JobMetric\Location\Models\LocationCity;
use JobMetric\Location\Models\LocationCountry;
use JobMetric\Location\Models\LocationDistrict;
use JobMetric\Location\Models\LocationProvince;
use Throwable;

/**
 * Trait HasAddress
 *
 * @package JobMetric\Location
 *
 * @property LocationAddress addresses
 * @property LocationCountry addressLocationCountry
 * @property LocationProvince addressLocationProvince
 * @property LocationCity addressLocationCity
 * @property LocationDistrict addressLocationDistrict
 *
 * @method morphMany(string $class, string $string)
 * @method belongsTo(string $class, string $string)
 */
trait HasAddress
{
    /**
     * Address relationship
     *
     * @return MorphMany
     * @throws Throwable
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(LocationAddress::class, 'addressable');
    }

    /**
     * Location Country relationship
     *
     * @return BelongsTo
     * @throws Throwable
     */
    public function addressLocationCountry(): BelongsTo
    {
        return $this->belongsTo(LocationCountry::class, 'id');
    }

    /**
     * Location Province relationship
     *
     * @return BelongsTo
     * @throws Throwable
     */
    public function addressLocationProvince(): BelongsTo
    {
        return $this->belongsTo(LocationProvince::class, 'id');
    }

    /**
     * Location City relationship
     *
     * @return BelongsTo
     * @throws Throwable
     */
    public function addressLocationCity(): BelongsTo
    {
        return $this->belongsTo(LocationCity::class, 'id');
    }

    /**
     * Location District relationship
     *
     * @return BelongsTo
     * @throws Throwable
     */
    public function addressLocationDistrict(): BelongsTo
    {
        return $this->belongsTo(LocationDistrict::class, 'id');
    }

    /**
     * Store address
     *
     * @param array $data
     *
     * @return array
     * @throws Throwable
     */
    public function storeAddress(array $data): array
    {
        return LocationAddressFacade::store($this, $data);
    }

    /**
     * Update address
     *
     * @param int $location_address_id
     * @param array $data
     *
     * @return array
     * @throws Throwable
     */
    public function updateAddress(int $location_address_id, array $data): array
    {
        $flag = false;

        $location_addresses = $this->addresses()->get();

        foreach ($location_addresses as $location_address) {
            if ($location_address->id === $location_address_id) {
                $flag = true;
                break;
            }
        }

        if ($flag) {
            return LocationAddressFacade::update($location_address_id, $data);
        } else {
            return [
                'ok' => false,
                'message' => trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.address')]),
                'error' => [
                    'location_address_id' => trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.address')])
                ],
                'status' => 422
            ];
        }
    }

    /**
     * Get all addresses
     *
     * @throws Throwable
     */
    public function getAddress(): AnonymousResourceCollection
    {
        return LocationAddressResource::collection(
            $this->addresses()->get()
        );
    }

    /**
     * Forget address
     *
     * @param int $location_address_id
     *
     * @return bool
     * @throws Throwable
     */
    public function forgetAddress(int $location_address_id): bool
    {
        $flag = false;

        $location_addresses = $this->addresses()->get();

        foreach ($location_addresses as $location_address) {
            if ($location_address->id === $location_address_id) {
                $flag = true;
                break;
            }
        }

        if ($flag) {
            LocationAddress::query()->where('id', $location_address_id)->delete();

            return true;
        }

        return false;
    }
}
