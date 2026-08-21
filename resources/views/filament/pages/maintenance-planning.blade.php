<x-filament-panels::page>
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <x-filament::button size="sm" icon="heroicon-m-chevron-left" wire:click="previousMonth" />
            <h2 style="font-size: 1.25rem; font-weight: 700; min-width: 220px; text-align: center; text-transform: capitalize;">
                {{ $monthTitle }}
            </h2>
            <x-filament::button size="sm" icon="heroicon-m-chevron-right" wire:click="nextMonth" />
            <x-filament::link size="sm" wire:click="currentMonth">Текущий месяц</x-filament::link>
        </div>
        <div class="text-sm text-gray-500">
            Задач в месяце: <b>{{ $stats['tasks'] }}</b>
            &nbsp;·&nbsp; Заявок: <b>{{ $stats['orders'] }}</b>
            &nbsp;·&nbsp; Просрочено: <b class="text-danger-600">{{ $stats['overdue'] }}</b>
        </div>
    </div>

    @if ($overdueTasks->isNotEmpty())
        <x-filament::section icon="heroicon-o-exclamation-triangle" icon-color="danger" class="mb-4">
            <x-slot name="heading">Просроченные задачи</x-slot>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 6px;">
                @foreach ($overdueTasks as $task)
                    <li>
                        <a
                            href="{{ \App\Filament\Resources\Equipment\EquipmentResource::getUrl('view', ['record' => $task->equipment_id]) }}"
                            style="color: var(--fi-color-danger-600); font-weight: 500;"
                        >
                            {{ $task->equipment->name }} — {{ $task->name }}
                            <span style="color: var(--fi-color-gray-500); font-weight: 400;">
                                (срок {{ $task->next_due_date?->format('d.m.Y') }}, просрочка {{ abs($task->days_until_due) }} дн.)
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </x-filament::section>
    @endif

    <x-filament::section>
        <x-slot name="heading">Календарь месяца</x-slot>

        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; margin-bottom: 6px;">
            @foreach (['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'] as $weekday)
                <div class="text-sm font-semibold text-center text-gray-500">{{ $weekday }}</div>
            @endforeach
        </div>

        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px;">
            @for ($i = 1; $i <= $leadingBlanks; $i++)
                <div style="min-height: 96px; border: 1px dashed transparent;"></div>
            @endfor

            @foreach ($days as $day => $items)
                @php
                    $isToday = $day === now()->day && $month === now()->format('Y-m');
                @endphp
                <div
                    style="
                        min-height: 96px;
                        border: 1px solid var(--fi-color-gray-200);
                        border-radius: 8px;
                        padding: 6px;
                        background: var(--fi-color-white);
                        @if ($isToday) border: 2px solid var(--fi-color-primary-500); @endif
                    "
                >
                    <div style="font-size: 0.8rem; font-weight: 600; margin-bottom: 4px; @if ($isToday) color: var(--fi-color-primary-600); @endif">
                        {{ $day }}
                    </div>

                    @foreach ($items['tasks'] as $task)
                        <a
                            href="{{ \App\Filament\Resources\Equipment\EquipmentResource::getUrl('view', ['record' => $task->equipment_id]) }}"
                            title="{{ $task->name }} ({{ $task->equipment->name }})"
                            style="
                                display: block;
                                font-size: 0.7rem;
                                line-height: 1.2;
                                padding: 3px 5px;
                                border-radius: 4px;
                                margin-bottom: 3px;
                                background: var(--fi-color-warning-100);
                                color: var(--fi-color-warning-800);
                                text-decoration: none;
                                overflow: hidden;
                                text-overflow: ellipsis;
                                white-space: nowrap;
                            "
                        >
                            {{ $task->name }}
                        </a>
                    @endforeach

                    @foreach ($items['orders'] as $order)
                        <a
                            href="{{ \App\Filament\Resources\WorkOrders\WorkOrderResource::getUrl('edit', ['record' => $order]) }}"
                            title="{{ $order->title }}"
                            style="
                                display: block;
                                font-size: 0.7rem;
                                line-height: 1.2;
                                padding: 3px 5px;
                                border-radius: 4px;
                                margin-bottom: 3px;
                                background: var(--fi-color-info-100);
                                color: var(--fi-color-info-800);
                                text-decoration: none;
                                overflow: hidden;
                                text-overflow: ellipsis;
                                white-space: nowrap;
                            "
                        >
                            {{ \Illuminate\Support\Str::limit($order->title, 22) }}
                        </a>
                    @endforeach
                </div>
            @endforeach
        </div>

        <div style="display: flex; gap: 16px; margin-top: 12px; font-size: 0.8rem; color: var(--fi-color-gray-500);">
            <span>
                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 3px; background: var(--fi-color-warning-100); border: 1px solid var(--fi-color-warning-300);"></span>
                Срок по плановой задаче
            </span>
            <span>
                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 3px; background: var(--fi-color-info-100); border: 1px solid var(--fi-color-info-300);"></span>
                Заявка на обслуживание
            </span>
        </div>
    </x-filament::section>
</x-filament-panels::page>
