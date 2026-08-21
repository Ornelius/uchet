<?php

namespace Database\Seeders;

use App\Enums\EquipmentStatus;
use App\Enums\IntervalUnit;
use App\Enums\OrderPriority;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\TaskType;
use App\Enums\WorkCategory;
use App\Models\Equipment;
use App\Models\Facility;
use App\Models\ServiceTask;
use App\Models\User;
use App\Models\WorkJournalEntry;
use App\Models\WorkOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Администратор',
                'password' => Hash::make('password'),
            ],
        );

        $facilityA = Facility::updateOrCreate(
            ['name' => 'Котельная №1 (ул. Ленина, 12)'],
            [
                'address' => 'г. Тверь, ул. Ленина, д. 12',
                'commissioning_date' => now()->subYears(10),
                'thermal_power' => 12.5,
                'responsible_person' => 'Петров Иван Сергеевич',
                'responsible_phone' => '+7 (900) 111-22-33',
                'notes' => 'Основная котельная микрорайона «Северный».',
            ],
        );

        $facilityB = Facility::updateOrCreate(
            ['name' => 'Котельная №2 (ул. Гагарина, 5)'],
            [
                'address' => 'г. Тверь, ул. Гагарина, д. 5',
                'commissioning_date' => now()->subYears(4),
                'thermal_power' => 8.0,
                'responsible_person' => 'Сидорова Анна Викторовна',
                'responsible_phone' => '+7 (900) 444-55-66',
                'notes' => 'Модульная котельная школы №7.',
            ],
        );

        $this->seedFacilityA($facilityA);
        $this->seedFacilityB($facilityB);
    }

    private function seedFacilityA(Facility $facility): void
    {
        $boiler = Equipment::updateOrCreate(
            ['serial_number' => 'ИНВ-0001'],
            [
                'facility_id' => $facility->id,
                'name' => 'Котёл КВ-ГМ-10',
                'type' => 'Котёл',
                'manufacturer' => 'ООО «Бийскэнергомаш»',
                'model' => 'КВ-ГМ-10',
                'power' => 10.0,
                'installation_date' => now()->subYears(8),
                'warranty_until' => now()->subYears(6),
                'status' => EquipmentStatus::InService,
                'notes' => 'Основной водогрейный котёл.',
            ],
        );

        $gasMeter = Equipment::updateOrCreate(
            ['serial_number' => 'ИНВ-0002'],
            [
                'facility_id' => $facility->id,
                'name' => 'Счётчик газа СГ-16',
                'type' => 'Счётчик газа',
                'manufacturer' => 'АО «Газдевайс»',
                'model' => 'СГ-16М-200',
                'power' => null,
                'installation_date' => now()->subYears(3),
                'warranty_until' => now()->subYears(2),
                'status' => EquipmentStatus::InService,
                'notes' => 'Узловой учёт природного газа.',
            ],
        );

        $pump = Equipment::updateOrCreate(
            ['serial_number' => 'ИНВ-0003'],
            [
                'facility_id' => $facility->id,
                'name' => 'Сетевой насос 1Д-315-71',
                'type' => 'Насос',
                'manufacturer' => 'ООО «Гидромаш»',
                'model' => '1Д-315-71',
                'power' => 75.0,
                'installation_date' => now()->subYears(5),
                'status' => EquipmentStatus::InService,
            ],
        );

        $boilerTasks = [
            [
                'name' => 'Замена вибровставки',
                'type' => TaskType::Replacement,
                'interval_amount' => 1,
                'interval_unit' => IntervalUnit::Years,
                'last_executed_date' => now()->subYears(2),
                'notes' => 'Просрочена — замену не выполняли с прошлого цикла.',
            ],
            [
                'name' => 'Проверка автоматики безопасности',
                'type' => TaskType::Maintenance,
                'interval_amount' => 6,
                'interval_unit' => IntervalUnit::Months,
                'last_executed_date' => now()->subMonths(7),
            ],
        ];

        $gasMeterTasks = [
            [
                'name' => 'Поверка счётчика газа',
                'type' => TaskType::Calibration,
                'interval_amount' => 5,
                'interval_unit' => IntervalUnit::Years,
                'last_executed_date' => now()->subYears(5)->addDays(20),
                'notes' => 'Срок поверки истекает в ближайшие месяцы.',
            ],
        ];

        $pumpTasks = [
            [
                'name' => 'Замена сальникового уплотнения',
                'type' => TaskType::Replacement,
                'interval_amount' => 2,
                'interval_unit' => IntervalUnit::Years,
                'last_executed_date' => now()->subYears(1)->subMonths(6),
            ],
            [
                'name' => 'Ежегодное техническое обслуживание',
                'type' => TaskType::Maintenance,
                'interval_amount' => 1,
                'interval_unit' => IntervalUnit::Years,
                'last_executed_date' => now()->subMonths(5),
            ],
        ];

        foreach ($boilerTasks as $data) {
            $this->createTask($boiler, $data['name'], $data);
        }

        foreach ($gasMeterTasks as $data) {
            $this->createTask($gasMeter, $data['name'], $data);
        }

        foreach ($pumpTasks as $data) {
            $this->createTask($pump, $data['name'], $data);
        }

        $this->seedHistory($facility, [
            [
                'equipment' => $boiler,
                'category' => WorkCategory::Breakdown,
                'description' => 'Аварийное отключение по датчику давления — заменён датчик.',
                'work_date' => now()->subMonths(2),
            ],
            [
                'equipment' => $gasMeter,
                'category' => WorkCategory::Calibration,
                'description' => 'Внеплановая поверка после переноса узла учёта.',
                'work_date' => now()->subMonths(8),
            ],
            [
                'equipment' => $pump,
                'category' => WorkCategory::Repair,
                'description' => 'Замена подшипников, балансировка ротора.',
                'work_date' => now()->subMonths(3),
            ],
        ]);

        $this->createOrder($boiler, $boilerTasks[0]['name'], [
            'status' => OrderStatus::Open,
            'type' => OrderType::Emergency,
            'priority' => OrderPriority::High,
            'planned_date' => now()->subDays(5),
            'description' => 'Срочная замена вибровставки после вибрационного контроля.',
        ]);
    }

    private function seedFacilityB(Facility $facility): void
    {
        $boiler = Equipment::updateOrCreate(
            ['serial_number' => 'ИНВ-0101'],
            [
                'facility_id' => $facility->id,
                'name' => 'Котёл КСВа-4.0 Гн',
                'type' => 'Котёл',
                'manufacturer' => 'АО «Промэнерго»',
                'model' => 'КСВа-4.0 Гн',
                'power' => 4.0,
                'installation_date' => now()->subYears(4),
                'status' => EquipmentStatus::InService,
            ],
        );

        $meter = Equipment::updateOrCreate(
            ['serial_number' => 'ИНВ-0102'],
            [
                'facility_id' => $facility->id,
                'name' => 'Счётчик газа СГ-6',
                'type' => 'Счётчик газа',
                'manufacturer' => 'АО «Газдевайс»',
                'model' => 'СГ-6М-100',
                'installation_date' => now()->subYears(2),
                'status' => EquipmentStatus::InService,
            ],
        );

        $this->createTask($boiler, 'Техническое обслуживание горелки', [
            'name' => 'Техническое обслуживание горелки',
            'type' => TaskType::Maintenance,
            'interval_amount' => 1,
            'interval_unit' => IntervalUnit::Years,
            'last_executed_date' => now()->subYears(1)->subMonths(1),
        ]);

        $this->createTask($meter, 'Поверка счётчика газа', [
            'name' => 'Поверка счётчика газа',
            'type' => TaskType::Calibration,
            'interval_amount' => 5,
            'interval_unit' => IntervalUnit::Years,
            'last_executed_date' => now()->subYears(5)->addDays(15),
        ]);

        $this->seedHistory($facility, [
            [
                'equipment' => $boiler,
                'category' => WorkCategory::PlannedService,
                'description' => 'Плановое ТО горелки, чистка форсунок.',
                'work_date' => now()->subMonths(11),
            ],
        ]);
    }

    private function createTask(Equipment $equipment, string $name, array $data): ServiceTask
    {
        return ServiceTask::updateOrCreate(
            ['equipment_id' => $equipment->id, 'name' => $name],
            [
                'type' => $data['type'],
                'interval_amount' => $data['interval_amount'],
                'interval_unit' => $data['interval_unit'],
                'last_executed_date' => $data['last_executed_date'],
                'notes' => $data['notes'] ?? null,
            ],
        );
    }

    private function seedHistory(Facility $facility, array $entries): void
    {
        foreach ($entries as $entry) {
            WorkJournalEntry::updateOrCreate(
                [
                    'equipment_id' => $entry['equipment']->id,
                    'description' => $entry['description'],
                ],
                [
                    'category' => $entry['category'],
                    'work_date' => $entry['work_date']->toDateString(),
                    'performed_by' => 'Петров Иван Сергеевич',
                ],
            );
        }
    }

    private function createOrder(Equipment $equipment, string $title, array $data): WorkOrder
    {
        return WorkOrder::updateOrCreate(
            ['equipment_id' => $equipment->id, 'title' => $title],
            [
                'type' => $data['type'],
                'status' => $data['status'],
                'priority' => $data['priority'],
                'planned_date' => $data['planned_date'],
                'description' => $data['description'] ?? null,
            ],
        );
    }
}