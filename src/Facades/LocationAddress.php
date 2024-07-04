<?php

namespace JobMetric\Location\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array store(\Illuminate\Database\Eloquent\Model $model, array $data)
 * @method static array update(int $location_address_id, array $data)
 *
 * @see \JobMetric\Location\Services\AddressManager
 */
class LocationAddress extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \JobMetric\Location\Services\AddressManager::class;
    }
}
