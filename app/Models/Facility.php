<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'commissioning_date',
        'thermal_power',
        'responsible_person',
        'responsible_phone',
        'notes',
        'scheme',
    ];

    protected function casts(): array
    {
        return [
            'commissioning_date' => 'date',
            'thermal_power' => 'decimal:2',
        ];
    }

    public function getSchemeUrlAttribute(): ?string
    {
        return $this->scheme ? asset('storage/'.$this->scheme) : null;
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    public function serviceTasks(): HasManyThrough
    {
        return $this->hasManyThrough(ServiceTask::class, Equipment::class);
    }

    public function workOrders(): HasManyThrough
    {
        return $this->hasManyThrough(WorkOrder::class, Equipment::class);
    }

    public function workJournalEntries(): HasManyThrough
    {
        return $this->hasManyThrough(WorkJournalEntry::class, Equipment::class);
    }
}
