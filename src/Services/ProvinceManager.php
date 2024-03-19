<?php

namespace JobMetric\Location\Services;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use JobMetric\Location\Events\Province\ProvinceDeleteEvent;
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
     * @return QueryBuilder
     */
    public function query(array $filter = []): QueryBuilder
    {
        $fields = ['id', 'name', 'location_country_id', 'status'];

        return QueryBuilder::for(LocationProvince::class)
            ->allowedFields($fields)
            ->allowedSorts($fields)
            ->allowedFilters($fields)
            ->defaultSort('-id')
            ->where($filter);
    }

    /**
     * Paginate the specified location provinces.
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
     * Get all location provinces.
     *
     * @param array $filter
     * @return Collection
     */
    public function all(array $filter = []): Collection
    {
        return $this->query($filter)->get();
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
        $validator = Validator::make($data, (new StoreProvinceRequest)->rules());
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
            $province = new LocationProvince;
            $province->{config('location.foreign_key.country')} = $data['location_country_id'];
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
        $validator = Validator::make($data, (new UpdateProvinceRequest)->setLocationProvinceId($location_province_id)->rules());
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

        return DB::transaction(function () use ($location_province_id, $data) {
            /**
             * @var LocationProvince $location_province
             */
            $location_province = LocationProvince::query()->where('id', $location_province_id)->first();

            if (!$location_province) {
                return [
                    'ok' => false,
                    'message' => trans('location::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.province')])
                    ]
                ];
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
                'data' => LocationProvinceResource::make($location_province)
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
                    'message' => trans('location::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.province')])
                    ]
                ];
            }

            event(new ProvinceDeleteEvent($location_province));

            $data = LocationProvinceResource::make($location_province);

            $location_province->delete();

            return [
                'ok' => true,
                'data' => $data,
                'message' => trans('location::base.messages.deleted', ['name' => trans('location::base.model_name.province')])
            ];
        });
    }
}
