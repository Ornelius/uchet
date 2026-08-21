<?php

namespace Database\Factories;

use App\Enums\EquipmentStatus;
use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Equipment>
 */
class EquipmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'facility_id' => Facility::factory(),
            'name' => fake()->words(3, true),
            'type' => fake()->randomElement(['boiler', 'pump', 'gas_meter', 'sensor', 'heat_exchanger', 'valve', 'burner']),
            'serial_number' => 'SN-'.fake()->unique()->numerify('####-####'),
            'manufacturer' => fake()->company(),
            'model' => fake()->bothify('??-###'),
            'power' => fake()->optional(0.5)->randomFloat(2, 0.1, 100),
            'installation_date' => fake()->dateTimeBetween('-15 years', '-6 months')->format('Y-m-d'),
            'warranty_until' => fake()->boolean(30)
                ? fake()->dateTimeBetween('now', '+3 years')->format('Y-m-d')
                : null,
            'status' => EquipmentStatus::InService,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function underRepair(): static
    {
        return $this->state(['status' => EquipmentStatus::UnderRepair]);
    }
}
