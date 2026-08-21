<?php

namespace Database\Factories;

use App\Enums\OrderPriority;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\WorkOrder>
 */
class WorkOrderFactory extends Factory
{
    public function definition(): array
    {
        $planned = fake()->dateTimeBetween('-30 days', '+30 days');

        return [
            'equipment_id' => Equipment::factory(),
            'service_task_id' => null,
            'title' => fake()->sentence(4),
            'type' => OrderType::Planned,
            'status' => OrderStatus::Open,
            'priority' => OrderPriority::Normal,
            'planned_date' => $planned->format('Y-m-d'),
            'due_date' => (clone $planned)->modify('+7 days')->format('Y-m-d'),
            'assigned_to' => fake()->optional()->name(),
            'description' => fake()->optional()->text(120),
            'is_auto' => false,
        ];
    }

    public function auto(): static
    {
        return $this->state(['is_auto' => true]);
    }

    public function done(): static
    {
        return $this->state([
            'status' => OrderStatus::Done,
            'completed_at' => fake()->dateTimeBetween('-10 days', 'now'),
        ]);
    }
}
