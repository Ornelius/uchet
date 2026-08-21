<?php

namespace App\Filament\Resources\Facilities\RelationManagers;

use App\Enums\WorkCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WorkJournalEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'workJournalEntries';

    protected static ?string $title = 'История работ';

    protected static bool $isLazy = false;

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                TextColumn::make('work_date')
                    ->label('Дата')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('equipment.name')
                    ->label('Оборудование'),
                TextColumn::make('category')
                    ->label('Категория')
                    ->badge(),
                TextColumn::make('description')
                    ->label('Описание')
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('performed_by')
                    ->label('Исполнитель')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('document_number')
                    ->label('№ документа')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('work_date', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->label('Категория')
                    ->options(WorkCategory::class),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
