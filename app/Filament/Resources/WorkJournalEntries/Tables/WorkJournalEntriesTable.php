<?php

namespace App\Filament\Resources\WorkJournalEntries\Tables;

use App\Enums\WorkCategory;
use App\Models\WorkJournalEntry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WorkJournalEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('work_date')
                    ->label('Дата')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('equipment.name')
                    ->label('Оборудование')
                    ->searchable(),
                TextColumn::make('equipment.facility.name')
                    ->label('Объект')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('category')
                    ->label('Категория')
                    ->badge(),
                TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('performed_by')
                    ->label('Исполнитель')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('document_number')
                    ->label('№ документа')
                    ->placeholder('—')
                    ->toggleable(),
                IconColumn::make('work_order_id')
                    ->label('По заявке')
                    ->boolean()
                    ->trueIcon('heroicon-o-clipboard-document-check')
                    ->trueColor('info')
                    ->falseIcon(null),
            ])
            ->defaultSort('work_date', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->label('Категория')
                    ->options(WorkCategory::class),
                SelectFilter::make('equipment_id')
                    ->label('Оборудование')
                    ->relationship('equipment', 'name')
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
