<?php

namespace App\Filament\Resources\Facilities\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class FacilityWorkOrdersChart extends ChartWidget
{
    public ?Model $record = null;

    protected ?string $heading = 'Заявки по месяцам (12 мес.)';

    protected static ?int $sort = 2;

    protected string $color = 'info';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $orders = $this->record?->workOrders()->get() ?? collect();

        $months = collect(range(11, 0))
            ->map(fn (int $i): \Illuminate\Support\Carbon => now()->startOfMonth()->subMonthsNoOverflow($i));

        return [
            'datasets' => [
                [
                    'label' => 'Заявки',
                    'data' => $months
                        ->map(fn ($month) => $orders->filter(
                            fn ($order) => $order->planned_date
                                && $order->planned_date->betweenIncluded($month, $month->copy()->endOfMonth()),
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
