<?php

namespace JobMetric\Location\Events\District;

use JobMetric\Location\Models\LocationDistrict;

class DistrictForceDeleteEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly LocationDistrict $locationDistrict,
    )
    {
    }
}
