<?php

namespace JobMetric\Location\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \JobMetric\Location\Models\LocationCountry store(array $data)
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
