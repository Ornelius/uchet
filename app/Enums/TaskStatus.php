<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TaskStatus: string implements HasColor, HasIcon, HasLabel
{
    case Overdue = 'overdue';
    case DueSoon = 'due_soon';
    case Ok = 'ok';
    case NoReference = 'no_reference';

    public function getLabel(): string
    {
        return match ($this) {
            self::Overdue => 'Просрочено',
            self::DueSoon => 'Скоро',
            self::Ok => 'В норме',
            self::NoReference => '—',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Overdue => 'danger',
            self::DueSoon => 'warning',
            self::Ok => 'success',
            self::NoReference => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Overdue => 'heroicon-o-exclamation-triangle',
            self::DueSoon => 'heroicon-o-bell',
            self::Ok => 'heroicon-o-check-circle',
            self::NoReference => 'heroicon-o-minus-circle',
        };
    }
}
