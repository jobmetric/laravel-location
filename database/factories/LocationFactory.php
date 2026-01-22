<?php

namespace JobMetric\Location\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Location\Models\Location;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country_id'  => null,
            'province_id' => null,
            'city_id'     => null,
            'district_id' => null,
        ];
    }

    /**
     * set country_id
     *
     * @param int $country_id
     *
     * @return static
     */
    public function setCountry(int $country_id): static
    {
        return $this->state(fn (array $attributes) => [
            'country_id' => $country_id,
        ]);
    }

    /**
     * set province_id
     *
     * @param int|null $province_id
     *
     * @return static
     */
    public function setProvince(?int $province_id): static
    {
        return $this->state(fn (array $attributes) => [
            'province_id' => $province_id,
        ]);
    }

    /**
     * set city_id
     *
     * @param int|null $city_id
     *
     * @return static
     */
    public function setCity(?int $city_id): static
    {
        return $this->state(fn (array $attributes) => [
            'city_id' => $city_id,
        ]);
    }

    /**
     * set district_id
     *
     * @param int|null $district_id
     *
     * @return static
     */
    public function setDistrict(?int $district_id): static
    {
        return $this->state(fn (array $attributes) => [
            'district_id' => $district_id,
        ]);
    }

    /**
     * set full location (country, province, city, district)
     *
     * @param int $country_id
     * @param int|null $province_id
     * @param int|null $city_id
     * @param int|null $district_id
     *
     * @return static
     */
    public function setLocation(
        int $country_id,
        ?int $province_id = null,
        ?int $city_id = null,
        ?int $district_id = null
    ): static {
        return $this->state(fn (array $attributes) => [
            'country_id'  => $country_id,
            'province_id' => $province_id,
            'city_id'     => $city_id,
            'district_id' => $district_id,
        ]);
    }
}
