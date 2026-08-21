<?php

namespace App\Filament\Widgets;

use App\Enums\WorkCategory;
use App\Models\WorkJournalEntry;
use Filament\Widgets\ChartWidget;

class BreakdownsChart extends ChartWidget
{
    protected ?string $heading = 'Поломки и ремонты по месяцам (12 мес.)';

    protected static ?int $sort = 4;

    protected string $color = 'danger';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $entries = WorkJournalEntry::query()
            ->whereIn('category', [WorkCategory::Breakdown, WorkCategory::Repair])
            ->where('work_date', '>=', now()->subMonths(11)->startOfMonth())
            ->get();

        $breakdowns = $entries->where('category', WorkCategory::Breakdown);
        $repairs = $entries->where('category', WorkCategory::Repair);

        $months = collect(range(11, 0))
            ->map(fn (int $i): \Illuminate\Support\Carbon => now()->startOfMonth()->subMonthsNoOverflow($i));

        return [
            'datasets' => [
                [
                    'label' => 'Поломки',
                    'data' => $months
                        ->map(fn ($month) => $breakdowns->filter(
                            fn ($entry) => $entry->work_date->betweenIncluded($month, $month->copy()->endOfMonth()),
                        )->count())
                        ->all(),
                ],
                [
                    'label' => 'Ремонты',
                    'data' => $months
                        ->map(fn ($month) => $repairs->filter(
                            fn ($entry) => $entry->work_date->betweenIncluded($month, $month->copy()->endOfMonth()),
                        )->count())
                        ->all(),
                ],
            ],
            'labels' => $months->map(fn ($month) => $month->translatedFormat('M'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
