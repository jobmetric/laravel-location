<?php

namespace JobMetric\Location\Events\GeoArea;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Location\Models\GeoArea;

readonly class GeoAreaUpdateEvent implements DomainEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public GeoArea $geoArea,
        public array $data
    ) {
    }

    /**
     * Returns the stable technical key for the domain event.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'geo_area.updated';
    }

    /**
     * Returns the full metadata definition for this domain event.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'location::base.model_name.geo_area', 'location::base.events.geo_area_updated.title', 'location::base.events.geo_area_updated.description', 'fas fa-edit', [
            'geo_area',
            'storage',
            'management',
        ]);
    }
}
