<?php

namespace App\Filament\Resources\Facilities\Widgets;

use App\Enums\WorkCategory;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class FacilityBreakdownsChart extends ChartWidget
{
    public ?Model $record = null;

    protected ?string $heading = 'Поломки по месяцам (12 мес.)';

    protected static ?int $sort = 3;

    protected string $color = 'danger';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $entries = $this->record?->workJournalEntries()
            ->whereIn('category', [WorkCategory::Breakdown, WorkCategory::Repair])
            ->get() ?? collect();

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
        return 'bar';
    }
}
