<?php

namespace JobMetric\Location\Services;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use JobMetric\Location\Events\Province\ProvinceDeleteEvent;
use JobMetric\Location\Events\Province\ProvinceForceDeleteEvent;
use JobMetric\Location\Events\Province\ProvinceRestoreEvent;
use JobMetric\Location\Events\Province\ProvinceStoreEvent;
use JobMetric\Location\Events\Province\ProvinceUpdateEvent;
use JobMetric\Location\Http\Requests\StoreProvinceRequest;
use JobMetric\Location\Http\Requests\UpdateProvinceRequest;
use JobMetric\Location\Http\Resources\LocationProvinceResource;
use JobMetric\Location\Models\LocationProvince;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

class ProvinceManager
{
    /**
     * The application instance.
     *
     * @var Application
     */
    protected Application $app;

    /**
     * Create a new province instance.
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
     * Get the specified location province.
     *
     * @param array $filter
     * @param array $with
     * @param string|null $mode
     *
     * @return QueryBuilder
     */
    public function query(array $filter = [], array $with = [], string $mode = null): QueryBuilder
    {
        $fields = ['id', 'name', 'location_country_id', 'status'];

        $query = QueryBuilder::for(LocationProvince::class);

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
     * Paginate the specified location provinces.
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
        return LocationProvinceResource::collection(
            $this->query($filter, $with, $mode)->paginate($page_limit)
        );
    }

    /**
     * Get all location provinces.
     *
     * @param array $filter
     * @param array $with
     * @param string|null $mode
     *
     * @return AnonymousResourceCollection
     */
    public function all(array $filter = [], array $with = [], string $mode = null): AnonymousResourceCollection
    {
        return LocationProvinceResource::collection(
            $this->query($filter, $with, $mode)->get()
        );
    }

    /**
     * Get the specified location province.
     *
     * @param int $location_province_id
     * @param array $with
     * @param string|null $mode
     *
     * @return array
     */
    public function get(int $location_province_id, array $with = [], string $mode = null): array
    {
        if ($mode === 'withTrashed') {
            $query = LocationProvince::withTrashed();
        } else if ($mode === 'onlyTrashed') {
            $query = LocationProvince::onlyTrashed();
        } else {
            $query = LocationProvince::query();
        }

        $query->where('id', $location_province_id);

        if (!empty($with)) {
            if (isset($with['locationCities'])) {
                $with['locationCities'] = function ($query) {
                    $query->where('status', true);
                };
            }

            if (isset($with['locationDistricts'])) {
                $with['locationDistricts'] = function ($query) {
                    $query->where('status', true);
                };
            }

            $query->with($with);
        }

        $location_province = $query->first();

        if (!$location_province) {
            return [
                'ok' => false,
                'message' => trans('package-core::base.validation.errors'),
                'errors' => [
                    trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.province')])
                ],
                'status' => 404
            ];
        }

        return [
            'ok' => true,
            'message' => trans('location::base.messages.found', ['name' => trans('location::base.model_name.province')]),
            'data' => LocationProvinceResource::make($location_province),
            'status' => 200
        ];
    }

    /**
     * Store the specified location province.
     *
     * @param array $data
     * @return array
     * @throws Throwable
     */
    public function store(array $data): array
    {
        $validator = Validator::make($data, (new StoreProvinceRequest)->setLocationCountryId($data['location_country_id'] ?? null)->rules());
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

        return DB::transaction(function () use ($data) {
            $province = new LocationProvince;
            $province->location_country_id = $data['location_country_id'];
            $province->name = $data['name'];
            $province->status = $data['status'] ?? true;
            $province->save();

            event(new ProvinceStoreEvent($province, $data));

            return [
                'ok' => true,
                'message' => trans('location::base.messages.created', ['name' => trans('location::base.model_name.province')]),
                'data' => LocationProvinceResource::make($province),
                'status' => 201
            ];
        });
    }

