<?php

namespace JobMetric\Location\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Location\Models\Province;

/**
 * @extends Factory<Province>
 */
class ProvinceFactory extends Factory
{
    protected $model = Province::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country_id' => null,
            'name'       => $this->faker->city(),
            'status'     => $this->faker->boolean(90),
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
