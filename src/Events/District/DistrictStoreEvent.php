<?php

namespace JobMetric\Location\Events\District;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Location\Models\District;

readonly class DistrictStoreEvent implements DomainEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public District $district
    ) {
    }

    /**
     * Returns the stable technical key for the domain event.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'district.stored';
    }

    /**
     * Returns the full metadata definition for this domain event.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'location::base.model_name.district', 'location::base.events.district_stored.title', 'location::base.events.district_stored.description', 'fas fa-save', [
            'district',
            'storage',
            'management',
        ]);
    }
}
