<?php

namespace JobMetric\Location\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Spatie\QueryBuilder\QueryBuilder query(array $filter = [])
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginate(array $filter = [], int $page_limit = 15)
 * @method static \Illuminate\Database\Eloquent\Collection all(array $filter = [])
 * @method static array store(array $data)
 * @method static array update(int $location_city_id, array $data)
 * @method static array delete(int $location_city_id)
 *
 * @see \JobMetric\Location\Services\CityManager
 */
class LocationCity extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \JobMetric\Location\Services\CityManager::class;
    }
}
