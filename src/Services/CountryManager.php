<?php

namespace JobMetric\Location\Services;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use JobMetric\Location\Events\Country\CountryStoreEvent;
use JobMetric\Location\Http\Requests\StoreCountryRequest;
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
     * Store the specified country.
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
                'data' => $country
            ];
        });
    }
}
