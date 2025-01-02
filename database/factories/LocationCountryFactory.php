<?php

namespace JobMetric\Location\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Location\Models\LocationCountry;

/**
 * @extends Factory<LocationCountry>
 */
class LocationCountryFactory extends Factory
{
    protected $model = LocationCountry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->country,
            'flag' => $this->faker->countryCode,
            'mobile_prefix' => null,
            'validation' => [],
            'status' => $this->faker->boolean
        ];
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
     * set flag
     *
     * @param string $flag
     *
     * @return static
     */
    public function setFlag(string $flag): static
    {
        return $this->state(fn(array $attributes) => [
            'flag' => $flag
        ]);
    }

    /**
     * set mobile prefix
     *
     * @param string $mobile_prefix
     *
     * @return static
     */
    public function setMobilePrefix(string $mobile_prefix): static
    {
        return $this->state(fn(array $attributes) => [
            'mobile_prefix' => $mobile_prefix
        ]);
    }

    /**
     * set validation
     *
     * @param string $validation
     *
     * @return static
     */
    public function setValidation(string $validation): static
    {
        return $this->state(fn(array $attributes) => [
            'validation' => $validation
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
