<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Facility>
 */
class FacilityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'address' => fake()->address(),
            'commissioning_date' => fake()->dateTimeBetween('-20 years', '-1 year')->format('Y-m-d'),
            'thermal_power' => fake()->randomFloat(2, 0.5, 20),
            'responsible_person' => fake()->name(),
            'responsible_phone' => fake()->phoneNumber(),
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
