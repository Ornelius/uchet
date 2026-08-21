<?php

namespace App\Models;

use App\Enums\IntervalUnit;
use App\Enums\TaskStatus;
use App\Enums\TaskType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class ServiceTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_id',
        'name',
        'type',
        'interval_amount',
        'interval_unit',
        'last_executed_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => TaskType::class,
            'interval_unit' => IntervalUnit::class,
            'interval_amount' => 'integer',
            'last_executed_date' => 'date',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(WorkJournalEntry::class);
    }

    public function getReferenceDateAttribute(): ?Carbon
    {
        return $this->last_executed_date
            ?? $this->equipment?->installation_date
            ?? $this->created_at?->toDate();
    }

    public function getNextDueDateAttribute(): ?Carbon
    {
        $base = $this->reference_date;

        if ($base === null) {
            return null;
        }

        $base = $base->copy()->startOfDay();

        return match ($this->interval_unit) {
            IntervalUnit::Days => $base->addDays($this->interval_amount),
            IntervalUnit::Months => $base->addMonthsNoOverflow($this->interval_amount),
            IntervalUnit::Years => $base->addYearsNoOverflow($this->interval_amount),
        };
    }

    public function getDaysUntilDueAttribute(): ?int
    {
        $due = $this->next_due_date;

        if ($due === null) {
            return null;
        }

        return now()->startOfDay()->diffInDays($due->copy()->startOfDay(), false);
    }

    public function getTaskStatusAttribute(): TaskStatus
    {
        $days = $this->days_until_due;

        if ($days === null) {
            return TaskStatus::NoReference;
        }

        if ($days < 0) {
            return TaskStatus::Overdue;
        }

        if ($days <= (int) config('maintenance.due_soon_days')) {
            return TaskStatus::DueSoon;
        }

        return TaskStatus::Ok;
    }

    public function getIntervalLabelAttribute(): string
    {
        return $this->interval_amount.' '.$this->interval_unit->pluralLabel($this->interval_amount);
    }

    public function markExecuted(?Carbon $date = null): void
    {
        $date = $date?->copy()->startOfDay() ?? now()->startOfDay();

        $current = $this->last_executed_date?->copy()->startOfDay();

        if ($current === null || $date->gt($current)) {
            $this->forceFill(['last_executed_date' => $date])->save();
        }
    }

    public function scopeOverdue(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->whereHas('equipment', function (Builder $q) use ($today): void {
            $q->whereRaw(Equipment::nextDueExpression().' < ?', [$today]);
        });
    }

    public function scopeDueSoon(Builder $query): Builder
    {
        $today = now()->toDateString();
        $limit = now()->addDays((int) config('maintenance.due_soon_days'))->toDateString();

        return $query->whereHas('equipment', function (Builder $q) use ($today, $limit): void {
            $q->whereRaw(Equipment::nextDueExpression().' >= ?', [$today])
                ->whereRaw(Equipment::nextDueExpression().' <= ?', [$limit]);
        });
    }
}
