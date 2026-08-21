<?php

namespace App\Filament\Resources\Equipment\Widgets;

use App\Enums\WorkCategory;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class EquipmentStatsOverview extends StatsOverviewWidget
{
    public ?Model $record = null;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $equipment = $this->record;

        $openOrders = $equipment?->workOrders()->active()->count() ?? 0;
        $breakdowns = $equipment?->workJournalEntries()
            ->where('category', WorkCategory::Breakdown)
            ->where('work_date', '>=', now()->subYear())
            ->count() ?? 0;

        return [
            Stat::make('Задач обслуживания', $equipment?->serviceTasks->count() ?? 0)
                ->description('периодических'),
            Stat::make('Ближайшее обслуживание', $equipment?->next_service_date?->format('d.m.Y') ?? '—')
                ->description($equipment?->worst_task_status->getLabel() ?? null)
                ->color($equipment?->worst_task_status->getColor() ?? 'gray'),
            Stat::make('Открытых заявок', $openOrders)
                ->color($openOrders > 0 ? 'warning' : 'success'),
            Stat::make('Поломки за год', $breakdowns)
                ->color($breakdowns > 0 ? 'danger' : 'success'),
        ];
    }
}
