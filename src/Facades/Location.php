<?php

namespace JobMetric\Location\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \JobMetric\Location\Services\Location
 *
 * @method static \Spatie\QueryBuilder\QueryBuilder query(array $filters = [], array $with = [], ?string $mode = null)
 * @method static \JobMetric\PackageCore\Output\Response paginate(int $pageLimit = 15, array $filters = [], array $with = [], ?string $mode = null)
 * @method static \JobMetric\PackageCore\Output\Response all(array $filters = [], array $with = [], ?string $mode = null)
 * @method static \JobMetric\PackageCore\Output\Response show(int $id, array $with = [], ?string $mode = null)
 * @method static \JobMetric\PackageCore\Output\Response store(array $data, array $with = [])
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
