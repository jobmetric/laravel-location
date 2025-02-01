<?php

namespace JobMetric\Location\Services;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use JobMetric\Location\Events\Address\AddressDeleteEvent;
use JobMetric\Location\Events\Address\AddressForceDeleteEvent;
use JobMetric\Location\Events\Address\AddressRestoreEvent;
use JobMetric\Location\Events\Address\AddressStoreEvent;
use JobMetric\Location\Events\Address\AddressUpdateEvent;
use JobMetric\Location\HasAddress;
use JobMetric\Location\Http\Requests\StoreAddressRequest;
use JobMetric\Location\Http\Requests\UpdateAddressRequest;
use JobMetric\Location\Http\Resources\LocationAddressResource;
use JobMetric\Location\Models\LocationAddress;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

class AddressManager
{
    /**
     * The application instance.
     *
     * @var Application
     */
    protected Application $app;

    /**
     * Create a new country instance.
     *
     * @param Application $app
     *
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get the specified location address.
     *
     * @param array $filter
     * @param array $with
     * @param string|null $mode
     *
     * @return QueryBuilder
     */
    public function query(array $filter = [], array $with = [], string $mode = null): QueryBuilder
    {
        $fields = [
            'id',
            'addressable_type',
            'addressable_id',
            'location_country_id',
            'location_province_id',
            'location_city_id',
            'location_district_id',
            'address',
            'pluck',
            'unit',
            'postcode',
            'lat',
            'lng',
            'info'
        ];

        $query = QueryBuilder::for(LocationAddress::class);

        if ($mode === 'withTrashed') {
            $query->withTrashed();
        }

        if ($mode === 'onlyTrashed') {
            $query->onlyTrashed();
        }

        $query->allowedFields($fields)
            ->allowedSorts($fields)
            ->allowedFilters($fields)
            ->defaultSort('-id')
            ->where($filter);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query;
    }

    /**
     * Paginate the specified location addresses.
     *
     * @param array $filter
     * @param int $page_limit
     * @param array $with
     * @param string|null $mode
     *
     * @return AnonymousResourceCollection
     */
    public function paginate(array $filter = [], int $page_limit = 15, array $with = [], string $mode = null): AnonymousResourceCollection
    {
        return LocationAddressResource::collection(
            $this->query($filter, $with, $mode)->paginate($page_limit)
        );
    }

    /**
     * Get all location addresses.
     *
     * @param array $filter
     * @param array $with
     * @param string|null $mode
     *
     * @return AnonymousResourceCollection
     */
    public function all(array $filter = [], array $with = [], string $mode = null): AnonymousResourceCollection
    {
        return LocationAddressResource::collection(
            $this->query($filter, $with, $mode)->get()
        );
    }

    /**
     * Get the specified location address.
     *
     * @param int $location_address_id
     * @param array $with
     * @param string|null $mode
     *
     * @return array
     */
    public function get(int $location_address_id, array $with = [], string $mode = null): array
    {
        if ($mode === 'withTrashed') {
            $query = LocationAddress::withTrashed();
        } else if ($mode === 'onlyTrashed') {
            $query = LocationAddress::onlyTrashed();
        } else {
            $query = LocationAddress::query();
        }

        $query->where('id', $location_address_id);

        if (!empty($with)) {
            $query->with($with);
        }

        $location_address = $query->first();

        if (!$location_address) {
            return [
                'ok' => false,
                'message' => trans('package-core::base.validation.errors'),
                'errors' => [
                    trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.address')])
                ],
                'status' => 404
            ];
        }

        return [
            'ok' => true,
            'message' => trans('location::base.messages.found', ['name' => trans('location::base.model_name.address')]),
            'data' => LocationAddressResource::make($location_address),
            'status' => 200
        ];
    }

    /**
     * Store the specified location address.
     *
     * @param Model $model
     * @param array $data
     *
     * @return array
     * @throws Throwable
     */
    public function store(Model $model, array $data): array
    {
        if (!class_uses($model, HasAddress::class)) {
            return [
                'ok' => false,
                'message' => trans('location::base.validation.model_not_use_trait', ['model' => get_class($model)]),
                'errors' => [],
                'status' => 422
            ];
        }

        $validator = Validator::make($data, (new StoreAddressRequest)->rules());
        if ($validator->fails()) {
            $errors = $validator->errors()->all();

            return [
                'ok' => false,
                'message' => trans('package-core::base.validation.errors'),
                'errors' => $errors,
                'status' => 422
            ];
        } else {
            $data = $validator->validated();

            $address = new LocationAddress;

            $address->addressable()->associate($model);
            $address->location_country_id = $data['country_id'];
            $address->location_province_id = $data['province_id'];
            $address->location_city_id = $data['city_id'];
            $address->location_district_id = $data['district_id'] ?? null;
            $address->address = $data['address'];
            $address->pluck = $data['pluck'] ?? null;
            $address->unit = $data['unit'] ?? null;
            $address->postcode = $data['postcode'] ?? null;
            $address->lat = $data['lat'] ?? null;
            $address->lng = $data['lng'] ?? null;
            $address->info = $data['info'] ?? [];

            $address->save();

            event(new AddressStoreEvent($address, $data));

            return [
                'ok' => true,
                'message' => trans('location::base.messages.created', ['name' => trans('location::base.model_name.address')]),
                'data' => LocationAddressResource::make($address),
                'status' => 201
            ];
        }
    }

