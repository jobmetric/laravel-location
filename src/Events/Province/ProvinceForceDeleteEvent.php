<?php

namespace JobMetric\Location\Events\Province;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Location\Models\Province;

readonly class ProvinceForceDeleteEvent implements DomainEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public Province $province
    ) {
    }

    /**
     * Returns the stable technical key for the domain event.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'province.force_deleted';
    }

    /**
     * Returns the full metadata definition for this domain event.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'location::base.model_name.province', 'location::base.events.province_force_deleted.title', 'location::base.events.province_force_deleted.description', 'fas fa-trash-alt', [
            'province',
            'storage',
            'management',
        ]);
    }
}
