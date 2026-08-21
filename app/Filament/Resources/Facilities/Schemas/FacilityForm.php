<?php

namespace App\Filament\Resources\Facilities\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FacilityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Сведения об объекте')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Наименование')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Например: Котельная №1'),
                            TextInput::make('address')
                                ->label('Адрес')
                                ->maxLength(255),
                            DatePicker::make('commissioning_date')
                                ->label('Дата ввода в эксплуатацию')
                                ->native(false),
                            TextInput::make('thermal_power')
                                ->label('Тепловая мощность, Гкал/ч')
                                ->numeric()
                                ->minValue(0),
                            TextInput::make('responsible_person')
                                ->label('Ответственное лицо')
                                ->maxLength(255),
                            TextInput::make('responsible_phone')
                                ->label('Телефон ответственного')
                                ->tel()
                                ->maxLength(255),
                            Textarea::make('notes')
                                ->label('Примечания')
                                ->rows(3)
                                ->columnSpan(2),
                        ]),
                    ]),
                Section::make('Схема котельной')
                    ->description('Общая схема в формате JPG. Открывается в интерактивном режиме: масштабирование колёсиком мыши, перемещение перетаскиванием.')
                    ->schema([
                        FileUpload::make('scheme')
                            ->label('Схема (JPG)')
                            ->image()
                            ->disk('public')
                            ->directory('facilities/schemes')
                            ->maxSize(20480)
                            ->helperText('Один файл, до 20 МБ.'),
                    ]),
            ]);
    }
}
