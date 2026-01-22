<?php

namespace JobMetric\Location\Events\Address;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Location\Models\Address;

readonly class AddressUpdateEvent implements DomainEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public Address $address,
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
        return 'address.updated';
    }

    /**
     * Returns the full metadata definition for this domain event.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'location::base.model_name.address', 'location::base.events.address_updated.title', 'location::base.events.address_updated.description', 'fas fa-edit', [
            'address',
            'storage',
            'management',
        ]);
    }
}
