<?php

namespace JobMetric\Location\Events\GeoArea;

use JobMetric\Location\Models\LocationGeoArea;

class GeoAreaStoreEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly LocationGeoArea $locationGeoArea,
        public readonly array           $data
    )
    {
    }
}
