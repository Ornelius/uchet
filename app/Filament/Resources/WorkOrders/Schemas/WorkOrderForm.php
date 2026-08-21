<?php

namespace App\Filament\Resources\WorkOrders\Schemas;

use App\Models\Equipment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WorkOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Заявка')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('equipment_id')
                                ->label('Оборудование')
                                ->relationship('equipment', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live(),
                            Select::make('service_task_id')
                                ->label('Плановая задача')
                                ->options(function (Get $get): array {
                                    $equipment = Equipment::find($get('equipment_id'));

                                    return $equipment?->serviceTasks->pluck('name', 'id')->all() ?? [];
                                })
                                ->searchable()
                                ->nullable()
                                ->helperText('Если заявка закрывает периодическую задачу — выберите её'),
                            TextInput::make('title')
                                ->label('Название')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(2),
                            Select::make('type')
                                ->label('Тип')
                                ->options(\App\Enums\OrderType::class)
                                ->default(\App\Enums\OrderType::Planned)
                                ->required(),
                            Select::make('priority')
                                ->label('Приоритет')
                                ->options(\App\Enums\OrderPriority::class)
                                ->default(\App\Enums\OrderPriority::Normal)
                                ->required(),
                            Select::make('status')
                                ->label('Статус')
                                ->options(\App\Enums\OrderStatus::class)
                                ->default(\App\Enums\OrderStatus::Open)
                                ->required(),
                            TextInput::make('assigned_to')
                                ->label('Исполнитель')
                                ->maxLength(255),
                            DatePicker::make('planned_date')
                                ->label('Плановая дата')
                                ->default(now())
                                ->required()
                                ->native(false),
                            DatePicker::make('due_date')
                                ->label('Срок выполнения')
                                ->native(false),
                            Textarea::make('description')
                                ->label('Описание')
                                ->rows(3)
                                ->columnSpan(2),
                        ]),
                    ]),
                Section::make('Фотографии')
                    ->schema([
                        FileUpload::make('photos')
                            ->label('Фотографии')
                            ->multiple()
                            ->disk('public')
                            ->directory('work-orders')
                            ->maxSize(10240)
                            ->helperText('Фотографии до/после выполнения работ. Можно добавить несколько.'),
                    ]),
            ]);
    }
}
