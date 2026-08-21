<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderType: string implements HasColor, HasLabel
{
    case Planned = 'planned';
    case Emergency = 'emergency';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Planned => 'Плановая',
            self::Emergency => 'Аварийная',
            self::Other => 'Другая',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Planned => 'info',
            self::Emergency => 'danger',
            self::Other => 'gray',
        };
    }
}
