<?php

namespace JobMetric\Location\Events\GeoArea;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Location\Models\GeoArea;

readonly class GeoAreaStoreEvent implements DomainEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public GeoArea $geoArea
    ) {
    }

    /**
     * Returns the stable technical key for the domain event.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'geo_area.stored';
    }

    /**
     * Returns the full metadata definition for this domain event.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'location::base.model_name.geo_area', 'location::base.events.geo_area_stored.title', 'location::base.events.geo_area_stored.description', 'fas fa-save', [
            'geo_area',
            'storage',
            'management',
        ]);
    }
}
