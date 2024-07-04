<?php

namespace JobMetric\Location\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Location\Models\LocationGeoAreaZone;

/**
 * @extends Factory<LocationGeoAreaZone>
 */
class GeoAreaZoneFactory extends Factory
{
    protected $model = LocationGeoAreaZone::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'geo_area_id' => null,
            'location_country_id' => null,
            'location_province_id' => null,
            'location_city_id' => null,
            'location_district_id' => null
        ];
    }

    /**
     * set geo area id
     *
     * @param int $geo_area_id
     *
     * @return static
     */
    public function setGeoAreaId(int $geo_area_id): static
    {
        return $this->state(fn(array $attributes) => [
            'geo_area_id' => $geo_area_id
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
}
