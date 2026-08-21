<?php

namespace App\Filament\Resources\Equipment\Tables;

use App\Enums\EquipmentStatus;
use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\Equipment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class EquipmentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('qr_url')
                    ->label('QR')
                    ->size(36)
                    ->square()
                    ->extraAttributes(['style' => 'object-fit: contain;']),
                TextColumn::make('name')
                    ->label('Наименование')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->url(fn (Equipment $record): string => EquipmentResource::getUrl('view', ['record' => $record])),
                TextColumn::make('serial_number')
                    ->label('Инвентарный №')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Тип')
                    ->toggleable(),
                TextColumn::make('facility.name')
                    ->label('Объект')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->sortable(),
                TextColumn::make('components')
                    ->label('')
                    ->state(fn (Equipment $record): string => (string) $record->id)
                    ->formatStateUsing(function (Equipment $record): HtmlString {
                        $url = e(EquipmentResource::getUrl('view', ['record' => $record]));

                        $rows = $record->components->map(function ($component) use ($url): string {
                            $meta = collect([$component->serial_number, $component->manufacturer, $component->model, $component->type])
                                ->filter()
                                ->map(fn ($value): string => e((string) $value))
                                ->implode(' · ');

                            return '<tr class="fi-ta-row fi-equipment-detail-row bg-gray-50 dark:bg-gray-900/40">'
                                . '<td colspan="12" class="fi-ta-cell">'
                                . '<div class="flex items-center gap-2 py-1 pl-10 pr-3">'
                                . '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="fi-icon fi-size-xs fi-text-gray-400 dark:fi-text-gray-500"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5 15 12l-6.75 7.5"/></svg>'
                                . '<a href="' . $url . '" class="fi-text-sm fi-font-medium fi-text-primary hover:fi-underline">' . e((string) $component->name) . '</a>'
                                . ($meta !== '' ? '<span class="fi-text-xs fi-text-gray-400 dark:fi-text-gray-500">(' . $meta . ')</span>' : '')
                                . '</div>'
                                . '</td>'
                                . '</tr>';
                        })->implode('');

                        $list = $rows !== ''
                            ? $rows
                            : '<tr class="fi-ta-row fi-equipment-detail-row bg-gray-50 dark:bg-gray-900/40">'
                                . '<td colspan="12" class="fi-ta-cell">'
                                . '<p class="fi-text-sm fi-text-gray-400 dark:fi-text-gray-500 py-1 pl-10 pr-3">Нет составных элементов</p>'
                                . '</td>'
                                . '</tr>';

                        $htmlTemplate = <<<'HTML'
<div x-data="{ open: false }">
    <button
        type="button"
        class="fi-btn fi-size-xs fi-color-gray"
        aria-label="Составные элементы"
        x-bind:aria-expanded="open"
        @click.prevent.stop="open = !open; $nextTick(() => {
            const tr = $el.closest('tr');
            if (!tr) return;
            if (open) {
                if (!tr.nextElementSibling || !tr.nextElementSibling.classList.contains('fi-equipment-detail-row')) {
                    tr.after($refs.detailRows.content.cloneNode(true));
                }
            } else {
                let next = tr.nextElementSibling;
                while (next && next.classList.contains('fi-equipment-detail-row')) {
                    const toRemove = next;
                    next = next.nextElementSibling;
                    toRemove.remove();
                }
            }
        })">
        <svg x-bind:class="open ? 'rotate-90' : ''" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="fi-icon fi-size-sm fi-transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5 15 12l-6.75 7.5"/></svg>
    </button>
    <template x-ref="detailRows">
        {{ROWS}}
    </template>
</div>
HTML;

                        return new HtmlString(str_replace('{{ROWS}}', $list, $htmlTemplate));
                    })
                    ->searchable(fn (Builder $query, string $search): Builder => $query->whereHas('components', fn (Builder $q): Builder => $q->where('name', 'like', "%{$search}%"))),
                TextColumn::make('next_service_date')
                    ->label('Ближайшее обслуживание')
                    ->date('d.m.Y')
                    ->placeholder('—')
                    ->color(fn (?string $state): ?string => match (true) {
                        $state === null => null,
                        now()->gte($state) => 'danger',
                        now()->addDays(config('maintenance.due_soon_days'))->gte($state) => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('worst_task_status')
                    ->label('Сроки')
                    ->badge()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('attention')
                    ->label('Внимание')
                    ->options([
                        'overdue' => 'Просрочено',
                        'due_soon' => 'Скоро (30 дней)',
                        'ok' => 'В норме',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! filled($data['value'] ?? null)) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'overdue' => $query->overdue(),
                            'due_soon' => $query->dueSoon(),
                            'ok' => $query->whereDoesntHave('serviceTasks', fn (Builder $t): Builder => $t->whereRaw(
                                Equipment::nextDueExpression().' <= ?',
                                [now()->addDays(config('maintenance.due_soon_days'))->toDateString()],
                            )),
                            default => $query,
                        };
                    }),
                SelectFilter::make('facility_id')
                    ->label('Объект')
                    ->relationship('facility', 'name'),
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(EquipmentStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('components'))
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
