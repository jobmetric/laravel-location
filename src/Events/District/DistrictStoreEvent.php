<?php

namespace JobMetric\Location\Events\District;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JobMetric\Location\Models\LocationDistrict;

class DistrictStoreEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly LocationDistrict $locationDistrict,
        public readonly array            $data
    )
    {
    }
}
