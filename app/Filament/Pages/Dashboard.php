<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AttentionTable;
use App\Filament\Widgets\BreakdownsChart;
use App\Filament\Widgets\EquipmentTypesChart;
use App\Filament\Widgets\GlobalStatsOverview;
use App\Filament\Widgets\OrdersChart;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            GlobalStatsOverview::class,
            AttentionTable::class,
            OrdersChart::class,
            BreakdownsChart::class,
            EquipmentTypesChart::class,
        ];
    }
}
