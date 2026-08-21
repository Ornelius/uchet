<?php

namespace App\Filament\Resources\Facilities\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class FacilityEquipmentTypesChart extends ChartWidget
{
    public ?Model $record = null;

    protected ?string $heading = 'Состав оборудования по типам';

    protected static ?int $sort = 4;

    protected string $color = 'primary';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $counts = $this->record?->equipment
            ->groupBy('type')
            ->map->count() ?? collect();

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
