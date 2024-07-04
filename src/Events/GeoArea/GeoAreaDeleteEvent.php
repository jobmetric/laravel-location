<?php

namespace JobMetric\Location\Events\GeoArea;

use JobMetric\Location\Models\LocationGeoArea;

class GeoAreaDeleteEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly LocationGeoArea $locationGeoArea,
    )
    {
    }
}
