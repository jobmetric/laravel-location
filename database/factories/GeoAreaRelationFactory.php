<?php

namespace JobMetric\Location\Factories;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Location\Models\GeoAreaRelation;

/**
 * @extends Factory<GeoAreaRelation>
 */
class GeoAreaRelationFactory extends Factory
{
    protected $model = GeoAreaRelation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'geo_area_id'       => null,
            'geographical_type' => null,
            'geographical_id'   => null,
            'created_at'        => $this->faker->dateTimeBetween('-30 days'),
        ];
    }

    /**
     * set geo_area_id
     *
     * @param int $geo_area_id
     *
     * @return static
     */
    public function setGeoArea(int $geo_area_id): static
    {
        return $this->state(fn (array $attributes) => [
            'geo_area_id' => $geo_area_id,
        ]);
    }

    /**
     * set geographical
     *
     * @param string $geographical_type
     * @param int $geographical_id
     *
     * @return static
     */
    public function setGeographical(string $geographical_type, int $geographical_id): static
    {
        return $this->state(fn (array $attributes) => [
            'geographical_type' => $geographical_type,
            'geographical_id'   => $geographical_id,
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
