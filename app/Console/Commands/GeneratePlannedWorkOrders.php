<?php

namespace App\Console\Commands;

use App\Enums\OrderPriority;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\ServiceTask;
use App\Models\WorkOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GeneratePlannedWorkOrders extends Command
{
    protected $signature = 'maintenance:generate-orders
                            {--dry-run : Только показать, что будет создано, не сохраняя}';

    protected $description = 'Автоматически создавать заявки на плановое обслуживание по задачам с заданной периодичностью';

    public function handle(): int
    {
        $leadDays = (int) config('maintenance.work_order_lead_days');
        $durationDays = (int) config('maintenance.work_order_duration_days');

        $horizon = now()->addDays($leadDays);
        $created = 0;
        $skipped = 0;

        $tasks = ServiceTask::query()
            ->with(['equipment', 'workOrders'])
            ->get()
            ->filter(function (ServiceTask $task) use ($horizon): bool {
                $due = $task->next_due_date;

                return $due !== null && $due->lte($horizon);
            });

        foreach ($tasks as $task) {
            $due = $task->next_due_date;
            $reference = $task->reference_date;

            $hasOpenForCycle = $task->workOrders
                ->filter(fn (WorkOrder $order): bool => in_array($order->status, OrderStatus::active(), true))
                ->contains(function (WorkOrder $order) use ($reference): bool {
                    return $reference === null
                        || $order->planned_date->gte($reference->copy()->startOfDay());
                });

            if ($hasOpenForCycle) {
                $skipped++;

                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("Создам: {$task->name} — {$task->equipment->name} (срок {$due->format('d.m.Y')})");

                continue;
            }

            WorkOrder::create([
                'equipment_id' => $task->equipment_id,
                'service_task_id' => $task->id,
                'title' => 'Плановое обслуживание: '.$task->name,
                'type' => OrderType::Planned,
                'status' => OrderStatus::Open,
                'priority' => $due->isPast() ? OrderPriority::High : OrderPriority::Normal,
                'planned_date' => $due->copy()->startOfDay(),
                'due_date' => $due->copy()->addDays($durationDays)->startOfDay(),
                'description' => sprintf(
                    'Создано автоматически. Периодичность: %s. Предыдущее выполнение: %s.',
                    $task->interval_label,
                    $reference?->format('d.m.Y') ?? '—',
                ),
                'is_auto' => true,
            ]);

            $created++;
        }

        $this->info("Готово. Создано заявок: {$created}, пропущено (уже есть открытые): {$skipped}.");

        return self::SUCCESS;
    }
}
