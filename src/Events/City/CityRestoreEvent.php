<?php

namespace JobMetric\Location\Events\City;

use JobMetric\Location\Models\LocationCity;

class CityRestoreEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly LocationCity $locationCity,
    )
    {
    }
}
