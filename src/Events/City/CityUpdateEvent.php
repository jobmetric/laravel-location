<?php

namespace JobMetric\Location\Events\City;

use JobMetric\Location\Models\LocationCity;

class CityUpdateEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly LocationCity $locationCity,
        public readonly array        $data
    )
    {
    }
}
