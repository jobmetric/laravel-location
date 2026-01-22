<?php

namespace JobMetric\Location\Factories;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Location\Models\LocationRelation;

/**
 * @extends Factory<LocationRelation>
 */
class LocationRelationFactory extends Factory
{
    protected $model = LocationRelation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location_id'       => null,
            'locationable_type' => null,
            'locationable_id'   => null,
            'created_at'        => $this->faker->dateTimeBetween('-30 days'),
        ];
    }

    /**
     * set location_id
     *
     * @param int $location_id
     *
     * @return static
     */
    public function setLocation(int $location_id): static
    {
        return $this->state(fn (array $attributes) => [
            'location_id' => $location_id,
        ]);
    }

    /**
     * set locationable
     *
     * @param string $locationable_type
     * @param int $locationable_id
     *
     * @return static
     */
    public function setLocationable(string $locationable_type, int $locationable_id): static
    {
        return $this->state(fn (array $attributes) => [
            'locationable_type' => $locationable_type,
            'locationable_id'   => $locationable_id,
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
