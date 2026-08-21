<?php

namespace App\Filament\Resources\Facilities\RelationManagers;

use App\Enums\EquipmentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EquipmentRelationManager extends RelationManager
{
    protected static string $relationship = 'equipment';

    protected static ?string $title = 'Состав оборудования';

    protected static bool $isLazy = false;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Наименование')
                    ->required()
                    ->maxLength(255),
                TextInput::make('type')
                    ->label('Тип')
                    ->required()
                    ->helperText('Котёл, насос, счётчик газа, вибродатчик, датчик, теплообменник, горелка и т.д.'),
                TextInput::make('serial_number')
                    ->label('Инвентарный №')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('manufacturer')
                    ->label('Производитель'),
                TextInput::make('model')
                    ->label('Модель'),
                DatePicker::make('installation_date')
                    ->label('Дата установки')
                    ->native(false),
                Select::make('status')
                    ->label('Статус')
                    ->options(EquipmentStatus::class)
                    ->default(EquipmentStatus::InService)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('qr_url')
                    ->label('QR')
                    ->size(32)
                    ->square()
                    ->extraAttributes(['style' => 'object-fit: contain;']),
                TextColumn::make('name')
                    ->label('Наименование')
                    ->searchable()
                    ->weight('semibold'),
                TextColumn::make('serial_number')
                    ->label('Инвентарный №')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Тип'),
                TextColumn::make('installation_date')
                    ->label('Установлено')
                    ->date('d.m.Y')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('worst_task_status')
                    ->label('Сроки')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(EquipmentStatus::class),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Добавить оборудование'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record): string => \App\Filament\Resources\Equipment\EquipmentResource::getUrl('view', ['record' => $record])),
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