    /**
     * Update the specified location address.
     *
     * @param int $location_address_id
     * @param array $data
     *
     * @return array
     */
    public function update(int $location_address_id, array $data): array
    {
        $validator = Validator::make($data, (new UpdateAddressRequest())->rules());
        if ($validator->fails()) {
            $errors = $validator->errors()->all();

            return [
                'ok' => false,
                'message' => trans('package-core::base.validation.errors'),
                'errors' => $errors,
                'status' => 422
            ];
        } else {
            $data = $validator->validated();
        }

        return DB::transaction(function () use ($location_address_id, $data) {
            /**
             * @var LocationAddress $location_address
             */
            $location_address = LocationAddress::query()->where('id', $location_address_id)->first();

            if (!$location_address) {
                return [
                    'ok' => false,
                    'message' => trans('package-core::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.address')])
                    ],
                    'status' => 404
                ];
            }

            if (array_key_exists('location_country_id', $data)) {
                $location_address->location_country_id = $data['location_country_id'];
            }

            if (array_key_exists('location_province_id', $data)) {
                $location_address->location_province_id = $data['location_province_id'];
            }

            if (array_key_exists('location_city_id', $data)) {
                $location_address->location_city_id = $data['location_city_id'];
            }

            if (array_key_exists('location_district_id', $data)) {
                $location_address->location_district_id = $data['location_district_id'];
            }

            if (array_key_exists('address', $data)) {
                $location_address->address = $data['address'];
            }

            if (array_key_exists('pluck', $data)) {
                $location_address->pluck = $data['pluck'];
            }

            if (array_key_exists('unit', $data)) {
                $location_address->unit = $data['unit'];
            }

            if (array_key_exists('postcode', $data)) {
                $location_address->postcode = $data['postcode'];
            }

            if (array_key_exists('lat', $data)) {
                $location_address->lat = $data['lat'];
            }

            if (array_key_exists('lng', $data)) {
                $location_address->lng = $data['lng'];
            }

            if (array_key_exists('info', $data)) {
                $location_address->info = $data['info'];
            }

            $location_address->save();

            event(new AddressUpdateEvent($location_address, $data));

            return [
                'ok' => true,
                'message' => trans('location::base.messages.updated', ['name' => trans('location::base.model_name.address')]),
                'data' => LocationAddressResource::make($location_address),
                'status' => 200
            ];
        });
    }

    /**
     * Delete the specified location address.
     *
     * @param int $location_address_id
     *
     * @return array
     */
    public function delete(int $location_address_id): array
    {
        return DB::transaction(function () use ($location_address_id) {
            /**
             * @var LocationAddress $location_address
             */
            $location_address = LocationAddress::query()->where('id', $location_address_id)->first();

            if (!$location_address) {
                return [
                    'ok' => false,
                    'message' => trans('package-core::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.address')])
                    ],
                    'status' => 404
                ];
            }

            event(new AddressDeleteEvent($location_address));

            $data = LocationAddressResource::make($location_address);

            $location_address->delete();

            return [
                'ok' => true,
                'message' => trans('location::base.messages.deleted', ['name' => trans('location::base.model_name.address')]),
                'data' => $data,
                'status' => 200
            ];
        });
    }

    /**
     * Restore the specified location address.
     *
     * @param int $location_address_id
     *
     * @return array
     */
    public function restore(int $location_address_id): array
    {
        return DB::transaction(function () use ($location_address_id) {
            /**
             * @var LocationAddress $location_address
             */
            $location_address = LocationAddress::onlyTrashed()->where('id', $location_address_id)->first();

            if (!$location_address) {
                return [
                    'ok' => false,
                    'message' => trans('package-core::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.address')])
                    ],
                    'status' => 404
                ];
            }

            event(new AddressRestoreEvent($location_address));

            $data = LocationAddressResource::make($location_address);

            $location_address->restore();

            return [
                'ok' => true,
                'data' => $data,
                'message' => trans('location::base.messages.restored', ['name' => trans('location::base.model_name.address')]),
                'status' => 200
            ];
        });
    }

    /**
     * Force delete the specified location address.
     *
     * @param int $location_address_id
     *
     * @return array
     */
    public function forceDelete(int $location_address_id): array
    {
        return DB::transaction(function () use ($location_address_id) {
            /**
             * @var LocationAddress $location_address
             */
            $location_address = LocationAddress::onlyTrashed()->where('id', $location_address_id)->first();

            if (!$location_address) {
                return [
                    'ok' => false,
                    'message' => trans('package-core::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.address')])
                    ],
                    'status' => 404
                ];
            }

            event(new AddressForceDeleteEvent($location_address));

            $data = LocationAddressResource::make($location_address);

            $location_address->forceDelete();

            return [
                'ok' => true,
                'data' => $data,
                'message' => trans('location::base.messages.permanently_deleted', ['name' => trans('location::base.model_name.address')]),
                'status' => 200
            ];
        });
    }
}
