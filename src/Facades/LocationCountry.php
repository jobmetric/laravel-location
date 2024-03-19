<?php

namespace JobMetric\Location\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array store(array $data)
 * @method static array update(int $location_country_id, array $data)
 * @method static array delete(int $location_country_id)
 *
 * @see \JobMetric\Location\Services\CountryManager
 */
class LocationCountry extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \JobMetric\Location\Services\CountryManager::class;
    }
}
