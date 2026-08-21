<?php

namespace App\Models;

use App\Enums\EquipmentStatus;
use App\Enums\TaskStatus;
use App\Services\QrCodeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipment';

    protected $fillable = [
        'facility_id',
        'name',
        'type',
        'serial_number',
        'manufacturer',
        'model',
        'power',
        'installation_date',
        'warranty_until',
        'status',
        'qr_code',
        'photos',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'installation_date' => 'date',
            'warranty_until' => 'date',
            'power' => 'decimal:2',
            'photos' => 'array',
            'status' => EquipmentStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Equipment $equipment): void {
            $equipment->generateQrCode();
        });
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function serviceTasks(): HasMany
    {
        return $this->hasMany(ServiceTask::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function workJournalEntries(): HasMany
    {
        return $this->hasMany(WorkJournalEntry::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(EquipmentComponent::class)->orderBy('name');
    }

    public static function nextDueExpression(): string
    {
        return "coalesce(service_tasks.last_executed_date, equipment.installation_date, service_tasks.created_at::date)
            + case service_tasks.interval_unit
                when 'days' then make_interval(days => service_tasks.interval_amount)
                when 'months' then make_interval(months => service_tasks.interval_amount)
                when 'years' then make_interval(years => service_tasks.interval_amount)
                else interval '0'
              end";
    }

    public function scopeOverdue(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->whereHas('serviceTasks', function (Builder $q) use ($today): void {
            $q->whereRaw(self::nextDueExpression().' < ?', [$today]);
        });
    }

    public function scopeDueSoon(Builder $query): Builder
    {
        $today = now()->toDateString();
        $limit = now()->addDays((int) config('maintenance.due_soon_days'))->toDateString();

        return $query->whereHas('serviceTasks', function (Builder $q) use ($today, $limit): void {
            $q->whereRaw(self::nextDueExpression().' >= ?', [$today])
                ->whereRaw(self::nextDueExpression().' <= ?', [$limit]);
        });
    }

    public function generateQrCode(): string
    {
        $path = app(QrCodeService::class)->generate($this);

        $this->forceFill(['qr_code' => $path])->saveQuietly();

        return $path;
    }

    public function getQrUrlAttribute(): ?string
    {
        return $this->qr_code ? asset('storage/'.$this->qr_code) : null;
    }

    public function getQrPngPathAttribute(): ?string
    {
        if (! $this->qr_code) {
            return null;
        }

        return storage_path('app/public/'.str_replace('.svg', '.png', $this->qr_code));
    }

    public function getNextServiceDateAttribute(): ?Carbon
    {
        return $this->serviceTasks
            ->map(fn (ServiceTask $task): ?Carbon => $task->next_due_date)
            ->filter()
            ->min();
    }

    public function getWorstTaskStatusAttribute(): TaskStatus
    {
        $statuses = $this->serviceTasks->map(fn (ServiceTask $task): TaskStatus => $task->task_status);

        if ($statuses->isEmpty()) {
            return TaskStatus::NoReference;
        }

        if ($statuses->contains(TaskStatus::Overdue)) {
            return TaskStatus::Overdue;
        }

        if ($statuses->contains(TaskStatus::DueSoon)) {
            return TaskStatus::DueSoon;
        }

        return TaskStatus::Ok;
    }
}
