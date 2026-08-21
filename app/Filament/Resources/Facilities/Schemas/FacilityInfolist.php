<?php

namespace App\Filament\Resources\Facilities\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FacilityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Электронный паспорт объекта')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('name')
                                ->label('Наименование')
                                ->weight('bold'),
                            TextEntry::make('address')
                                ->label('Адрес')
                                ->placeholder('—')
                                ->columnSpan(2),
                            TextEntry::make('commissioning_date')
                                ->label('Введён в эксплуатацию')
                                ->date('d.m.Y')
                                ->placeholder('—'),
                            TextEntry::make('thermal_power')
                                ->label('Тепловая мощность, Гкал/ч')
                                ->placeholder('—'),
                            TextEntry::make('responsible_person')
                                ->label('Ответственное лицо')
                                ->placeholder('—'),
                            TextEntry::make('responsible_phone')
                                ->label('Телефон')
                                ->placeholder('—'),
                        ]),
                    ]),
                Section::make('Примечания')
                    ->schema([
                        TextEntry::make('notes')->placeholder('—')->columnSpanFull(),
                    ])->columns(3),
            ]);
    }
}
