<?php

namespace App\Filament\Resources\Equipment\RelationManagers;

use App\Enums\IntervalUnit;
use App\Enums\TaskType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use App\Models\Equipment;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServiceTasksRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceTasks';

    protected static bool $isLazy = false;

    protected static ?string $title = 'Задачи обслуживания';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Наименование задачи')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Например: Замена вибровставки'),
                Select::make('type')
                    ->label('Вид работ')
                    ->options(TaskType::class)
                    ->default(TaskType::Maintenance)
                    ->required(),
                TextInput::make('interval_amount')
                    ->label('Интервал')
                    ->numeric()
                    ->minValue(1)
                    ->required(),
                Select::make('interval_unit')
                    ->label('Периодичность')
                    ->options(IntervalUnit::class)
                    ->default(IntervalUnit::Months)
                    ->required(),
                DatePicker::make('last_executed_date')
                    ->label('Дата последнего выполнения')
                    ->native(false)
                    ->helperText('Если не указана — срок считается от даты установки оборудования'),
                Textarea::make('notes')
                    ->label('Примечания')
                    ->rows(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Задача')
                    ->searchable()
                    ->weight('semibold'),
                TextColumn::make('type')
                    ->label('Вид работ')
                    ->badge(),
                TextColumn::make('interval_label')
                    ->label('Периодичность'),
                TextColumn::make('last_executed_date')
                    ->label('Последнее выполнение')
                    ->date('d.m.Y')
                    ->placeholder('—'),
                TextColumn::make('next_due_date')
                    ->label('Следующее')
                    ->date('d.m.Y')
                    ->placeholder('—')
                    ->color(fn (?string $state): ?string => match (true) {
                        $state === null => null,
                        now()->gte($state) => 'danger',
                        now()->addDays(config('maintenance.due_soon_days'))->gte($state) => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('task_status')
                    ->label('Статус')
                    ->badge(),
            ])
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query
                    ->select('service_tasks.*')
                    ->join('equipment', 'equipment.id', '=', 'service_tasks.equipment_id')
                    ->orderByRaw(Equipment::nextDueExpression());
            })
            ->filters([
                SelectFilter::make('type')
                    ->label('Вид работ')
                    ->options(TaskType::class),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Добавить задачу'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
