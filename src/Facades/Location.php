<?php

namespace JobMetric\Location\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \JobMetric\Location\Services\Location
 *
 * @method static \JobMetric\PackageCore\Output\Response store(array $data)
 * @method static \JobMetric\PackageCore\Output\Response show(int $id, array $with = [])
 */
class Location extends Facade
{
    /**
     * Get the registered name of the component in the service container.
     *
     * This accessor must match the binding defined in the package service provider.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'location-location';
    }
}
