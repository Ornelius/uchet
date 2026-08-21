<?php

namespace App\Filament\Widgets;

use App\Enums\WorkCategory;
use App\Models\Equipment;
use App\Models\Facility;
use App\Models\ServiceTask;
use App\Models\WorkOrder;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GlobalStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Общая сводка';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $overdue = ServiceTask::query()->overdue()->count();
        $dueSoon = ServiceTask::query()->dueSoon()->count();
        $openOrders = WorkOrder::active()->count();
        $breakdowns = \App\Models\WorkJournalEntry::where('category', WorkCategory::Breakdown)
            ->where('work_date', '>=', now()->subYear())
            ->count();

        return [
            Stat::make('Объекты', Facility::count())
                ->description('на учёте')
                ->icon('heroicon-o-home-modern'),
            Stat::make('Оборудование', Equipment::count())
                ->description('единиц')
                ->icon('heroicon-o-cpu-chip'),
            Stat::make('Просрочено', $overdue)
                ->description('задач требуют срочного внимания')
                ->color($overdue > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-exclamation-triangle'),
            Stat::make('Скоро', $dueSoon)
                ->description('задач в ближайшие 30 дней')
                ->color($dueSoon > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-bell'),
            Stat::make('Открытые заявки', $openOrders)
                ->description('в работе')
                ->color($openOrders > 0 ? 'info' : 'gray')
                ->icon('heroicon-o-clipboard-document-list'),
            Stat::make('Поломки за год', $breakdowns)
                ->color($breakdowns > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-bolt-slash'),
        ];
    }
}
