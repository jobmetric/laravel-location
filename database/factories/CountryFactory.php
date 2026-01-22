<?php

namespace JobMetric\Location\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Location\Models\Country;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    protected $model = Country::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'              => $this->faker->country(),
            'flag'              => $this->faker->boolean(80) ? strtolower($this->faker->countryCode()) . '.svg' : null,
            'mobile_prefix'     => $this->faker->boolean(70) ? $this->faker->numberBetween(1, 999) : null,
            'validation'        => $this->faker->boolean() ? ['/^\d{10}$/', '/^\d{9}$/'] : null,
            'address_on_letter' => $this->faker->boolean(60) ? "{country}, {province}, {city}\n{district}, {blvd}, {street}, {alley}\n No: {number} Floor: {floor} Unit: {unit}\nPostcode: {postcode}\nReceiver: {receiver_name}\nPhone: {receiver_number}" : null,
            'status'            => $this->faker->boolean(90),
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
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }

    /**
     * set flag
     *
     * @param string|null $flag
     *
     * @return static
     */
    public function setFlag(?string $flag): static
    {
        return $this->state(fn (array $attributes) => [
            'flag' => $flag,
        ]);
    }

    /**
     * set mobile prefix
     *
     * @param int|null $mobile_prefix
     *
     * @return static
     */
    public function setMobilePrefix(?int $mobile_prefix): static
    {
        return $this->state(fn (array $attributes) => [
            'mobile_prefix' => $mobile_prefix,
        ]);
    }

    /**
     * set validation
     *
     * @param array|null $validation
     *
     * @return static
     */
    public function setValidation(?array $validation): static
    {
        return $this->state(fn (array $attributes) => [
            'validation' => $validation,
        ]);
    }

    /**
     * set address_on_letter
     *
     * @param string|null $address_on_letter
     *
     * @return static
     */
    public function setAddressOnLetter(?string $address_on_letter): static
    {
        return $this->state(fn (array $attributes) => [
            'address_on_letter' => $address_on_letter,
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
