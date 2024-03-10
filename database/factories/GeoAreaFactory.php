<?php

namespace JobMetric\BanIp\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Location\Models\LocationGeoArea;

/**
 * @extends Factory<LocationGeoArea>
 */
class GeoAreaFactory extends Factory
{
    protected $model = LocationGeoArea::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->name,
            'description' => $this->faker->text(100),
            'status' => $this->faker->boolean
        ];
    }

    /**
     * set title
     *
     * @param string $title
     *
     * @return static
     */
    public function setTitle(string $title): static
    {
        return $this->state(fn(array $attributes) => [
            'title' => $title
        ]);
    }

    /**
     * set description
     *
     * @param string $description
     *
     * @return static
     */
    public function setDescription(string $description): static
    {
        return $this->state(fn(array $attributes) => [
            'description' => $description
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
