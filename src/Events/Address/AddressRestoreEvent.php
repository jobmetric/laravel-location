<?php

namespace JobMetric\Location\Events\Address;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Location\Models\Address;

readonly class AddressRestoreEvent implements DomainEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public Address $address
    ) {
    }

    /**
     * Returns the stable technical key for the domain event.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'address.restored';
    }

    /**
     * Returns the full metadata definition for this domain event.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'location::base.model_name.address', 'location::base.events.address_restored.title', 'location::base.events.address_restored.description', 'fas fa-undo', [
            'address',
            'storage',
            'management',
        ]);
    }
}
