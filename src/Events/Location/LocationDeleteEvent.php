<?php

namespace JobMetric\Location\Events\Location;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Location\Models\Location;

/**
 * Class LocationDeleteEvent
 *
 * Event fired when a Location is deleted.
 *
 * @package JobMetric\Location\Events\Location
 */
readonly class LocationDeleteEvent implements DomainEvent
{
    /**
     * Create a new event instance.
     *
     * @param Location $location
     */
    public function __construct(
        public Location $location
    ) {
    }

    /**
     * Get the event key.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'location.deleted';
    }

    /**
     * Get the event definition.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(
            self::key(),
            'location::base.model_name.location',
            'location::base.events.location_deleted.title',
            'location::base.events.location_deleted.description',
            'fas fa-trash',
            [
                'location',
                'delete',
                'management',
            ]
        );
    }
}
