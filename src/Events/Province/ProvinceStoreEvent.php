<?php

namespace JobMetric\Location\Events\Province;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JobMetric\Location\Models\LocationProvince;

class ProvinceStoreEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

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
