<?php

namespace JobMetric\Location\Events\City;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Location\Models\City;

readonly class CityRestoreEvent implements DomainEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public City $city
    ) {
    }

    /**
     * Returns the stable technical key for the domain event.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'city.restored';
    }

    /**
     * Returns the full metadata definition for this domain event.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'location::base.model_name.city', 'location::base.events.city_restored.title', 'location::base.events.city_restored.description', 'fas fa-undo', [
            'city',
            'storage',
            'management',
        ]);
    }
}
