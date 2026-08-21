<?php

namespace App\Filament\Widgets;

use App\Models\Equipment;
use Filament\Widgets\ChartWidget;

class EquipmentTypesChart extends ChartWidget
{
    protected ?string $heading = 'Оборудование по типам';

    protected static ?int $sort = 5;

    protected string $color = 'primary';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $counts = Equipment::query()
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->orderByDesc('total')
            ->get()
            ->pluck('total', 'type');

        return [
            'datasets' => [
                [
                    'label' => 'Оборудование',
                    'data' => $counts->values()->all(),
                ],
            ],
            'labels' => $counts->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
