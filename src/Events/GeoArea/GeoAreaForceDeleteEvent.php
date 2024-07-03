<?php

namespace JobMetric\Location\Events\GeoArea;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JobMetric\Location\Models\LocationGeoArea;

class GeoAreaForceDeleteEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly LocationGeoArea $locationGeoArea,
    )
    {
    }
}
