<?php

namespace App\Filament\Resources\WorkJournalEntries\Schemas;

use App\Enums\WorkCategory;
use App\Models\Equipment;
use App\Models\WorkOrder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WorkJournalEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Запись в журнале')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('equipment_id')
                                ->label('Оборудование')
                                ->relationship('equipment', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live(),
                            Select::make('category')
                                ->label('Категория')
                                ->options(WorkCategory::class)
                                ->default(WorkCategory::PlannedService)
                                ->required(),
                            Select::make('service_task_id')
                                ->label('Плановая задача')
                                ->options(function (Get $get): array {
                                    $equipment = Equipment::find($get('equipment_id'));

                                    return $equipment?->serviceTasks->pluck('name', 'id')->all() ?? [];
                                })
                                ->searchable()
                                ->nullable()
                                ->helperText('Если работа выполнена по периодической задаче — срок по ней будет пересчитан'),
                            Select::make('work_order_id')
                                ->label('Связанная заявка')
                                ->options(function (Get $get): array {
                                    $equipment = Equipment::find($get('equipment_id'));

                                    return $equipment?->workOrders()
                                        ->active()
                                        ->orderBy('planned_date')
                                        ->get()
                                        ->mapWithKeys(fn (WorkOrder $order): array => [$order->id => $order->planned_date->format('d.m.Y').': '.$order->title])
                                        ->all() ?? [];
                                })
                                ->searchable()
                                ->nullable(),
                            DatePicker::make('work_date')
                                ->label('Дата выполнения')
                                ->default(now())
                                ->required()
                                ->native(false),
                            TextInput::make('performed_by')
                                ->label('Исполнитель')
                                ->maxLength(255),
                            TextInput::make('document_number')
                                ->label('№ наряда / акта')
                                ->maxLength(255),
                            Textarea::make('description')
                                ->label('Описание работ')
                                ->required()
                                ->rows(3)
                                ->columnSpan(2),
                            FileUpload::make('photos')
                                ->label('Фотографии')
                                ->multiple()
                                ->disk('public')
                                ->directory('journal')
                                ->maxSize(10240)
                                ->columnSpan(2),
                        ]),
                    ]),
            ]);
    }
}
