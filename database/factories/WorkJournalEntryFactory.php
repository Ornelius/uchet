<?php

namespace Database\Factories;

use App\Enums\WorkCategory;
use App\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\WorkJournalEntry>
 */
class WorkJournalEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'equipment_id' => Equipment::factory(),
            'service_task_id' => null,
            'work_order_id' => null,
            'category' => fake()->randomElement(WorkCategory::cases()),
            'work_date' => fake()->dateTimeBetween('-12 months', 'now')->format('Y-m-d'),
            'description' => fake()->sentence(8),
            'performed_by' => fake()->name(),
            'document_number' => fake()->optional()->bothify('НР-####'),
        ];
    }

    public function breakdown(): static
    {
        return $this->state(['category' => WorkCategory::Breakdown]);
    }

    public function repair(): static
    {
        return $this->state(['category' => WorkCategory::Repair]);
    }
}
