<?php

namespace JobMetric\Location\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Location\Models\City;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    protected $model = City::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'province_id' => null,
            'name'        => $this->faker->city(),
            'status'      => $this->faker->boolean(90),
        ];
    }

    /**
     * set province_id
     *
     * @param int $province_id
     *
     * @return static
     */
    public function setProvince(int $province_id): static
    {
        return $this->state(fn (array $attributes) => [
            'province_id' => $province_id,
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
