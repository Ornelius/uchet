<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum WorkCategory: string implements HasColor, HasLabel
{
    case PlannedService = 'planned_service';
    case Repair = 'repair';
    case Breakdown = 'breakdown';
    case Calibration = 'calibration';
    case Inspection = 'inspection';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::PlannedService => 'Плановое ТО',
            self::Repair => 'Ремонт',
            self::Breakdown => 'Поломка / авария',
            self::Calibration => 'Поверка',
            self::Inspection => 'Осмотр',
            self::Other => 'Другое',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PlannedService => 'info',
            self::Repair => 'warning',
            self::Breakdown => 'danger',
            self::Calibration => 'primary',
            self::Inspection => 'success',
            self::Other => 'gray',
        };
    }

    public static function repairCategories(): array
    {
        return [self::Repair, self::Breakdown];
    }
}
