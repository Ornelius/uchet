<?php

namespace Tests\Feature;

use App\Enums\IntervalUnit;
use App\Enums\TaskStatus;
use App\Enums\WorkCategory;
use App\Models\Equipment;
use App\Models\ServiceTask;
use App\Models\WorkJournalEntry;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceTest extends TestCase
{
    use RefreshDatabase;

    private Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->equipment = Equipment::factory()->create();
    }

    public function test_next_due_date_is_calculated_from_last_execution_date(): void
    {
        $task = ServiceTask::factory()->create([
            'equipment_id' => $this->equipment->id,
            'interval_amount' => 1,
            'interval_unit' => IntervalUnit::Years,
            'last_executed_date' => '2025-03-15',
        ]);

        $this->assertSame('2026-03-15', $task->next_due_date->toDateString());
    }

    public function test_next_due_date_falls_back_to_installation_date(): void
    {
        $this->equipment->update(['installation_date' => '2024-05-01']);

        $task = ServiceTask::factory()->create([
            'equipment_id' => $this->equipment->id,
            'interval_amount' => 6,
            'interval_unit' => IntervalUnit::Months,
            'last_executed_date' => null,
        ]);

        $this->assertSame('2024-11-01', $task->next_due_date->toDateString());
    }

    public function test_month_intervals_do_not_overflow_end_of_month(): void
    {
        $task = ServiceTask::factory()->create([
            'equipment_id' => $this->equipment->id,
            'interval_amount' => 1,
            'interval_unit' => IntervalUnit::Months,
            'last_executed_date' => '2026-01-31',
        ]);

        $this->assertSame('2026-02-28', $task->next_due_date->toDateString());
    }

    public function test_task_is_marked_overdue_when_due_date_passed(): void
    {
        $task = ServiceTask::factory()->create([
            'equipment_id' => $this->equipment->id,
            'interval_amount' => 1,
            'interval_unit' => IntervalUnit::Years,
            'last_executed_date' => now()->subYears(2),
        ]);

        $this->assertSame(TaskStatus::Overdue, $task->task_status);
        $this->assertLessThan(0, $task->days_until_due);
    }

    public function test_task_is_marked_due_soon_within_configured_window(): void
    {
        $task = ServiceTask::factory()->create([
            'equipment_id' => $this->equipment->id,
            'interval_amount' => 10,
            'interval_unit' => IntervalUnit::Days,
            'last_executed_date' => now()->subDays(5),
        ]);

        $this->assertSame(TaskStatus::DueSoon, $task->task_status);
    }

    public function test_journal_entry_advances_the_service_task(): void
    {
        $task = ServiceTask::factory()->create([
            'equipment_id' => $this->equipment->id,
            'interval_amount' => 1,
            'interval_unit' => IntervalUnit::Years,
            'last_executed_date' => now()->subYears(2),
        ]);

        WorkJournalEntry::factory()->create([
            'equipment_id' => $this->equipment->id,
            'service_task_id' => $task->id,
            'category' => WorkCategory::Repair,
            'work_date' => now(),
        ]);

        $task->refresh();

        $this->assertSame(now()->toDateString(), $task->last_executed_date->toDateString());
        $this->assertSame(now()->addYear()->toDateString(), $task->next_due_date->toDateString());
        $this->assertSame(TaskStatus::Ok, $task->task_status);
    }

    public function test_journal_entry_does_not_move_task_back_in_time(): void
    {
        $task = ServiceTask::factory()->create([
            'equipment_id' => $this->equipment->id,
            'interval_amount' => 1,
            'interval_unit' => IntervalUnit::Years,
            'last_executed_date' => now()->subDay(),
        ]);

        WorkJournalEntry::factory()->create([
            'equipment_id' => $this->equipment->id,
            'service_task_id' => $task->id,
            'work_date' => now()->subMonths(3),
        ]);

        $this->assertSame(now()->subDay()->toDateString(), $task->refresh()->last_executed_date->toDateString());
    }

    public function test_completing_a_work_order_creates_journal_entry_and_advances_task(): void
    {
        $task = ServiceTask::factory()->create([
            'equipment_id' => $this->equipment->id,
            'type' => \App\Enums\TaskType::Replacement,
            'interval_amount' => 1,
            'interval_unit' => IntervalUnit::Years,
            'last_executed_date' => now()->subYears(3),
        ]);

        $order = WorkOrder::factory()->create([
            'equipment_id' => $this->equipment->id,
            'service_task_id' => $task->id,
        ]);

        $entry = $order->complete(
            description: 'Вибровставка заменена',
            performedBy: 'Иванов И.И.',
            documentNumber: 'НР-101',
        );

        $order->refresh();
        $task->refresh();

        $this->assertSame(\App\Enums\OrderStatus::Done, $order->status);
        $this->assertNotNull($order->completed_at);
        $this->assertSame($order->id, $entry->work_order_id);
        $this->assertSame(WorkCategory::Repair, $entry->category);
        $this->assertSame('Вибровставка заменена', $entry->description);
        $this->assertSame(now()->toDateString(), $task->last_executed_date->toDateString());
        $this->assertSame(TaskStatus::Ok, $task->task_status);
    }

    public function test_done_order_cannot_be_completed_again(): void
    {
        $order = WorkOrder::factory()->done()->create([
            'equipment_id' => $this->equipment->id,
        ]);

        $this->assertFalse($order->canComplete());
    }

    public function test_work_order_stores_photos_as_array(): void
    {
        $order = WorkOrder::factory()->create([
            'equipment_id' => $this->equipment->id,
            'photos' => ['work-orders/a.jpg', 'work-orders/b.jpg'],
        ]);

        $this->assertSame(
            ['work-orders/a.jpg', 'work-orders/b.jpg'],
            $order->refresh()->photos,
        );
    }
}
