<?php

namespace JobMetric\Location\Services;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use JobMetric\Location\Events\Country\CountryDeleteEvent;
use JobMetric\Location\Events\Country\CountryForceDeleteEvent;
use JobMetric\Location\Events\Country\CountryRestoreEvent;
use JobMetric\Location\Events\Country\CountryStoreEvent;
use JobMetric\Location\Events\Country\CountryUpdateEvent;
use JobMetric\Location\Http\Requests\StoreCountryRequest;
use JobMetric\Location\Http\Requests\UpdateCountryRequest;
use JobMetric\Location\Http\Resources\LocationCountryResource;
use JobMetric\Location\Models\LocationCountry;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

class CountryManager
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
     * Get the specified location country.
     *
     * @param array $filter
     * @param array $with
     * @param string|null $mode
     *
     * @return QueryBuilder
     */
    public function query(array $filter = [], array $with = [], string $mode = null): QueryBuilder
    {
        $fields = ['id', 'name', 'flag', 'mobile_prefix', 'validation', 'status'];

        $query = QueryBuilder::for(LocationCountry::class);

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
     * Paginate the specified location countries.
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
        return LocationCountryResource::collection(
            $this->query($filter, $with, $mode)->paginate($page_limit)
        );
    }

    /**
     * Get all location countries.
     *
     * @param array $filter
     * @param array $with
     * @param string|null $mode
     *
     * @return AnonymousResourceCollection
     */
    public function all(array $filter = [], array $with = [], string $mode = null): AnonymousResourceCollection
    {
        return LocationCountryResource::collection(
            $this->query($filter, $with, $mode)->get()
        );
    }

    /**
     * Get the specified location country.
     *
     * @param int $location_country_id
     * @param array $with
     * @param string|null $mode
     *
     * @return array
     */
    public function get(int $location_country_id, array $with = [], string $mode = null): array
    {
        if ($mode === 'withTrashed') {
            $query = LocationCountry::withTrashed();
        } else if ($mode === 'onlyTrashed') {
            $query = LocationCountry::onlyTrashed();
        } else {
            $query = LocationCountry::query();
        }

        $query->where('id', $location_country_id);

        if (!empty($with)) {
            if (isset($with['locationProvinces'])) {
                $with['locationProvinces'] = function ($query) {
                    $query->where('status', true);
                };
            }

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

        $location_country = $query->first();

        if (!$location_country) {
            return [
                'ok' => false,
                'message' => trans('location::base.validation.errors'),
                'errors' => [
                    trans('location::base.validation.object_not_found')
                ],
                'status' => 404
            ];
        }

        return [
            'ok' => true,
            'message' => trans('location::base.messages.found', ['name' => trans('location::base.model_name.country')]),
            'data' => LocationCountryResource::make($location_country),
            'status' => 200
        ];
    }

    /**
     * Store the specified location country.
     *
     * @param array $data
     *
     * @return array
     * @throws Throwable
     */
    public function store(array $data): array
    {
        $validator = Validator::make($data, (new StoreCountryRequest)->rules());
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
            $country = new LocationCountry;
            $country->name = $data['name'];
            $country->flag = $data['flag'] ?? null;
            $country->mobile_prefix = $data['mobile_prefix'] ?? null;
            $country->validation = $data['validation'] ?? null;
            $country->status = $data['status'] ?? true;
            $country->save();

            event(new CountryStoreEvent($country, $data));

            return [
                'ok' => true,
                'message' => trans('location::base.messages.created', ['name' => trans('location::base.model_name.country')]),
                'data' => LocationCountryResource::make($country),
                'status' => 201
            ];
        });
    }

    /**
     * Update the specified location country.
     *
     * @param int $location_country_id
     * @param array $data
     *
     * @return array
     */
    public function update(int $location_country_id, array $data): array
    {
        $validator = Validator::make($data, (new UpdateCountryRequest)->setLocationCountryId($location_country_id)->rules());
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

        return DB::transaction(function () use ($location_country_id, $data) {
            /**
             * @var LocationCountry $location_country
             */
            $location_country = LocationCountry::query()->where('id', $location_country_id)->first();

            if (!$location_country) {
                return [
                    'ok' => false,
                    'message' => trans('location::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.country')])
                    ],
                    'status' => 404
                ];
            }

            if (array_key_exists('name', $data)) {
                $location_country->name = $data['name'];
            }

            if (array_key_exists('flag', $data)) {
                $location_country->flag = $data['flag'];
            }

            if (array_key_exists('mobile_prefix', $data)) {
                $location_country->mobile_prefix = $data['mobile_prefix'];
            }

            if (array_key_exists('validation', $data)) {
                $location_country->validation = $data['validation'];
            }

            if (array_key_exists('status', $data)) {
                $location_country->status = $data['status'];
            }

            $location_country->save();

            event(new CountryUpdateEvent($location_country, $data));

            return [
                'ok' => true,
                'message' => trans('location::base.messages.updated', ['name' => trans('location::base.model_name.country')]),
                'data' => LocationCountryResource::make($location_country),
                'status' => 200
            ];
        });
    }

    /**
     * Delete the specified location country.
     *
     * @param int $location_country_id
     *
     * @return array
     */
    public function delete(int $location_country_id): array
    {
        return DB::transaction(function () use ($location_country_id) {
            /**
             * @var LocationCountry $location_country
             */
            $location_country = LocationCountry::query()->where('id', $location_country_id)->first();

            if (!$location_country) {
                return [
                    'ok' => false,
                    'message' => trans('location::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.country')])
                    ],
                    'status' => 404
                ];
            }

            event(new CountryDeleteEvent($location_country));

            $data = LocationCountryResource::make($location_country);

            $location_country->delete();

            return [
                'ok' => true,
                'data' => $data,
                'message' => trans('location::base.messages.deleted', ['name' => trans('location::base.model_name.country')]),
                'status' => 200
            ];
        });
    }

    /**
     * Restore the specified location country.
     *
     * @param int $location_country_id
     *
     * @return array
     */
    public function restore(int $location_country_id): array
    {
        return DB::transaction(function () use ($location_country_id) {
            /**
             * @var LocationCountry $location_country
             */
            $location_country = LocationCountry::onlyTrashed()->where('id', $location_country_id)->first();

            if (!$location_country) {
                return [
                    'ok' => false,
                    'message' => trans('location::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.country')])
                    ],
                    'status' => 404
                ];
            }

            event(new CountryRestoreEvent($location_country));

            $data = LocationCountryResource::make($location_country);

            $location_country->restore();

            return [
                'ok' => true,
                'data' => $data,
                'message' => trans('location::base.messages.restored', ['name' => trans('location::base.model_name.country')]),
                'status' => 200
            ];
        });
    }

    /**
     * Force delete the specified location country.
     *
     * @param int $location_country_id
     *
     * @return array
     */
    public function forceDelete(int $location_country_id): array
    {
        return DB::transaction(function () use ($location_country_id) {
            /**
             * @var LocationCountry $location_country
             */
            $location_country = LocationCountry::onlyTrashed()->where('id', $location_country_id)->first();

            if (!$location_country) {
                return [
                    'ok' => false,
                    'message' => trans('location::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.country')])
                    ],
                    'status' => 404
                ];
            }

            event(new CountryForceDeleteEvent($location_country));

            $data = LocationCountryResource::make($location_country);

            $location_country->forceDelete();

            return [
                'ok' => true,
                'data' => $data,
                'message' => trans('location::base.messages.permanently_deleted', ['name' => trans('location::base.model_name.country')]),
                'status' => 200
            ];
        });
    }
}
