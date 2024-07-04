<?php

namespace JobMetric\Location\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Location\Models\LocationProvince;

/**
 * @extends Factory<LocationProvince>
 */
class ProvinceFactory extends Factory
{
    protected $model = LocationProvince::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location_country_id' => null,
            'name' => $this->faker->name,
            'status' => $this->faker->boolean
        ];
    }

    /**
     * set location country id
     *
     * @param int $location_country_id
     *
     * @return static
     */
    public function setLocationCountryId(int $location_country_id): static
    {
        return $this->state(fn(array $attributes) => [
            'location_country_id' => $location_country_id
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
        return $this->state(fn(array $attributes) => [
            'name' => $name
        ]);
    }

    /**
     * set status
     *
     * @param string $status
     *
     * @return static
     */
    public function setStatus(string $status): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => $status
        ]);
    }
}
