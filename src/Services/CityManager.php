<?php

namespace JobMetric\Location\Services;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use JobMetric\Location\Events\City\CityDeleteEvent;
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
     * @return QueryBuilder
     */
    public function query(array $filter = []): QueryBuilder
    {
        $fields = ['id', 'name', config('location.foreign_key.country'), config('location.foreign_key.province'), 'status'];

        return QueryBuilder::for(LocationCity::class)
            ->allowedFields($fields)
            ->allowedSorts($fields)
            ->allowedFilters($fields)
            ->defaultSort('-id')
            ->where($filter);
    }

    /**
     * Paginate the specified location cities.
     *
     * @param array $filter
     * @param int $page_limit
     * @return LengthAwarePaginator
     */
    public function paginate(array $filter = [], int $page_limit = 15): LengthAwarePaginator
    {
        return $this->query($filter)->paginate($page_limit);
    }

    /**
     * Get all location cities.
     *
     * @param array $filter
     * @return Collection
     */
    public function all(array $filter = []): Collection
    {
        return $this->query($filter)->get();
    }

    /**
     * Store the specified location city.
     *
     * @param array $data
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
            $city->{config('location.foreign_key.country')} = $data[config('location.foreign_key.province')];
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
}
