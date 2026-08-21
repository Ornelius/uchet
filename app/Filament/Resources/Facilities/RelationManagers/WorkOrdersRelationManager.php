<?php

namespace App\Filament\Resources\Facilities\RelationManagers;

use App\Enums\OrderPriority;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WorkOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'workOrders';

    protected static ?string $title = 'Заявки';

    protected static bool $isLazy = false;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('equipment_id')
                    ->label('Оборудование')
                    ->options(fn (): array => $this->getOwnerRecord()->equipment->pluck('name', 'id')->all())
                    ->searchable()
                    ->required(),
                TextInput::make('title')
                    ->label('Название заявки')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->label('Тип')
                    ->options(OrderType::class)
                    ->default(OrderType::Planned)
                    ->required(),
                Select::make('status')
                    ->label('Статус')
                    ->options(OrderStatus::class)
                    ->default(OrderStatus::Open)
                    ->required(),
                Select::make('priority')
                    ->label('Приоритет')
                    ->options(OrderPriority::class)
                    ->default(OrderPriority::Normal)
                    ->required(),
                DatePicker::make('planned_date')
                    ->label('Плановая дата')
                    ->default(now())
                    ->required()
                    ->native(false),
                DatePicker::make('due_date')
                    ->label('Срок выполнения')
                    ->native(false),
                TextInput::make('assigned_to')
                    ->label('Исполнитель')
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Описание')
                    ->rows(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('planned_date')
                    ->label('План')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Заявка')
                    ->searchable()
                    ->weight('semibold'),
                TextColumn::make('equipment.name')
                    ->label('Оборудование'),
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('due_date')
                    ->label('Срок')
                    ->date('d.m.Y')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('planned_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(OrderStatus::class),
                SelectFilter::make('type')
                    ->label('Тип')
                    ->options(OrderType::class),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Новая заявка'),
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
