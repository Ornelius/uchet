<?php

namespace Database\Factories;

use App\Enums\IntervalUnit;
use App\Enums\TaskType;
use App\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ServiceTask>
 */
class ServiceTaskFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(TaskType::cases());
        $unit = fake()->randomElement(IntervalUnit::cases());

        return [
            'equipment_id' => Equipment::factory(),
            'name' => fake()->randomElement([
                'Замена узла', 'Поверка счётчика', 'Техническое обслуживание',
                'Диагностика', 'Замена датчика', 'Регулировка', 'Осмотр и чистка',
            ]),
            'type' => $type,
            'interval_amount' => match ($unit) {
                IntervalUnit::Days => fake()->numberBetween(7, 90),
                IntervalUnit::Months => fake()->numberBetween(1, 12),
                IntervalUnit::Years => fake()->numberBetween(1, 5),
            },
            'interval_unit' => $unit,
            'last_executed_date' => fake()->boolean(70)
                ? fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d')
                : null,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function overdue(): static
    {
        return $this->state(['last_executed_date' => null]);
    }
}
