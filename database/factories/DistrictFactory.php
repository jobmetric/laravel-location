<?php

namespace JobMetric\Location\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Location\Models\LocationDistrict;

/**
 * @extends Factory<LocationDistrict>
 */
class DistrictFactory extends Factory
{
    protected $model = LocationDistrict::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location_country_id' => null,
            'location_province_id' => null,
            'location_city_id' => null,
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
     * set location province id
     *
     * @param int $location_province_id
     *
     * @return static
     */
    public function setLocationProvinceId(int $location_province_id): static
    {
        return $this->state(fn(array $attributes) => [
            'location_province_id' => $location_province_id
        ]);
    }

    /**
     * set location city id
     *
     * @param int $location_city_id
     *
     * @return static
     */
    public function setLocationCityId(int $location_city_id): static
    {
        return $this->state(fn(array $attributes) => [
            'location_city_id' => $location_city_id
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
