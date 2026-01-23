<?php

namespace JobMetric\Location;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use JobMetric\Location\Facades\Address as AddressFacade;
use JobMetric\Location\Http\Resources\LocationAddressResource;
use JobMetric\Location\Models\Address;
use JobMetric\Location\Models\City;
use JobMetric\Location\Models\Country;
use JobMetric\Location\Models\District;
use JobMetric\Location\Models\Province;
use Throwable;

/**
 * Trait HasAddress
 *
 * @package JobMetric\Location
 *
 * @property Address addresses
 * @property Country addressLocationCountry
 * @property Province addressLocationProvince
 * @property City addressLocationCity
 * @property District addressLocationDistrict
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
        return $this->morphMany(Address::class, 'owner');
    }

    /**
     * Location Country relationship
     *
     * @return BelongsTo
     * @throws Throwable
     */
    public function addressLocationCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'id');
    }

    /**
     * Location Province relationship
     *
     * @return BelongsTo
     * @throws Throwable
     */
    public function addressLocationProvince(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'id');
    }

    /**
     * Location City relationship
     *
     * @return BelongsTo
     * @throws Throwable
     */
    public function addressLocationCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'id');
    }

    /**
     * Location District relationship
     *
     * @return BelongsTo
     *
     * @throws Throwable
     */
    public function addressLocationDistrict(): BelongsTo
    {
        return $this->belongsTo(District::class, 'id');
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
        $response = AddressFacade::store($this, $data);

        return [
            'ok'      => $response->isOk(),
            'message' => $response->getMessage(),
            'data'    => $response->getData(),
            'errors'  => $response->getErrors(),
            'status'  => $response->getStatus(),
        ];
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
    public function updateAddress(int $address_id, array $data): array
    {
        $flag = false;

        $addresses = $this->addresses()->get();

        foreach ($addresses as $address) {
            if ($address->id === $address_id) {
                $flag = true;
                break;
            }
        }

        if ($flag) {
            $response = AddressFacade::update($address_id, $data);

            return [
                'ok'      => $response->isOk(),
                'message' => $response->getMessage(),
                'data'    => $response->getData(),
                'errors'  => $response->getErrors(),
                'status'  => $response->getStatus(),
            ];
        }
        else {
            return [
                'ok'      => false,
                'message' => trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.address')]),
                'error'   => [
                    'address_id' => trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.address')]),
                ],
                'status'  => 422,
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
        return LocationAddressResource::collection($this->addresses()->get());
    }

    /**
     * Forget address
     *
     * @param int $location_address_id
     *
     * @return bool
     * @throws Throwable
     */
    public function forgetAddress(int $address_id): bool
    {
        $flag = false;

        $addresses = $this->addresses()->get();

        foreach ($addresses as $address) {
            if ($address->id === $address_id) {
                $flag = true;
                break;
            }
        }

        if ($flag) {
            Address::query()->where('id', $address_id)->delete();

            return true;
        }

        return false;
    }
}
