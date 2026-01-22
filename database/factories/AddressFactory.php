<?php

namespace JobMetric\Location\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Location\Models\Address;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id'  => null,
            'owner_type' => null,
            'owner_id'   => null,
            'address'    => [
                'blvd'   => $this->faker->boolean(60) ? $this->faker->streetName() : null,
                'street' => $this->faker->streetName(),
                'alley'  => $this->faker->boolean(40) ? $this->faker->streetName() : null,
                'number' => $this->faker->numberBetween(1, 999),
                'floor'  => $this->faker->boolean(50) ? $this->faker->numberBetween(1, 10) : null,
                'unit'   => $this->faker->boolean(40) ? $this->faker->numberBetween(1, 50) : null,
            ],
            'postcode'   => $this->faker->boolean(70) ? $this->faker->postcode() : null,
            'lat'        => $this->faker->boolean(60) ? (string) $this->faker->latitude() : null,
            'lng'        => $this->faker->boolean(60) ? (string) $this->faker->longitude() : null,
            'info'       => $this->faker->boolean(50) ? [
                'mobile_prefix' => $this->faker->numberBetween(1, 999),
                'mobile'        => $this->faker->phoneNumber(),
                'name'          => $this->faker->name(),
                'landline'      => $this->faker->boolean(30) ? $this->faker->phoneNumber() : null,
                'notes'         => $this->faker->boolean(20) ? $this->faker->sentence() : null,
            ] : null,
        ];
    }

    /**
     * set parent_id
     *
     * @param int|null $parent_id
     *
     * @return static
     */
    public function setParent(?int $parent_id): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent_id,
        ]);
    }

    /**
     * set owner
     *
     * @param string $owner_type
     * @param int $owner_id
     *
     * @return static
     */
    public function setOwner(string $owner_type, int $owner_id): static
    {
        return $this->state(fn (array $attributes) => [
            'owner_type' => $owner_type,
            'owner_id'   => $owner_id,
        ]);
    }

    /**
     * set address (replace entire address object)
     *
     * @param array|null $address
     *
     * @return static
     */
    public function setAddress(?array $address): static
    {
        return $this->state(fn (array $attributes) => [
            'address' => $address,
        ]);
    }

    /**
     * merge/override a single address field
     *
     * @param string $key
     * @param mixed $value
     *
     * @return static
     */
    public function addAddressField(string $key, mixed $value): static
    {
        return $this->state(function (array $attributes) use ($key, $value) {
            $addr = $attributes['address'] ?? [];
            $addr[$key] = $value;

            return ['address' => $addr];
        });
    }

    /**
     * set postcode
     *
     * @param string|null $postcode
     *
     * @return static
     */
    public function setPostcode(?string $postcode): static
    {
        return $this->state(fn (array $attributes) => [
            'postcode' => $postcode,
        ]);
    }

    /**
     * set coordinates
     *
     * @param string|null $lat
     * @param string|null $lng
     *
     * @return static
     */
    public function setCoordinates(?string $lat, ?string $lng): static
    {
        return $this->state(fn (array $attributes) => [
            'lat' => $lat,
            'lng' => $lng,
        ]);
    }

    /**
     * set info (replace entire info object)
     *
     * @param array|null $info
     *
     * @return static
     */
    public function setInfo(?array $info): static
    {
        return $this->state(fn (array $attributes) => [
            'info' => $info,
        ]);
    }

    /**
     * merge/override a single info field
     *
     * @param string $key
     * @param mixed $value
     *
     * @return static
     */
    public function addInfoField(string $key, mixed $value): static
    {
        return $this->state(function (array $attributes) use ($key, $value) {
            $info = $attributes['info'] ?? [];
            $info[$key] = $value;

            return ['info' => $info];
        });
    }
}
