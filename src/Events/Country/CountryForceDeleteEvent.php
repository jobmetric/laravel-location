<?php

namespace JobMetric\Location\Events\Country;

use JobMetric\Location\Models\LocationCountry;

class CountryForceDeleteEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly LocationCountry $locationCountry,
    )
    {
    }
}
