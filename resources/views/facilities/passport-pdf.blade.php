<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Паспорт объекта: {{ $facility->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #1a1a1a;
            margin: 0;
            padding: 24px 28px;
        }
        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header h1 {
            font-size: 16pt;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header .subtitle {
            font-size: 11pt;
            font-weight: bold;
        }
        .header .meta {
            font-size: 9pt;
            color: #555;
            margin-top: 4px;
        }
        h2 {
            font-size: 11pt;
            margin: 16px 0 6px 0;
            border-bottom: 1px solid #999;
            padding-bottom: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }
        th, td {
            border: 1px solid #bbb;
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #eee;
            font-weight: bold;
        }
        .kv table { font-size: 9pt; }
        .kv td:first-child {
            width: 34%;
            background: #f5f5f5;
            font-weight: bold;
        }
        .qr {
            width: 34px;
            height: 34px;
        }
        .footer {
            margin-top: 24px;
            font-size: 9pt;
            border-top: 1px solid #999;
            padding-top: 8px;
            color: #555;
        }
        .sig {
            display: inline-block;
            width: 45%;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Электронный паспорт объекта</h1>
    <div class="subtitle">{{ $facility->name }}</div>
    <div class="meta">
        Сформирован: {{ now()->format('d.m.Y H:i') }} &nbsp;|&nbsp;
        Система учёта оборудования
    </div>
</div>

<h2>1. Сведения об объекте</h2>
<div class="kv">
    <table>
        <tr>
            <td>Наименование</td><td>{{ $facility->name }}</td>
            <td>Адрес</td><td>{{ $facility->address ?? '—' }}</td>
        </tr>
        <tr>
            <td>Введён в эксплуатацию</td><td>{{ $facility->commissioning_date?->format('d.m.Y') ?? '—' }}</td>
            <td>Тепловая мощность</td><td>{{ $facility->thermal_power ? $facility->thermal_power.' Гкал/ч' : '—' }}</td>
        </tr>
        <tr>
            <td>Ответственное лицо</td><td>{{ $facility->responsible_person ?? '—' }}</td>
            <td>Телефон</td><td>{{ $facility->responsible_phone ?? '—' }}</td>
        </tr>
        @if ($facility->notes)
        <tr>
            <td>Примечания</td><td colspan="3">{{ $facility->notes }}</td>
        </tr>
        @endif
    </table>
</div>

<h2>2. Состав оборудования ({{ $facility->equipment->count() }} ед.)</h2>
<table>
    <thead>
    <tr>
        <th style="width:30px">QR</th>
        <th>Наименование</th>
        <th>Инв. №</th>
        <th>Тип</th>
        <th>Установлено</th>
        <th>Статус</th>
        <th>Ближайшее обслуж.</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($facility->equipment as $eq)
        <tr>
            <td>
                @if ($eq->qr_png_path && file_exists($eq->qr_png_path))
                    <img class="qr" src="{{ $eq->qr_png_path }}">
                @else
                    —
                @endif
            </td>
            <td>{{ $eq->name }}<br><span style="color:#666">{{ $eq->manufacturer ?? '' }} {{ $eq->model ?? '' }}</span></td>
            <td>{{ $eq->serial_number }}</td>
            <td>{{ $eq->type }}</td>
            <td>{{ $eq->installation_date?->format('d.m.Y') ?? '—' }}</td>
            <td>{{ $eq->status->getLabel() }}</td>
            <td>{{ $eq->next_service_date?->format('d.m.Y') ?? '—' }}</td>
        </tr>
    @empty
        <tr><td colspan="7">Оборудование не учтено</td></tr>
    @endforelse
    </tbody>
</table>

<h2>3. Периодические задачи обслуживания</h2>
<table>
    <thead>
    <tr>
        <th>Оборудование</th>
        <th>Задача</th>
        <th>Вид работ</th>
        <th>Периодичность</th>
        <th>Последнее выполнение</th>
        <th>Следующее</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($tasksSorted as $task)
        <tr>
            <td>{{ $task->equipment->name }}</td>
            <td>{{ $task->name }}</td>
            <td>{{ $task->type->getLabel() }}</td>
            <td>{{ $task->interval_label }}</td>
            <td>{{ $task->last_executed_date?->format('d.m.Y') ?? '—' }}</td>
            <td>{{ $task->next_due_date?->format('d.m.Y') ?? '—' }}</td>
        </tr>
    @empty
        <tr><td colspan="6">Задачи не назначены</td></tr>
    @endforelse
    </tbody>
</table>

<h2>4. Последние заявки</h2>
<table>
    <thead>
    <tr>
        <th>Плановая дата</th>
        <th>Заявка</th>
        <th>Оборудование</th>
        <th>Тип</th>
        <th>Статус</th>
        <th>Исполнитель</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($facility->workOrders as $order)
        <tr>
            <td>{{ $order->planned_date->format('d.m.Y') }}</td>
            <td>{{ $order->title }}</td>
            <td>{{ $order->equipment->name }}</td>
            <td>{{ $order->type->getLabel() }}</td>
            <td>{{ $order->status->getLabel() }}</td>
            <td>{{ $order->assigned_to ?? '—' }}</td>
        </tr>
    @empty
        <tr><td colspan="6">Заявок нет</td></tr>
    @endforelse
    </tbody>
</table>

<h2>5. Последние работы (журнал)</h2>
<table>
    <thead>
    <tr>
        <th>Дата</th>
        <th>Оборудование</th>
        <th>Категория</th>
        <th>Описание</th>
        <th>Исполнитель</th>
        <th>№ документа</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($facility->workJournalEntries as $entry)
        <tr>
            <td>{{ $entry->work_date->format('d.m.Y') }}</td>
            <td>{{ $entry->equipment->name }}</td>
            <td>{{ $entry->category->getLabel() }}</td>
            <td>{{ \Illuminate\Support\Str::limit($entry->description, 90) }}</td>
            <td>{{ $entry->performed_by ?? '—' }}</td>
            <td>{{ $entry->document_number ?? '—' }}</td>
        </tr>
    @empty
        <tr><td colspan="6">Записей нет</td></tr>
    @endforelse
    </tbody>
</table>

<div class="footer">
    <div class="sig">
        Ответственное лицо: ____________________ / {{ $facility->responsible_person ?? '' }} /
    </div>
    <div class="sig">
        Главный инженер: ____________________
    </div>
    <div style="margin-top:14px">
        Паспорт сформирован автоматически системой учёта оборудования. Дата: {{ now()->format('d.m.Y H:i') }}.
    </div>
</div>

</body>
</html>
