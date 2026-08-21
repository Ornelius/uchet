<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EquipmentStatus: string implements HasColor, HasIcon, HasLabel
{
    case InService = 'in_service';
    case UnderRepair = 'under_repair';
    case OutOfService = 'out_of_service';
    case AwaitingReplacement = 'awaiting_replacement';

    public function getLabel(): string
    {
        return match ($this) {
            self::InService => 'В эксплуатации',
            self::UnderRepair => 'В ремонте',
            self::OutOfService => 'Выведено из эксплуатации',
            self::AwaitingReplacement => 'Ожидает замены',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::InService => 'success',
            self::UnderRepair => 'warning',
            self::OutOfService => 'gray',
            self::AwaitingReplacement => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::InService => 'heroicon-o-check-circle',
            self::UnderRepair => 'heroicon-o-wrench-screwdriver',
            self::OutOfService => 'heroicon-o-pause-circle',
            self::AwaitingReplacement => 'heroicon-o-arrow-path',
        };
    }
}
