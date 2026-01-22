<?php

namespace JobMetric\Location\Factories;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Location\Models\AddressRelation;

/**
 * @extends Factory<AddressRelation>
 */
class AddressRelationFactory extends Factory
{
    protected $model = AddressRelation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'address_id' => null,
            'addressable_type' => null,
            'addressable_id' => null,
            'collection' => $this->faker->boolean(40) ? $this->faker->randomElement([
                'billing',
                'shipping',
                'delivery',
                'return',
            ]) : null,
            'created_at' => $this->faker->dateTimeBetween('-30 days'),
        ];
    }

    /**
     * set address_id
     *
     * @param int $address_id
     *
     * @return static
     */
    public function setAddress(int $address_id): static
    {
        return $this->state(fn (array $attributes) => [
            'address_id' => $address_id,
        ]);
    }

    /**
     * set addressable
     *
     * @param string $addressable_type
     * @param int $addressable_id
     *
     * @return static
     */
    public function setAddressable(string $addressable_type, int $addressable_id): static
    {
        return $this->state(fn (array $attributes) => [
            'addressable_type' => $addressable_type,
            'addressable_id'   => $addressable_id,
        ]);
    }

    /**
     * set collection
     *
     * @param string|null $collection
     *
     * @return static
     */
    public function setCollection(?string $collection): static
    {
        return $this->state(fn (array $attributes) => [
            'collection' => $collection,
        ]);
    }

    /**
     * set created_at
     *
     * @param DateTimeInterface|string $created_at
     *
     * @return static
     */
    public function setCreatedAt(DateTimeInterface|string $created_at): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $created_at,
        ]);
    }
}
