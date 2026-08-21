<?php

namespace App\Filament\Resources\Facilities\Widgets;

use App\Enums\WorkCategory;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class FacilityStatsOverview extends StatsOverviewWidget
{
    public ?Model $record = null;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $facility = $this->record;

        $overdue = $facility?->serviceTasks()->overdue()->count() ?? 0;
        $openOrders = $facility?->workOrders()->active()->count() ?? 0;
        $breakdowns = $facility?->workJournalEntries()
            ->where('category', WorkCategory::Breakdown)
            ->where('work_date', '>=', now()->subYear())
            ->count() ?? 0;

        return [
            Stat::make('Оборудование', $facility?->equipment->count() ?? 0)
                ->description('единиц на учёте'),
            Stat::make('Просрочено задач', $overdue)
                ->description('требуют внимания')
                ->color($overdue > 0 ? 'danger' : 'success'),
            Stat::make('Открытые заявки', $openOrders)
                ->description('плановые и аварийные')
                ->color($openOrders > 0 ? 'warning' : 'success'),
            Stat::make('Поломки за год', $breakdowns)
                ->color($breakdowns > 0 ? 'danger' : 'success'),
        ];
    }
}
