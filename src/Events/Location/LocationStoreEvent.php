<?php

namespace JobMetric\Location\Events\Location;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Location\Models\Location;

/**
 * Class LocationStoreEvent
 *
 * Event fired when a new Location is created.
 *
 * @package JobMetric\Location\Events\Location
 */
readonly class LocationStoreEvent implements DomainEvent
{
    /**
     * Create a new event instance.
     *
     * @param Location $location
     * @param array<string,mixed> $data
     */
    public function __construct(
        public Location $location,
        public array $data = []
    ) {
    }

    /**
     * Get the event key.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'location.stored';
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
            'location::base.events.location_stored.title',
            'location::base.events.location_stored.description',
            'fas fa-save',
            [
                'location',
                'storage',
                'management',
            ]
        );
    }
}
