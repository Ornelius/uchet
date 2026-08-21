<?php

namespace App\Models;

use App\Enums\WorkCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkJournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_id',
        'service_task_id',
        'work_order_id',
        'category',
        'work_date',
        'description',
        'performed_by',
        'document_number',
        'photos',
    ];

    protected function casts(): array
    {
        return [
            'category' => WorkCategory::class,
            'work_date' => 'date',
            'photos' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (WorkJournalEntry $entry): void {
            $entry->advanceServiceTask();
        });
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function serviceTask(): BelongsTo
    {
        return $this->belongsTo(ServiceTask::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    protected function advanceServiceTask(): void
    {
        if ($this->service_task_id === null) {
            return;
        }

        $task = $this->serviceTask;

        if ($task !== null) {
            $task->markExecuted($this->work_date);
        }
    }
}
