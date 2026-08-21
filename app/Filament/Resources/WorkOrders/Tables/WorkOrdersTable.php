<?php

namespace App\Filament\Resources\WorkOrders\Tables;

use App\Enums\OrderPriority;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\WorkCategory;
use App\Models\WorkOrder;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class WorkOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Заявка')
                    ->searchable()
                    ->weight('semibold')
                    ->limit(45),
                TextColumn::make('equipment.name')
                    ->label('Оборудование')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->sortable(),
                TextColumn::make('priority')
                    ->label('Приоритет')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('planned_date')
                    ->label('План')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Срок')
                    ->date('d.m.Y')
                    ->placeholder('—')
                    ->color(fn (?string $state, WorkOrder $record): ?string => match (true) {
                        $state === null => null,
                        now()->lt($state) => null,
                        default => in_array($record->status, OrderStatus::active()) ? 'danger' : 'gray',
                    }),
                TextColumn::make('assigned_to')
                    ->label('Исполнитель')
                    ->placeholder('—')
                    ->toggleable(),
                IconColumn::make('is_auto')
                    ->label('Авто')
                    ->boolean()
                    ->trueIcon('heroicon-o-bolt')
                    ->trueColor('warning')
                    ->falseIcon(null),
                ImageColumn::make('photos')
                    ->label('Фото')
                    ->disk('public')
                    ->state(fn (WorkOrder $record): ?string => $record->photos[0] ?? null)
                    ->size(40)
                    ->square()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->defaultSort('planned_date', 'asc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(OrderStatus::class),
                SelectFilter::make('type')
                    ->label('Тип')
                    ->options(OrderType::class),
                SelectFilter::make('priority')
                    ->label('Приоритет')
                    ->options(OrderPriority::class),
                SelectFilter::make('equipment_id')
                    ->label('Оборудование')
                    ->relationship('equipment', 'name')
                    ->searchable(),
                TernaryFilter::make('is_auto')
                    ->label('Создана системой'),
            ])
            ->recordActions([
                Action::make('start')
                    ->label('Начать')
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->visible(fn (WorkOrder $record): bool => $record->status === OrderStatus::Open)
                    ->action(function (WorkOrder $record): void {
                        $record->update(['status' => OrderStatus::InProgress]);

                        Notification::make()->success()->title('Работа начата')->send();
                    }),
                Action::make('complete')
                    ->label('Завершить')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (WorkOrder $record): bool => $record->canComplete())
                    ->modalHeading('Завершение заявки')
                    ->modalDescription('Запись будет добавлена в журнал работ. Если заявка связана с плановой задачей — срок по ней пересчитается.')
                    ->form([
                        DatePicker::make('work_date')
                            ->label('Дата выполнения')
                            ->default(now())
                            ->required()
                            ->native(false),
                        Select::make('category')
                            ->label('Категория работ')
                            ->options(WorkCategory::class)
                            ->default(fn (WorkOrder $record): string => $record->defaultCategory()->value)
                            ->required(),
                        TextInput::make('performed_by')
                            ->label('Исполнитель'),
                        TextInput::make('document_number')
                            ->label('№ наряда / акта'),
                        Textarea::make('description')
                            ->label('Выполненные работы')
                            ->required()
                            ->rows(3),
                        FileUpload::make('photos')
                            ->label('Фотографии')
                            ->multiple()
                            ->disk('public')
                            ->directory('journal')
                            ->maxSize(10240),
                    ])
                    ->action(function (WorkOrder $record, array $data): void {
                        $record->complete(
                            description: $data['description'] ?? null,
                            workDate: Carbon::parse($data['work_date'] ?? now()),
                            performedBy: $data['performed_by'] ?? null,
                            documentNumber: $data['document_number'] ?? null,
                            photos: $data['photos'] ?? null,
                            category: isset($data['category']) ? WorkCategory::from($data['category']) : null,
                        );

                        Notification::make()
                            ->success()
                            ->title('Заявка выполнена')
                            ->body('Запись добавлена в журнал работ.')
                            ->send();
                    }),
                Action::make('cancel')
                    ->label('Отменить')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Отменить заявку?')
                    ->visible(fn (WorkOrder $record): bool => $record->canComplete())
                    ->action(function (WorkOrder $record): void {
                        $record->update(['status' => OrderStatus::Cancelled]);

                        Notification::make()->warning()->title('Заявка отменена')->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
