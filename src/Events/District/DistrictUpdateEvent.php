<?php

namespace JobMetric\Location\Events\District;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Location\Models\District;

readonly class DistrictUpdateEvent implements DomainEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public District $district,
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
        return 'district.updated';
    }

    /**
     * Returns the full metadata definition for this domain event.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'location::base.model_name.district', 'location::base.events.district_updated.title', 'location::base.events.district_updated.description', 'fas fa-edit', [
            'district',
            'storage',
            'management',
        ]);
    }
}
