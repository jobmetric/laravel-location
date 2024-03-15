<?php

namespace JobMetric\Location\Events\Country;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JobMetric\Location\Models\LocationCountry;

class CountryUpdateEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly LocationCountry $country,
        public readonly array           $data
    )
    {
    }
}
