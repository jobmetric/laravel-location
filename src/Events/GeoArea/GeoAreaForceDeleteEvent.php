<?php

namespace JobMetric\Location\Events\GeoArea;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Location\Models\GeoArea;

readonly class GeoAreaForceDeleteEvent implements DomainEvent
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
        return 'geo_area.force_deleted';
    }

    /**
     * Returns the full metadata definition for this domain event.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'location::base.model_name.geo_area', 'location::base.events.geo_area_force_deleted.title', 'location::base.events.geo_area_force_deleted.description', 'fas fa-trash-alt', [
            'geo_area',
            'storage',
            'management',
        ]);
    }
}
