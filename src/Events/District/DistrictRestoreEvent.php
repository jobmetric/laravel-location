<?php

namespace JobMetric\Location\Events\District;

use JobMetric\Location\Models\LocationDistrict;

class DistrictRestoreEvent
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
