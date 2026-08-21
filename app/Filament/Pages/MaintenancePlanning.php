<?php

namespace App\Filament\Pages;

use App\Models\Equipment;
use App\Models\ServiceTask;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class MaintenancePlanning extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Планирование ТО';

    protected static ?string $title = 'Планирование технического обслуживания';

    protected string $view = 'filament.pages.maintenance-planning';

    protected static ?int $navigationSort = 5;

    public ?string $month = null;

    protected function getViewData(): array
    {
        return [
            'monthTitle' => $this->month_title,
            'stats' => $this->stats,
            'overdueTasks' => $this->overdue_tasks,
            'days' => $this->days,
            'leadingBlanks' => $this->leading_blanks,
            'month' => $this->month,
        ];
    }

    public function mount(): void
    {
        $this->month ??= now()->format('Y-m');
    }

    public function previousMonth(): void
    {
        $this->month = $this->monthStart()->subMonthNoOverflow()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = $this->monthStart()->addMonthNoOverflow()->format('Y-m');
    }

    public function currentMonth(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function monthStart(): Carbon
    {
        return Carbon::parse($this->month.'-01');
    }

    public function monthEnd(): Carbon
    {
        return $this->monthStart()->endOfMonth();
    }

    public function getMonthTitleProperty(): string
    {
        return $this->monthStart()->translatedFormat('F Y');
    }

    public function getDaysProperty(): array
    {
        $start = $this->monthStart();
        $end = $this->monthEnd();

        $tasks = ServiceTask::query()
            ->with('equipment')
            ->select('service_tasks.*')
            ->join('equipment', 'equipment.id', '=', 'service_tasks.equipment_id')
            ->whereRaw(Equipment::nextDueExpression().' between ? and ?', [$start->toDateString(), $end->toDateString()])
            ->get();

        $orders = WorkOrder::query()
            ->with('equipment')
            ->whereBetween('planned_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('planned_date')
            ->get();

        $byDay = collect(range(1, $end->day))->mapWithKeys(fn (int $day) => [$day => [
            'tasks' => [],
            'orders' => [],
        ]])->all();

        foreach ($tasks as $task) {
            $day = $task->next_due_date?->day;

            if ($day && array_key_exists($day, $byDay)) {
                $byDay[$day]['tasks'][] = $task;
            }
        }

        foreach ($orders as $order) {
            $day = $order->planned_date?->day;

            if ($day && array_key_exists($day, $byDay)) {
                $byDay[$day]['orders'][] = $order;
            }
        }

        return $byDay;
    }

    public function getWeeksProperty(): array
    {
        $start = $this->monthStart();
        $end = $this->monthEnd();

        $leadingBlanks = match ($start->dayOfWeekIso) {
            1 => 0,
            2 => 1,
            3 => 2,
            4 => 3,
            5 => 4,
            6 => 5,
            default => 6,
        };

        $totalCells = $leadingBlanks + $end->day;
        $weeks = ceil($totalCells / 7);

        return range(1, (int) $weeks);
    }

    public function getLeadingBlanksProperty(): int
    {
        return match ($this->monthStart()->dayOfWeekIso) {
            1 => 0,
            2 => 1,
            3 => 2,
            4 => 3,
            5 => 4,
            6 => 5,
            default => 6,
        };
    }

    public function getOverdueTasksProperty(): Collection
    {
        return ServiceTask::query()
            ->with('equipment')
            ->select('service_tasks.*')
            ->join('equipment', 'equipment.id', '=', 'service_tasks.equipment_id')
            ->overdue()
            ->orderByRaw(Equipment::nextDueExpression())
            ->get();
    }

    public function getStatsProperty(): array
    {
        $start = $this->monthStart();
        $end = $this->monthEnd();

        return [
            'tasks' => ServiceTask::query()
                ->join('equipment', 'equipment.id', '=', 'service_tasks.equipment_id')
                ->whereRaw(Equipment::nextDueExpression().' between ? and ?', [$start->toDateString(), $end->toDateString()])
                ->count(),
            'orders' => WorkOrder::whereBetween('planned_date', [$start->toDateString(), $end->toDateString()])->count(),
            'overdue' => ServiceTask::query()->overdue()->count(),
        ];
    }
}