    /**
     * Update the specified location province.
     *
     * @param int $location_province_id
     * @param array $data
     * @return array
     */
    public function update(int $location_province_id, array $data): array
    {
        $validator = Validator::make($data, (new UpdateProvinceRequest)->setLocationProvinceId($location_province_id)->setLocationCountryId($data['location_country_id'] ?? null)->rules());
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

        return DB::transaction(function () use ($location_province_id, $data) {
            /**
             * @var LocationProvince $location_province
             */
            $location_province = LocationProvince::query()->where('id', $location_province_id)->first();

            if (!$location_province) {
                return [
                    'ok' => false,
                    'message' => trans('package-core::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.province')])
                    ],
                    'status' => 404
                ];
            }

            if (array_key_exists('location_country_id', $data)) {
                $location_province->location_country_id = $data['location_country_id'];
            }

            if (array_key_exists('name', $data)) {
                $location_province->name = $data['name'];
            }

            if (array_key_exists('status', $data)) {
                $location_province->status = $data['status'];
            }

            $location_province->save();

            event(new ProvinceUpdateEvent($location_province, $data));

            return [
                'ok' => true,
                'message' => trans('location::base.messages.updated', ['name' => trans('location::base.model_name.province')]),
                'data' => LocationProvinceResource::make($location_province),
                'status' => 200
            ];
        });
    }

    /**
     * Delete the specified location province.
     *
     * @param int $location_province_id
     * @return array
     */
    public function delete(int $location_province_id): array
    {
        return DB::transaction(function () use ($location_province_id) {
            /**
             * @var LocationProvince $location_province
             */
            $location_province = LocationProvince::query()->where('id', $location_province_id)->first();

            if (!$location_province) {
                return [
                    'ok' => false,
                    'message' => trans('package-core::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.province')])
                    ],
                    'status' => 404
                ];
            }

            event(new ProvinceDeleteEvent($location_province));

            $data = LocationProvinceResource::make($location_province);

            $location_province->delete();

            return [
                'ok' => true,
                'message' => trans('location::base.messages.deleted', ['name' => trans('location::base.model_name.province')]),
                'data' => $data,
                'status' => 200
            ];
        });
    }

    /**
     * Restore the specified location province.
     *
     * @param int $location_province_id
     *
     * @return array
     */
    public function restore(int $location_province_id): array
    {
        return DB::transaction(function () use ($location_province_id) {
            /**
             * @var LocationProvince $location_province
             */
            $location_province = LocationProvince::onlyTrashed()->where('id', $location_province_id)->first();

            if (!$location_province) {
                return [
                    'ok' => false,
                    'message' => trans('package-core::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.province')])
                    ],
                    'status' => 404
                ];
            }

            event(new ProvinceRestoreEvent($location_province));

            $data = LocationProvinceResource::make($location_province);

            $location_province->restore();

            return [
                'ok' => true,
                'data' => $data,
                'message' => trans('location::base.messages.restored', ['name' => trans('location::base.model_name.province')]),
                'status' => 200
            ];
        });
    }

    /**
     * Force delete the specified location province.
     *
     * @param int $location_province_id
     *
     * @return array
     */
    public function forceDelete(int $location_province_id): array
    {
        return DB::transaction(function () use ($location_province_id) {
            /**
             * @var LocationProvince $location_province
             */
            $location_province = LocationProvince::onlyTrashed()->where('id', $location_province_id)->first();

            if (!$location_province) {
                return [
                    'ok' => false,
                    'message' => trans('package-core::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.province')])
                    ],
                    'status' => 404
                ];
            }

            event(new ProvinceForceDeleteEvent($location_province));

            $data = LocationProvinceResource::make($location_province);

            $location_province->forceDelete();

            return [
                'ok' => true,
                'data' => $data,
                'message' => trans('location::base.messages.permanently_deleted', ['name' => trans('location::base.model_name.province')]),
                'status' => 200
            ];
        });
    }
}
