<?php

namespace App\Filament\Widgets;

use App\Models\Equipment;
use App\Models\ServiceTask;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class AttentionTable extends TableWidget
{
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Требуют внимания')
            ->columns([
                TextColumn::make('name')
                    ->label('Задача')
                    ->weight('semibold'),
                TextColumn::make('equipment.name')
                    ->label('Оборудование'),
                TextColumn::make('equipment.facility.name')
                    ->label('Объект')
                    ->placeholder('—'),
                TextColumn::make('next_due_date')
                    ->label('Срок')
                    ->date('d.m.Y')
                    ->color(fn (?string $state): ?string => $state && now()->gte($state) ? 'danger' : 'warning'),
                TextColumn::make('days_until_due')
                    ->label('Осталось')
                    ->badge()
                    ->color(fn (?int $state): string => $state !== null && $state < 0 ? 'danger' : 'warning')
                    ->formatStateUsing(fn (?int $state): string => match (true) {
                        $state === null => '—',
                        $state < 0 => 'просрочено на '.abs($state).' дн.',
                        $state === 0 => 'сегодня',
                        default => $state.' дн.',
                    }),
                TextColumn::make('task_status')
                    ->label('Статус')
                    ->badge(),
            ])
            ->paginated(false);
    }

    protected function getTableQuery(): Builder
    {
        return ServiceTask::query()
            ->join('equipment', 'equipment.id', '=', 'service_tasks.equipment_id')
            ->with('equipment.facility')
            ->whereRaw(Equipment::nextDueExpression().' <= ?', [
                now()->addDays(config('maintenance.due_soon_days'))->toDateString(),
            ])
            ->orderByRaw(Equipment::nextDueExpression());
    }

    public static function canView(): bool
    {
        return ServiceTask::query()
            ->join('equipment', 'equipment.id', '=', 'service_tasks.equipment_id')
            ->whereRaw(Equipment::nextDueExpression().' <= ?', [
                now()->addDays(config('maintenance.due_soon_days'))->toDateString(),
            ])
            ->exists();
    }
}
