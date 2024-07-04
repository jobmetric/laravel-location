<?php

namespace JobMetric\Location\Events\Address;

use JobMetric\Location\Models\LocationAddress;

class AddressUpdateEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly LocationAddress $locationAddress,
        public readonly array           $data
    )
    {
    }
}
