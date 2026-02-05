<?php

namespace JobMetric\Location\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Location\Models\District;

/**
 * @extends Factory<District>
 */
class DistrictFactory extends Factory
{
    protected $model = District::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'city_id' => null,
            'name'    => $this->faker->streetName(),
            'subtitle' => $this->faker->optional(0.4)->sentence(3),
            'keywords' => $this->faker->optional(0.4)->randomElements([
                'central',
                'downtown',
                'west',
                'east',
                'north',
                'south',
            ], $this->faker->numberBetween(1, 3)),
            'status'  => $this->faker->boolean(90),
        ];
    }

    /**
     * set city_id
     *
     * @param int $city_id
     *
     * @return static
     */
    public function setCity(int $city_id): static
    {
        return $this->state(fn (array $attributes) => [
            'city_id' => $city_id,
        ]);
    }

    /**
     * set name
     *
     * @param string $name
     *
     * @return static
     */
    public function setName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }

    /**
     * set status
     *
     * @param bool $status
     *
     * @return static
     */
    public function setStatus(bool $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
