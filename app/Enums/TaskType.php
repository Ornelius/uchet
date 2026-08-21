<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TaskType: string implements HasColor, HasLabel
{
    case Replacement = 'replacement';
    case Calibration = 'calibration';
    case Maintenance = 'maintenance';

    public function getLabel(): string
    {
        return match ($this) {
            self::Replacement => 'Замена',
            self::Calibration => 'Поверка',
            self::Maintenance => 'Обслуживание',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Replacement => 'danger',
            self::Calibration => 'info',
            self::Maintenance => 'primary',
        };
    }
}
