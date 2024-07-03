<?php

namespace JobMetric\Location\Services;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use JobMetric\Location\Events\City\CityDeleteEvent;
use JobMetric\Location\Events\City\CityForceDeleteEvent;
use JobMetric\Location\Events\City\CityRestoreEvent;
use JobMetric\Location\Events\City\CityUpdateEvent;
use JobMetric\Location\Events\District\DistrictDeleteEvent;
use JobMetric\Location\Events\District\DistrictForceDeleteEvent;
use JobMetric\Location\Events\District\DistrictRestoreEvent;
use JobMetric\Location\Events\District\DistrictStoreEvent;
use JobMetric\Location\Events\District\DistrictUpdateEvent;
use JobMetric\Location\Http\Requests\StoreDistrictRequest;
use JobMetric\Location\Http\Requests\UpdateCityRequest;
use JobMetric\Location\Http\Requests\UpdateDistrictRequest;
use JobMetric\Location\Http\Resources\LocationCityResource;
use JobMetric\Location\Http\Resources\LocationDistrictResource;
use JobMetric\Location\Models\LocationCity;
use JobMetric\Location\Models\LocationDistrict;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

class DistrictManager
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
     * Get the specified location district.
     *
     * @param array $filter
     * @param array $with
     * @param string|null $mode
     *
     * @return QueryBuilder
     */
    public function query(array $filter = [], array $with = [], string $mode = null): QueryBuilder
    {
        $fields = ['id', 'name', config('location.foreign_key.country'), config('location.foreign_key.province'), config('location.foreign_key.city'), 'status'];

        $query = QueryBuilder::for(LocationDistrict::class);

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
     * Paginate the specified location districts.
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
        return LocationDistrictResource::collection(
            $this->query($filter, $with, $mode)->paginate($page_limit)
        );
    }

    /**
     * Get all location districts.
     *
     * @param array $filter
     * @param array $with
     * @param string|null $mode
     *
     * @return AnonymousResourceCollection
     */
    public function all(array $filter = [], array $with = [], string $mode = null): AnonymousResourceCollection
    {
        return LocationDistrictResource::collection(
            $this->query($filter, $with, $mode)->get()
        );
    }

    /**
     * Get the specified location district.
     *
     * @param int $location_district_id
     * @param array $with
     * @param string|null $mode
     *
     * @return array
     */
    public function get(int $location_district_id, array $with = [], string $mode = null): array
    {
        if ($mode === 'withTrashed') {
            $query = LocationDistrict::withTrashed();
        } else if ($mode === 'onlyTrashed') {
            $query = LocationDistrict::onlyTrashed();
        } else {
            $query = LocationDistrict::query();
        }

        $query->where('id', $location_district_id);

        if (!empty($with)) {
            $query->with($with);
        }

        $location_district = $query->first();

        if (!$location_district) {
            return [
                'ok' => false,
                'message' => trans('location::base.validation.errors'),
                'errors' => [
                    trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.district')])
                ],
                'status' => 404
            ];
        }

        return [
            'ok' => true,
            'message' => trans('location::base.messages.found', ['name' => trans('location::base.model_name.district')]),
            'data' => LocationDistrictResource::make($location_district),
            'status' => 200
        ];
    }

    /**
     * Store the specified location district.
     *
     * @param array $data
     *
     * @return array
     * @throws Throwable
     */
    public function store(array $data): array
    {
        $validator = Validator::make($data, (new StoreDistrictRequest)->setLocationCityId($data[config('location.foreign_key.city')] ?? null)->rules());
        if ($validator->fails()) {
            $errors = $validator->errors()->all();

            return [
                'ok' => false,
                'message' => trans('location::base.validation.errors'),
                'errors' => $errors,
                'status' => 422
            ];
        } else {
            $data = $validator->validated();
        }

        return DB::transaction(function () use ($data) {
            $district = new LocationDistrict;
            $district->{config('location.foreign_key.country')} = $data[config('location.foreign_key.province')];
            $district->{config('location.foreign_key.province')} = $data[config('location.foreign_key.province')];
            $district->{config('location.foreign_key.city')} = $data[config('location.foreign_key.city')];
            $district->name = $data['name'];
            $district->status = $data['status'] ?? true;
            $district->save();

            event(new DistrictStoreEvent($district, $data));

            return [
                'ok' => true,
                'message' => trans('location::base.messages.created', ['name' => trans('location::base.model_name.district')]),
                'data' => LocationDistrictResource::make($district),
                'status' => 201
            ];
        });
    }

    /**
     * Update the specified location district.
     *
     * @param int $location_district_id
     * @param array $data
     *
     * @return array
     */
    public function update(int $location_district_id, array $data): array
    {
        $validator = Validator::make($data, (new UpdateDistrictRequest)->setLocationDistrictId($location_district_id)->setLocationCityId($data[config('location.foreign_key.city')] ?? null)->rules());
        if ($validator->fails()) {
            $errors = $validator->errors()->all();

            return [
                'ok' => false,
                'message' => trans('location::base.validation.errors'),
                'errors' => $errors,
                'status' => 422
            ];
        } else {
            $data = $validator->validated();
        }

        return DB::transaction(function () use ($location_district_id, $data) {
            /**
             * @var LocationDistrict $location_district
             */
            $location_district = LocationDistrict::query()->where('id', $location_district_id)->first();

            if (!$location_district) {
                return [
                    'ok' => false,
                    'message' => trans('location::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.district')])
                    ],
                    'status' => 404
                ];
            }

            if (array_key_exists(config('location.foreign_key.country'), $data)) {
                $location_district->{config('location.foreign_key.country')} = $data[config('location.foreign_key.country')];
            }

            if (array_key_exists(config('location.foreign_key.province'), $data)) {
                $location_district->{config('location.foreign_key.province')} = $data[config('location.foreign_key.province')];
            }

            if (array_key_exists(config('location.foreign_key.city'), $data)) {
                $location_district->{config('location.foreign_key.city')} = $data[config('location.foreign_key.city')];
            }

            if (array_key_exists('name', $data)) {
                $location_district->name = $data['name'];
            }

            if (array_key_exists('status', $data)) {
                $location_district->status = $data['status'];
            }

            $location_district->save();

            event(new DistrictUpdateEvent($location_district, $data));

            return [
                'ok' => true,
                'message' => trans('location::base.messages.updated', ['name' => trans('location::base.model_name.district')]),
                'data' => LocationDistrictResource::make($location_district),
                'status' => 200
            ];
        });
    }

    /**
     * Delete the specified location district.
     *
     * @param int $location_district_id
     *
     * @return array
     */
    public function delete(int $location_district_id): array
    {
        return DB::transaction(function () use ($location_district_id) {
            /**
             * @var LocationDistrict $location_district
             */
            $location_district = LocationDistrict::query()->where('id', $location_district_id)->first();

            if (!$location_district) {
                return [
                    'ok' => false,
                    'message' => trans('location::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.district')])
                    ],
                    'status' => 404
                ];
            }

            event(new DistrictDeleteEvent($location_district));

            $data = LocationDistrictResource::make($location_district);

            $location_district->delete();

            return [
                'ok' => true,
                'message' => trans('location::base.messages.deleted', ['name' => trans('location::base.model_name.district')]),
                'data' => $data,
                'status' => 200
            ];
        });
    }

    /**
     * Restore the specified location district.
     *
     * @param int $location_district_id
     *
     * @return array
     */
    public function restore(int $location_district_id): array
    {
        return DB::transaction(function () use ($location_district_id) {
            /**
             * @var LocationDistrict $location_district
             */
            $location_district = LocationDistrict::onlyTrashed()->where('id', $location_district_id)->first();

            if (!$location_district) {
                return [
                    'ok' => false,
                    'message' => trans('location::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.district')])
                    ],
                    'status' => 404
                ];
            }

            event(new DistrictRestoreEvent($location_district));

            $data = LocationDistrictResource::make($location_district);

            $location_district->restore();

            return [
                'ok' => true,
                'data' => $data,
                'message' => trans('location::base.messages.restored', ['name' => trans('location::base.model_name.district')]),
                'status' => 200
            ];
        });
    }

    /**
     * Force delete the specified location district.
     *
     * @param int $location_district_id
     *
     * @return array
     */
    public function forceDelete(int $location_district_id): array
    {
        return DB::transaction(function () use ($location_district_id) {
            /**
             * @var LocationDistrict $location_district
             */
            $location_district = LocationDistrict::onlyTrashed()->where('id', $location_district_id)->first();

            if (!$location_district) {
                return [
                    'ok' => false,
                    'message' => trans('location::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.district')])
                    ],
                    'status' => 404
                ];
            }

            event(new DistrictForceDeleteEvent($location_district));

            $data = LocationDistrictResource::make($location_district);

            $location_district->forceDelete();

            return [
                'ok' => true,
                'data' => $data,
                'message' => trans('location::base.messages.permanently_deleted', ['name' => trans('location::base.model_name.district')]),
                'status' => 200
            ];
        });
    }
}
