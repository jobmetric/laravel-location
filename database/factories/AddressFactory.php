<?php

namespace JobMetric\Location\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Location\Models\LocationAddress;

/**
 * @extends Factory<LocationAddress>
 */
class AddressFactory extends Factory
{
    protected $model = LocationAddress::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'addressable_type' => null,
            'addressable_id' => null,
            'location_country_id' => null,
            'location_province_id' => null,
            'location_city_id' => null,
            'location_district_id' => null,
            'address' => $this->faker->address,
            'pluck' => $this->faker->randomNumber(),
            'unit' => $this->faker->randomNumber(),
            'postcode' => $this->faker->randomNumber(),
            'lat' => $this->faker->latitude,
            'lng' => $this->faker->longitude,
        ];
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
        return $this->state(fn(array $attributes) => [
            'addressable_type' => $addressable_type,
            'addressable_id' => $addressable_id,
        ]);
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
     * set location district id
     *
     * @param int $location_district_id
     *
     * @return static
     */
    public function setLocationDistrictId(int $location_district_id): static
    {
        return $this->state(fn(array $attributes) => [
            'location_district_id' => $location_district_id
        ]);
    }

    /**
     * set address
     *
     * @param string $address
     *
     * @return static
     */
    public function setAddress(string $address): static
    {
        return $this->state(fn(array $attributes) => [
            'address' => $address
        ]);
    }

    /**
     * set pluck
     *
     * @param string $pluck
     *
     * @return static
     */
    public function setPluck(string $pluck): static
    {
        return $this->state(fn(array $attributes) => [
            'pluck' => $pluck
        ]);
    }

    /**
     * set unit
     *
     * @param string $unit
     *
     * @return static
     */
    public function setUnit(string $unit): static
    {
        return $this->state(fn(array $attributes) => [
            'unit' => $unit
        ]);
    }

    /**
     * set postcode
     *
     * @param string $postcode
     *
     * @return static
     */
    public function setPostcode(string $postcode): static
    {
        return $this->state(fn(array $attributes) => [
            'postcode' => $postcode
        ]);
    }

    /**
     * set location
     *
     * @param string $latitude
     * @param string $longitude
     *
     * @return static
     */
    public function setLocation(string $latitude, string $longitude): static
    {
        return $this->state(fn(array $attributes) => [
            'lat' => $latitude,
            'lng' => $longitude
        ]);
    }
}
