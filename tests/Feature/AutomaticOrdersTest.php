<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\ServiceTask;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutomaticOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_automatic_orders_are_created_for_overdue_and_soon_due_tasks(): void
    {
        $equipment = Equipment::factory()->create();

        ServiceTask::factory()->create([
            'equipment_id' => $equipment->id,
            'name' => 'Просроченная задача',
            'interval_amount' => 1,
            'interval_unit' => \App\Enums\IntervalUnit::Years,
            'last_executed_date' => now()->subYears(2),
        ]);

        ServiceTask::factory()->create([
            'equipment_id' => $equipment->id,
            'name' => 'Скоро задача',
            'interval_amount' => 5,
            'interval_unit' => \App\Enums\IntervalUnit::Months,
            'last_executed_date' => now()->subMonths(4)->subDays(20),
        ]);

        ServiceTask::factory()->create([
            'equipment_id' => $equipment->id,
            'name' => 'Далекая задача',
            'interval_amount' => 5,
            'interval_unit' => \App\Enums\IntervalUnit::Years,
            'last_executed_date' => now()->subMonths(1),
        ]);

        $this->artisan('maintenance:generate-orders')->assertSuccessful();

        $orders = WorkOrder::with('serviceTask')->get();

        $this->assertCount(2, $orders);
        $this->assertSame(
            ['Просроченная задача', 'Скоро задача'],
            $orders->map(fn (WorkOrder $order): ?string => $order->serviceTask?->name)
                ->sort()
                ->values()
                ->all(),
        );
        $this->assertTrue($orders->every->is_auto);
        $this->assertTrue($orders->every(fn (WorkOrder $order): bool => $order->type === \App\Enums\OrderType::Planned));
    }

    public function test_automatic_orders_are_not_duplicated_for_the_same_cycle(): void
    {
        $equipment = Equipment::factory()->create();
        $task = ServiceTask::factory()->create([
            'equipment_id' => $equipment->id,
            'name' => 'Единственная задача',
            'interval_amount' => 1,
            'interval_unit' => \App\Enums\IntervalUnit::Years,
            'last_executed_date' => now()->subYears(2),
        ]);

        WorkOrder::factory()->create([
            'equipment_id' => $equipment->id,
            'service_task_id' => $task->id,
            'planned_date' => now()->subDay(),
        ]);

        $this->artisan('maintenance:generate-orders')->assertSuccessful();

        $this->assertSame(1, WorkOrder::where('service_task_id', $task->id)->count());
    }

    public function test_new_cycle_creates_a_new_automatic_order(): void
    {
        $equipment = Equipment::factory()->create();
        $task = ServiceTask::factory()->create([
            'equipment_id' => $equipment->id,
            'name' => 'Задача',
            'interval_amount' => 1,
            'interval_unit' => \App\Enums\IntervalUnit::Years,
            'last_executed_date' => now()->subYears(3),
        ]);

        WorkOrder::factory()->done()->create([
            'equipment_id' => $equipment->id,
            'service_task_id' => $task->id,
            'planned_date' => now()->subYears(2),
        ]);

        $this->artisan('maintenance:generate-orders')->assertSuccessful();

        $this->assertSame(2, WorkOrder::where('service_task_id', $task->id)->count());
        $this->assertTrue(WorkOrder::where('service_task_id', $task->id)->active()->exists());
    }
}
