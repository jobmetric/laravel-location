<?php

namespace JobMetric\Location\Events\Province;

use JobMetric\Location\Models\LocationProvince;

class ProvinceStoreEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly LocationProvince $locationProvince,
        public readonly array            $data
    )
    {
    }
}
