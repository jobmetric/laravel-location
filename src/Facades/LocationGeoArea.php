<?php

namespace JobMetric\Location\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Spatie\QueryBuilder\QueryBuilder query(array $filter = [], array $with = [], string $mode = null)
 * @method static \Illuminate\Http\Resources\Json\AnonymousResourceCollection paginate(array $filter = [], int $page_limit = 15, array $with = [], string $mode = null)
 * @method static \Illuminate\Http\Resources\Json\AnonymousResourceCollection all(array $filter = [], array $with = [], string $mode = null)
 * @method static array get(int $location_geo_area_id, array $with = [], string $mode = null)
 * @method static array store(array $data)
 * @method static array update(int $location_geo_area_id, array $data)
 * @method static array delete(int $location_geo_area_id)
 * @method static array restore(int $location_geo_area_id)
 * @method static array forceDelete(int $location_geo_area_id)
 *
 * @see \JobMetric\Location\Services\GeoAreaManager
 */
class LocationGeoArea extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \JobMetric\Location\Services\GeoAreaManager::class;
    }
}
