<?php

namespace App\Filament\Resources\Equipment\Schemas;

use App\Enums\EquipmentStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EquipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основные сведения')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Наименование')
                                ->required()
                                ->maxLength(255),
                            Select::make('facility_id')
                                ->label('Объект (котельная)')
                                ->relationship('facility', 'name')
                                ->searchable()
                                ->preload()
                                ->nullable(),
                            TextInput::make('type')
                                ->label('Тип')
                                ->required()
                                ->maxLength(255)
                                ->helperText('Котёл, насос, счётчик газа, вибродатчик, датчик, теплообменник, горелка и т.д.'),
                            TextInput::make('serial_number')
                                ->label('Инвентарный / серийный №')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                            TextInput::make('manufacturer')
                                ->label('Производитель')
                                ->maxLength(255),
                            TextInput::make('model')
                                ->label('Модель')
                                ->maxLength(255),
                            TextInput::make('power')
                                ->label('Мощность, кВт')
                                ->numeric()
                                ->minValue(0),
                            Select::make('status')
                                ->label('Статус')
                                ->options(EquipmentStatus::class)
                                ->default(EquipmentStatus::InService)
                                ->required(),
                            DatePicker::make('installation_date')
                                ->label('Дата установки (ввода в эксплуатацию)')
                                ->native(false),
                            DatePicker::make('warranty_until')
                                ->label('Гарантия до')
                                ->native(false),
                        ]),
                    ]),
                Section::make('Фотографии и примечания')
                    ->schema([
                        FileUpload::make('photos')
                            ->label('Фотографии')
                            ->multiple()
                            ->disk('public')
                            ->directory('equipment')
                            ->maxSize(10240)
                            ->columnSpan(2),
                        Textarea::make('notes')
                            ->label('Примечания')
                            ->rows(3)
                            ->columnSpan(2),
                    ]),
                Section::make('Составные элементы')
                    ->description('Детали и узлы, входящие в состав оборудования: горелка — контроллер, двигатель, электроды; насос — муфта, подшипники и т.д.')
                    ->schema([
                        Repeater::make('components')
                            ->label('Элементы')
                            ->relationship()
                            ->collapsible()
                            ->defaultItems(0)
                            ->addActionLabel('Добавить элемент')
                            ->grid(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Наименование элемента')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                TextInput::make('type')
                                    ->label('Тип')
                                    ->maxLength(255),
                                TextInput::make('serial_number')
                                    ->label('Серийный / инвентарный №')
                                    ->maxLength(255),
                                TextInput::make('manufacturer')
                                    ->label('Производитель')
                                    ->maxLength(255),
                                TextInput::make('model')
                                    ->label('Модель')
                                    ->maxLength(255),
                                DatePicker::make('installation_date')
                                    ->label('Дата установки')
                                    ->native(false),
                                Select::make('status')
                                    ->label('Статус')
                                    ->options(EquipmentStatus::class)
                                    ->default(EquipmentStatus::InService),
                                FileUpload::make('photos')
                                    ->label('Фотографии')
                                    ->multiple()
                                    ->disk('public')
                                    ->directory('equipment')
                                    ->maxSize(10240)
                                    ->columnSpanFull(),
                                Textarea::make('notes')
                                    ->label('Примечания')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
