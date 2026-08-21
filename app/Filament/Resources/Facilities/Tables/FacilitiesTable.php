<?php

namespace App\Filament\Resources\Facilities\Tables;

use App\Filament\Resources\Facilities\FacilityResource;
use App\Models\Facility;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FacilitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Наименование')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->url(fn (Facility $record): string => FacilityResource::getUrl('view', ['record' => $record])),
                TextColumn::make('address')
                    ->label('Адрес')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('equipment')
                    ->label('Оборудование')
                    ->state(fn (Facility $record): int => $record->equipment()->count())
                    ->badge()
                    ->color('gray'),
                TextColumn::make('open_orders')
                    ->label('Открытые заявки')
                    ->state(fn (Facility $record): int => $record->workOrders()->active()->count())
                    ->badge()
                    ->color(fn (?int $state): ?string => $state > 0 ? 'warning' : 'success'),
                TextColumn::make('overdue_tasks')
                    ->label('Просрочено')
                    ->state(fn (Facility $record): int => $record->serviceTasks()->overdue()->count())
                    ->badge()
                    ->color(fn (?int $state): ?string => $state > 0 ? 'danger' : 'success'),
                TextColumn::make('responsible_person')
                    ->label('Ответственный')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
