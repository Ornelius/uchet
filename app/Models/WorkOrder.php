<?php

namespace App\Models;

use App\Enums\OrderPriority;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\TaskType;
use App\Enums\WorkCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_id',
        'service_task_id',
        'title',
        'type',
        'status',
        'priority',
        'planned_date',
        'due_date',
        'assigned_to',
        'description',
        'photos',
        'is_auto',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => OrderType::class,
            'status' => OrderStatus::class,
            'priority' => OrderPriority::class,
            'planned_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'is_auto' => 'boolean',
            'photos' => 'array',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function serviceTask(): BelongsTo
    {
        return $this->belongsTo(ServiceTask::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('work_orders.status', array_column(OrderStatus::active(), 'value'));
    }

    public function canComplete(): bool
    {
        return in_array($this->status, [OrderStatus::Open, OrderStatus::InProgress], true);
    }

    public function complete(
        ?string $description = null,
        ?Carbon $workDate = null,
        ?string $performedBy = null,
        ?string $documentNumber = null,
        ?array $photos = null,
        ?WorkCategory $category = null,
    ): WorkJournalEntry {
        return DB::transaction(function () use ($description, $workDate, $performedBy, $documentNumber, $photos, $category): WorkJournalEntry {
            $entry = WorkJournalEntry::create([
                'equipment_id' => $this->equipment_id,
                'service_task_id' => $this->service_task_id,
                'work_order_id' => $this->id,
                'category' => $category ?? $this->defaultCategory(),
                'work_date' => $workDate ?? now()->startOfDay(),
                'description' => $description ?? $this->description ?? $this->title,
                'performed_by' => $performedBy,
                'document_number' => $documentNumber,
                'photos' => $photos,
            ]);

            $this->forceFill([
                'status' => OrderStatus::Done,
                'completed_at' => now(),
            ])->save();

            return $entry;
        });
    }

    public function defaultCategory(): WorkCategory
    {
        if ($this->serviceTask?->type === TaskType::Calibration) {
            return WorkCategory::Calibration;
        }

        if ($this->serviceTask?->type === TaskType::Replacement) {
            return WorkCategory::Repair;
        }

        return WorkCategory::PlannedService;
    }
}
