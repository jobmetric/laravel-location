<?php

namespace JobMetric\Location\Services;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use JobMetric\Location\Events\Country\CountryDeleteEvent;
use JobMetric\Location\Events\Country\CountryStoreEvent;
use JobMetric\Location\Events\Country\CountryUpdateEvent;
use JobMetric\Location\Http\Requests\StoreCountryRequest;
use JobMetric\Location\Http\Requests\UpdateCountryRequest;
use JobMetric\Location\Http\Resources\LocationCountryResource;
use JobMetric\Location\Models\LocationCountry;
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
     * Store the specified location country.
     *
     * @param array $data
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
                'errors' => $errors
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
                'data' => LocationCountryResource::make($country)
            ];
        });
    }

    /**
     * Update the specified location country.
     *
     * @param int $location_country_id
     * @param array $data
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
                'errors' => $errors
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
                    ]
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
                'data' => LocationCountryResource::make($location_country)
            ];
        });
    }

    /**
     * Delete the specified location country.
     *
     * @param int $location_country_id
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
                    ]
                ];
            }

            event(new CountryDeleteEvent($location_country));

            $data = LocationCountryResource::make($location_country);

            $location_country->delete();

            return [
                'ok' => true,
                'data' => $data,
                'message' => trans('location::base.messages.deleted', ['name' => trans('location::base.model_name.country')])
            ];
        });
    }
}
