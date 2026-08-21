<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum IntervalUnit: string implements HasLabel
{
    case Days = 'days';
    case Months = 'months';
    case Years = 'years';

    public function getLabel(): string
    {
        return match ($this) {
            self::Days => 'дн.',
            self::Months => 'мес.',
            self::Years => 'лет',
        };
    }

    public function pluralLabel(int $amount): string
    {
        $n = abs($amount) % 100;
        $n = $n % 10;
        if ($n > 4 && $n < 20) {
            $forms = ['дней', 'месяцев', 'лет'];
        } elseif ($n > 1 && $n < 5) {
            $forms = ['дня', 'месяца', 'года'];
        } else {
            $forms = ['день', 'месяц', 'год'];
        }

        return $forms[match ($this) {
            self::Days => 0,
            self::Months => 1,
            self::Years => 2,
        }];
    }
}
