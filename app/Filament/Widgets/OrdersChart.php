<?php

namespace App\Filament\Widgets;

use App\Models\WorkOrder;
use Filament\Widgets\ChartWidget;

class OrdersChart extends ChartWidget
{
    protected ?string $heading = 'Заявки по месяцам (12 мес.)';

    protected static ?int $sort = 3;

    protected string $color = 'info';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $orders = WorkOrder::where('planned_date', '>=', now()->subMonths(11)->startOfMonth())->get();

        $months = collect(range(11, 0))
            ->map(fn (int $i): \Illuminate\Support\Carbon => now()->startOfMonth()->subMonthsNoOverflow($i));

        return [
            'datasets' => [
                [
                    'label' => 'Заявки',
                    'data' => $months
                        ->map(fn ($month) => $orders->filter(
                            fn ($order) => $order->planned_date->betweenIncluded($month, $month->copy()->endOfMonth()),
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
