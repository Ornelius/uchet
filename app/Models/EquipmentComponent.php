<?php

namespace App\Models;

use App\Enums\EquipmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentComponent extends Model
{
    use HasFactory;

    protected $table = 'equipment_components';

    protected $fillable = [
        'equipment_id',
        'name',
        'type',
        'serial_number',
        'manufacturer',
        'model',
        'installation_date',
        'status',
        'photos',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'installation_date' => 'date',
            'status' => EquipmentStatus::class,
            'photos' => 'array',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}