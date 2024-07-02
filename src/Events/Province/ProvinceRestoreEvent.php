<?php

namespace JobMetric\Location\Events\Province;

use JobMetric\Location\Models\LocationProvince;

class ProvinceRestoreEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly LocationProvince $locationProvince,
    )
    {
    }
}
