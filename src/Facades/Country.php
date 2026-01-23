<?php

namespace JobMetric\Location\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \JobMetric\Location\Services\Country
 *
 * @method static \JobMetric\PackageCore\Output\Response store(array $data)
 * @method static \JobMetric\PackageCore\Output\Response show(int $id, array $with = [])
 * @method static \JobMetric\PackageCore\Output\Response update(int $id, array $data, array $with = [])
 * @method static \JobMetric\PackageCore\Output\Response destroy(int $id, array $with = [])
 * @method static \JobMetric\PackageCore\Output\Response restore(int $id, array $with = [])
 * @method static \JobMetric\PackageCore\Output\Response forceDelete(int $id, array $with = [])
 * @method static \JobMetric\PackageCore\Output\Response toggleStatus(int $id, array $with = [])
 */
class Country extends Facade
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
        return 'location-country';
    }
}
