<?php

namespace App\Filament\Resources\Equipment\RelationManagers;

use App\Enums\WorkCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WorkJournalEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'workJournalEntries';

    protected static bool $isLazy = false;

    protected static ?string $title = 'Журнал работ';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('description')
                    ->label('Описание работ')
                    ->required()
                    ->columnSpanFull(),
                Select::make('category')
                    ->label('Категория')
                    ->options(WorkCategory::class)
                    ->default(WorkCategory::PlannedService)
                    ->required(),
                DatePicker::make('work_date')
                    ->label('Дата выполнения')
                    ->default(now())
                    ->required()
                    ->native(false),
                Select::make('service_task_id')
                    ->label('Связанная задача')
                    ->options(fn (): array => $this->getOwnerRecord()->serviceTasks->pluck('name', 'id')->all())
                    ->searchable()
                    ->nullable()
                    ->helperText('Если работа выполнена по плановой задаче — срок по ней будет пересчитан'),
                TextInput::make('performed_by')
                    ->label('Исполнитель')
                    ->maxLength(255),
                TextInput::make('document_number')
                    ->label('№ наряда / документа')
                    ->maxLength(255),
                FileUpload::make('photos')
                    ->label('Фотографии')
                    ->multiple()
                    ->disk('public')
                    ->directory('journal')
                    ->maxSize(10240)
                    ->columnSpanFull(),
            ]);
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
            ])
            ->defaultSort('work_date', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->label('Категория')
                    ->options(WorkCategory::class),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Записать работу'),
            ])
            ->recordActions([
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
