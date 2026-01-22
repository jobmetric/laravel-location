<?php

namespace JobMetric\Location\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Location\Models\GeoArea;

/**
 * @extends Factory<GeoArea>
 */
class GeoAreaFactory extends Factory
{
    protected $model = GeoArea::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => $this->faker->boolean(90),
        ];
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
