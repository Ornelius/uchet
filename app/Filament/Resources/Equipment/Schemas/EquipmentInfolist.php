<?php

namespace App\Filament\Resources\Equipment\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EquipmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основные сведения')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('name')->label('Наименование')->weight('bold'),
                            TextEntry::make('serial_number')->label('Инвентарный №'),
                            TextEntry::make('type')->label('Тип'),
                            TextEntry::make('manufacturer')->label('Производитель'),
                            TextEntry::make('model')->label('Модель'),
                            TextEntry::make('power')->label('Мощность, кВт')->placeholder('—'),
                            TextEntry::make('installation_date')->label('Установлено')->date('d.m.Y')->placeholder('—'),
                            TextEntry::make('warranty_until')->label('Гарантия до')->date('d.m.Y')->placeholder('—'),
                            TextEntry::make('status')->label('Статус')->badge(),
                            TextEntry::make('next_service_date')
                                ->label('Ближайшее обслуживание')
                                ->date('d.m.Y')
                                ->placeholder('—')
                                ->color(fn ($state): ?string => $state && now()->gte($state) ? 'danger' : null),
                            TextEntry::make('worst_task_status')->label('Обслуживание')->badge(),
                        ]),
                    ]),
                Section::make('QR-код')
                    ->description('Сканируйте код для перехода к электронному паспорту оборудования')
                    ->schema([
ImageEntry::make('qr_url')
                        ->disk('public')
                        ->label('QR-код')
                            ->square()
                            ->height(180)
                            ->extraAttributes(['style' => 'width: 180px; height: 180px; object-fit: contain;']),
                    ]),
                Section::make('Фотографии')
                    ->schema([
                        ImageEntry::make('photos')
                            ->disk('public')
                            ->label('Фотографии')
                            ->columns(4)
                            ->height(160),
                    ]),
                Section::make('Примечания')
                    ->schema([
                        TextEntry::make('notes')->placeholder('—')->html(false)->columnSpanFull(),
                    ])->columns(3),
                Section::make('Составные элементы')
                    ->description('Детали и узлы, входящие в состав оборудования')
                    ->schema([
                        RepeatableEntry::make('components')
                            ->label('Элементы')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextEntry::make('name')->label('Наименование')->weight('bold'),
                                    TextEntry::make('type')->label('Тип')->placeholder('—'),
                                    TextEntry::make('serial_number')->label('Серийный / инвентарный №')->placeholder('—'),
                                    TextEntry::make('manufacturer')->label('Производитель')->placeholder('—'),
                                    TextEntry::make('model')->label('Модель')->placeholder('—'),
                                    TextEntry::make('status')->label('Статус')->badge(),
                                    TextEntry::make('installation_date')->label('Дата установки')->date('d.m.Y')->placeholder('—'),
                                    TextEntry::make('notes')->label('Примечания')->placeholder('—')->columnSpan(2),
                                    ImageEntry::make('photos')
                                        ->disk('public')
                                        ->label('Фотографии')
                                        ->columns(4)
                                        ->height(100)
                                        ->columnSpan(3)
                                        ->placeholder('Нет фотографий'),
                                ]),
                            ]),
                    ]),
            ]);
    }
}
