<?php

namespace JobMetric\Location\Services;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use JobMetric\Location\Events\City\CityDeleteEvent;
use JobMetric\Location\Events\City\CityForceDeleteEvent;
use JobMetric\Location\Events\City\CityRestoreEvent;
use JobMetric\Location\Events\City\CityStoreEvent;
use JobMetric\Location\Events\City\CityUpdateEvent;
use JobMetric\Location\Http\Requests\StoreCityRequest;
use JobMetric\Location\Http\Requests\UpdateCityRequest;
use JobMetric\Location\Http\Resources\LocationCityResource;
use JobMetric\Location\Models\LocationCity;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

class CityManager
{
    /**
     * The application instance.
     *
     * @var Application
     */
    protected Application $app;

    /**
     * Create a new city instance.
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
     * Get the specified location city.
     *
     * @param array $filter
     * @param array $with
     * @param string|null $mode
     *
     * @return QueryBuilder
     */
    public function query(array $filter = [], array $with = [], string $mode = null): QueryBuilder
    {
        $fields = ['id', 'name', config('location.foreign_key.country'), config('location.foreign_key.province'), 'status'];

        $query = QueryBuilder::for(LocationCity::class);

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
     * Paginate the specified location cities.
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
        return LocationCityResource::collection(
            $this->query($filter, $with, $mode)->paginate($page_limit)
        );
    }

    /**
     * Get all location cities.
     *
     * @param array $filter
     * @param array $with
     * @param string|null $mode
     *
     * @return AnonymousResourceCollection
     */
    public function all(array $filter = [], array $with = [], string $mode = null): AnonymousResourceCollection
    {
        return LocationCityResource::collection(
            $this->query($filter, $with, $mode)->get()
        );
    }

    /**
     * Get the specified location city.
     *
     * @param int $location_city_id
     * @param array $with
     * @param string|null $mode
     *
     * @return array
     */
    public function get(int $location_city_id, array $with = [], string $mode = null): array
    {
        if ($mode === 'withTrashed') {
            $query = LocationCity::withTrashed();
        } else if ($mode === 'onlyTrashed') {
            $query = LocationCity::onlyTrashed();
        } else {
            $query = LocationCity::query();
        }

        $query->where('id', $location_city_id);

        if (!empty($with)) {
            if (isset($with['locationDistricts'])) {
                $with['locationDistricts'] = function ($query) {
                    $query->where('status', true);
                };
            }

            $query->with($with);
        }

        $location_city = $query->first();

        if (!$location_city) {
            return [
                'ok' => false,
                'message' => trans('location::base.validation.errors'),
                'errors' => [
                    trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.city')])
                ],
                'status' => 404
            ];
        }

        return [
            'ok' => true,
            'message' => trans('location::base.messages.found', ['name' => trans('location::base.model_name.city')]),
            'data' => LocationCityResource::make($location_city),
            'status' => 200
        ];
    }

    /**
     * Store the specified location city.
     *
     * @param array $data
     *
     * @return array
     * @throws Throwable
     */
    public function store(array $data): array
    {
        $validator = Validator::make($data, (new StoreCityRequest)->setLocationProvinceId($data[config('location.foreign_key.province')] ?? null)->rules());
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
            $city = new LocationCity;
            $city->{config('location.foreign_key.country')} = $data[config('location.foreign_key.country')];
            $city->{config('location.foreign_key.province')} = $data[config('location.foreign_key.province')];
            $city->name = $data['name'];
            $city->status = $data['status'] ?? true;
            $city->save();

            event(new CityStoreEvent($city, $data));

            return [
                'ok' => true,
                'message' => trans('location::base.messages.created', ['name' => trans('location::base.model_name.city')]),
                'data' => LocationCityResource::make($city),
                'status' => 201
            ];
        });
    }

    /**
     * Update the specified location city.
     *
     * @param int $location_city_id
     * @param array $data
     *
     * @return array
     */
    public function update(int $location_city_id, array $data): array
    {
        $validator = Validator::make($data, (new UpdateCityRequest)->setLocationCityId($location_city_id)->setLocationProvinceId($data[config('location.foreign_key.province')] ?? null)->rules());
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

        return DB::transaction(function () use ($location_city_id, $data) {
            /**
             * @var LocationCity $location_city
             */
            $location_city = LocationCity::query()->where('id', $location_city_id)->first();

            if (!$location_city) {
                return [
                    'ok' => false,
                    'message' => trans('location::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.city')])
                    ],
                    'status' => 404
                ];
            }

            if (array_key_exists(config('location.foreign_key.province'), $data)) {
                $location_city->{config('location.foreign_key.province')} = $data[config('location.foreign_key.province')];
            }

            if (array_key_exists('name', $data)) {
                $location_city->name = $data['name'];
            }

            if (array_key_exists('status', $data)) {
                $location_city->status = $data['status'];
            }

            $location_city->save();

            event(new CityUpdateEvent($location_city, $data));

            return [
                'ok' => true,
                'message' => trans('location::base.messages.updated', ['name' => trans('location::base.model_name.city')]),
                'data' => LocationCityResource::make($location_city),
                'status' => 200
            ];
        });
    }

    /**
     * Delete the specified location city.
     *
     * @param int $location_city_id
     *
     * @return array
     */
    public function delete(int $location_city_id): array
    {
        return DB::transaction(function () use ($location_city_id) {
            /**
             * @var LocationCity $location_city
             */
            $location_city = LocationCity::query()->where('id', $location_city_id)->first();

            if (!$location_city) {
                return [
                    'ok' => false,
                    'message' => trans('location::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.city')])
                    ],
                    'status' => 404
                ];
            }

            event(new CityDeleteEvent($location_city));

            $data = LocationCityResource::make($location_city);

            $location_city->delete();

            return [
                'ok' => true,
                'message' => trans('location::base.messages.deleted', ['name' => trans('location::base.model_name.city')]),
                'data' => $data,
                'status' => 200
            ];
        });
    }

    /**
     * Restore the specified location city.
     *
     * @param int $location_city_id
     *
     * @return array
     */
    public function restore(int $location_city_id): array
    {
        return DB::transaction(function () use ($location_city_id) {
            /**
             * @var LocationCity $location_city
             */
            $location_city = LocationCity::onlyTrashed()->where('id', $location_city_id)->first();

            if (!$location_city) {
                return [
                    'ok' => false,
                    'message' => trans('location::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.city')])
                    ],
                    'status' => 404
                ];
            }

            event(new CityRestoreEvent($location_city));

            $data = LocationCityResource::make($location_city);

            $location_city->restore();

            return [
                'ok' => true,
                'data' => $data,
                'message' => trans('location::base.messages.restored', ['name' => trans('location::base.model_name.city')]),
                'status' => 200
            ];
        });
    }

    /**
     * Force delete the specified location city.
     *
     * @param int $location_city_id
     *
     * @return array
     */
    public function forceDelete(int $location_city_id): array
    {
        return DB::transaction(function () use ($location_city_id) {
            /**
             * @var LocationCity $location_city
             */
            $location_city = LocationCity::onlyTrashed()->where('id', $location_city_id)->first();

            if (!$location_city) {
                return [
                    'ok' => false,
                    'message' => trans('location::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.city')])
                    ],
                    'status' => 404
                ];
            }

            event(new CityForceDeleteEvent($location_city));

            $data = LocationCityResource::make($location_city);

            $location_city->forceDelete();

            return [
                'ok' => true,
                'data' => $data,
                'message' => trans('location::base.messages.permanently_deleted', ['name' => trans('location::base.model_name.city')]),
                'status' => 200
            ];
        });
    }
}
