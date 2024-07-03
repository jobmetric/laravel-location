<?php

namespace JobMetric\Location\Services;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use JobMetric\Location\Events\GeoArea\GeoAreaDeleteEvent;
use JobMetric\Location\Events\GeoArea\GeoAreaForceDeleteEvent;
use JobMetric\Location\Events\GeoArea\GeoAreaRestoreEvent;
use JobMetric\Location\Events\GeoArea\GeoAreaStoreEvent;
use JobMetric\Location\Events\GeoArea\GeoAreaUpdateEvent;
use JobMetric\Location\Http\Requests\StoreGeoAreaRequest;
use JobMetric\Location\Http\Requests\UpdateGeoAreaRequest;
use JobMetric\Location\Http\Resources\LocationCityResource;
use JobMetric\Location\Http\Resources\LocationGeoAreaResource;
use JobMetric\Location\Models\LocationGeoArea;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

class GeoAreaManager
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
     * Get the specified location geo area.
     *
     * @param array $filter
     * @param array $with
     * @param string|null $mode
     *
     * @return QueryBuilder
     */
    public function query(array $filter = [], array $with = [], string $mode = null): QueryBuilder
    {
        $fields = ['id', 'title', 'description', 'status'];

        $query = QueryBuilder::for(LocationGeoArea::class);

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
     * Paginate the specified location geo area.
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
        return LocationGeoAreaResource::collection(
            $this->query($filter, $with, $mode)->paginate($page_limit)
        );
    }

    /**
     * Get all location geo areas.
     *
     * @param array $filter
     * @param array $with
     * @param string|null $mode
     *
     * @return AnonymousResourceCollection
     */
    public function all(array $filter = [], array $with = [], string $mode = null): AnonymousResourceCollection
    {
        return LocationGeoAreaResource::collection(
            $this->query($filter, $with, $mode)->get()
        );
    }

    /**
     * Get the specified location geo area.
     *
     * @param int $location_geo_area_id
     * @param array $with
     * @param string|null $mode
     *
     * @return array
     */
    public function get(int $location_geo_area_id, array $with = [], string $mode = null): array
    {
        if ($mode === 'withTrashed') {
            $query = LocationGeoArea::withTrashed();
        } else if ($mode === 'onlyTrashed') {
            $query = LocationGeoArea::onlyTrashed();
        } else {
            $query = LocationGeoArea::query();
        }

        $query->where('id', $location_geo_area_id)
            ->with(array_merge([
                'geoAreaZones',
                'geoAreaZones.country',
                'geoAreaZones.province',
                'geoAreaZones.city',
                'geoAreaZones.district'
            ], $with));

        $location_geo_area = $query->first();

        if (!$location_geo_area) {
            return [
                'ok' => false,
                'message' => trans('location::base.validation.errors'),
                'errors' => [
                    trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.geo_area')])
                ],
                'status' => 404
            ];
        }

        return [
            'ok' => true,
            'message' => trans('location::base.messages.found', ['name' => trans('location::base.model_name.geo_area')]),
            'data' => LocationGeoAreaResource::make($location_geo_area),
            'status' => 200
        ];
    }

    /**
     * Store the specified location geo area.
     *
     * @param array $data
     *
     * @return array
     * @throws Throwable
     */
    public function store(array $data): array
    {
        $validator = Validator::make($data, (new StoreGeoAreaRequest)->rules());
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
            $geo_area = new LocationGeoArea;
            $geo_area->title = $data['title'];
            $geo_area->description = $data['description'];
            $geo_area->status = $data['status'] ?? true;
            $geo_area->save();

            event(new GeoAreaStoreEvent($geo_area, $data));

            if (array_key_exists('geo_area_zones', $data)) {
                $geo_area->geoAreaZones()->createMany($data['geo_area_zones']);
            }

            return [
                'ok' => true,
                'message' => trans('location::base.messages.created', ['name' => trans('location::base.model_name.geo_area')]),
                'data' => LocationGeoAreaResource::make($geo_area),
                'status' => 201
            ];
        });
    }

    /**
     * Update the specified location geo area.
     *
     * @param int $location_geo_area_id
     * @param array $data
     *
     * @return array
     */
    public function update(int $location_geo_area_id, array $data): array
    {
        $validator = Validator::make($data, (new UpdateGeoAreaRequest)->setLocationGeoAreaId($location_geo_area_id)->rules());
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

        return DB::transaction(function () use ($location_geo_area_id, $data) {
            /**
             * @var LocationGeoArea $location_geo_area
             */
            $location_geo_area = LocationGeoArea::query()->where('id', $location_geo_area_id)->first();

            if (!$location_geo_area) {
                return [
                    'ok' => false,
                    'message' => trans('location::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.geo_area')])
                    ],
                    'status' => 404
                ];
            }

            if (array_key_exists('title', $data)) {
                $location_geo_area->title = $data['title'];
            }

            if (array_key_exists('description', $data)) {
                $location_geo_area->description = $data['description'];
            }

            if (array_key_exists('status', $data)) {
                $location_geo_area->status = $data['status'];
            }

            $location_geo_area->save();

            event(new GeoAreaUpdateEvent($location_geo_area, $data));

            if (array_key_exists('geo_area_zones', $data)) {
                $location_geo_area->geoAreaZones()->delete();
                $location_geo_area->geoAreaZones()->createMany($data['geo_area_zones']);
            }

            $location_geo_area->load([
                'geoAreaZones',
                'geoAreaZones.country',
                'geoAreaZones.province',
                'geoAreaZones.city',
                'geoAreaZones.district'
            ]);

            return [
                'ok' => true,
                'message' => trans('location::base.messages.updated', ['name' => trans('location::base.model_name.geo_area')]),
                'data' => LocationCityResource::make($location_geo_area),
                'status' => 200
            ];
        });
    }

    /**
     * Delete the specified location geo area.
     *
     * @param int $location_geo_area_id
     *
     * @return array
     */
    public function delete(int $location_geo_area_id): array
    {
        return DB::transaction(function () use ($location_geo_area_id) {
            /**
             * @var LocationGeoArea $location_geo_area
             */
            $location_geo_area = LocationGeoArea::query()->where('id', $location_geo_area_id)->first();

            if (!$location_geo_area) {
                return [
                    'ok' => false,
                    'message' => trans('location::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.geo_area')])
                    ],
                    'status' => 404
                ];
            }

            event(new GeoAreaDeleteEvent($location_geo_area));

            $data = LocationGeoAreaResource::make($location_geo_area);

            $location_geo_area->delete();

            return [
                'ok' => true,
                'message' => trans('location::base.messages.deleted', ['name' => trans('location::base.model_name.geo_area')]),
                'data' => $data,
                'status' => 200
            ];
        });
    }

    /**
     * Restore the specified location geo area.
     *
     * @param int $location_geo_area_id
     *
     * @return array
     */
    public function restore(int $location_geo_area_id): array
    {
        return DB::transaction(function () use ($location_geo_area_id) {
            /**
             * @var LocationGeoArea $location_geo_area
             */
            $location_geo_area = LocationGeoArea::onlyTrashed()->where('id', $location_geo_area_id)->first();

            if (!$location_geo_area) {
                return [
                    'ok' => false,
                    'message' => trans('location::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.geo_area')])
                    ],
                    'status' => 404
                ];
            }

            event(new GeoAreaRestoreEvent($location_geo_area));

            $data = LocationCityResource::make($location_geo_area);

            $location_geo_area->restore();

            return [
                'ok' => true,
                'data' => $data,
                'message' => trans('location::base.messages.restored', ['name' => trans('location::base.model_name.geo_area')]),
                'status' => 200
            ];
        });
    }

    /**
     * Force delete the specified location geo area.
     *
     * @param int $location_geo_area_id
     *
     * @return array
     */
    public function forceDelete(int $location_geo_area_id): array
    {
        return DB::transaction(function () use ($location_geo_area_id) {
            /**
             * @var LocationGeoArea $location_geo_area
             */
            $location_geo_area = LocationGeoArea::onlyTrashed()->where('id', $location_geo_area_id)->first();

            if (!$location_geo_area) {
                return [
                    'ok' => false,
                    'message' => trans('location::base.validation.errors'),
                    'errors' => [
                        trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.geo_area')])
                    ],
                    'status' => 404
                ];
            }

            event(new GeoAreaForceDeleteEvent($location_geo_area));

            $data = LocationCityResource::make($location_geo_area);

            $location_geo_area->forceDelete();

            return [
                'ok' => true,
                'data' => $data,
                'message' => trans('location::base.messages.permanently_deleted', ['name' => trans('location::base.model_name.geo_area')]),
                'status' => 200
            ];
        });
    }
}
