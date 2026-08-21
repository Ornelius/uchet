<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\Facility;
use App\Models\ServiceTask;
use App\Models\User;
use App\Models\WorkJournalEntry;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Facility $facility;
    private Equipment $equipment;
    private ServiceTask $task;
    private WorkOrder $order;
    private WorkJournalEntry $entry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->facility = Facility::factory()->create(['name' => 'Котельная №1']);
        $this->equipment = Equipment::factory()->create([
            'facility_id' => $this->facility->id,
            'name' => 'Котёл КВ-ГМ-10',
            'installation_date' => now()->subYears(3),
        ]);

        $this->task = ServiceTask::factory()->create([
            'equipment_id' => $this->equipment->id,
            'name' => 'Замена вибровставки',
            'interval_amount' => 1,
            'interval_unit' => \App\Enums\IntervalUnit::Years,
            'last_executed_date' => now()->subYears(2),
        ]);

        $this->order = WorkOrder::factory()->create([
            'equipment_id' => $this->equipment->id,
            'service_task_id' => $this->task->id,
            'title' => 'Плановое обслуживание: Замена вибровставки',
        ]);

        $this->entry = WorkJournalEntry::factory()->create([
            'equipment_id' => $this->equipment->id,
            'service_task_id' => $this->task->id,
            'work_order_id' => $this->order->id,
            'category' => \App\Enums\WorkCategory::Repair,
            'work_date' => now()->subYears(3)->toDateString(),
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        auth()->guard()->logout();

        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_dashboard_renders_for_authenticated_user(): void
    {
        $this->get('/admin')->assertOk();
    }

    public function test_facilities_list_renders(): void
    {
        $this->get('/admin/facilities')
            ->assertOk()
            ->assertSee('Котельная №1');
    }

    public function test_facility_create_page_renders(): void
    {
        $this->get('/admin/facilities/create')->assertOk();
    }

    public function test_facility_edit_page_renders(): void
    {
        $this->get('/admin/facilities/'.$this->facility->id.'/edit')->assertOk();
    }

    public function test_facility_passport_page_renders_with_sections(): void
    {
        $this->get('/admin/facilities/'.$this->facility->id)
            ->assertOk()
            ->assertSee('Электронный паспорт объекта')
            ->assertSee('Котёл КВ-ГМ-10');
    }

    public function test_facility_passport_shows_interactive_scheme(): void
    {
        \Illuminate\Support\Facades\Storage::disk('public')
            ->put('facilities/schemes/scheme-1.jpg', 'jpg');

        $this->facility->update(['scheme' => 'facilities/schemes/scheme-1.jpg']);

        $this->get('/admin/facilities/'.$this->facility->id)
            ->assertOk()
            ->assertSee('Схема котельной')
            ->assertSee('schemes\\/scheme-1.jpg', escape: false)
            ->assertSee('facility-scheme-'.$this->facility->id, escape: false)
            ->assertSee('vendor/leaflet/leaflet.js', escape: false);
    }

    public function test_facility_form_has_scheme_upload(): void
    {
        $this->get('/admin/facilities/create')
            ->assertOk()
            ->assertSee('Схема (JPG)');
    }

    public function test_equipment_list_renders_with_equipment_name(): void
    {
        $this->get('/admin/equipment')
            ->assertOk()
            ->assertSee('Котёл КВ-ГМ-10');
    }

    public function test_equipment_list_renders_rows_for_components(): void
    {
        $this->equipment->components()->create([
            'name' => 'Контроллер горелки',
            'type' => 'Контроллер',
            'serial_number' => 'CTRL-001',
            'manufacturer' => 'Siemens',
            'model' => 'LME22',
        ]);

        $this->get('/admin/equipment')
            ->assertOk()
            ->assertSee('Котёл КВ-ГМ-10')
            ->assertSee('Контроллер горелки')
            ->assertSee('CTRL-001')
            ->assertSee('LME22');
    }

    public function test_equipment_view_page_renders(): void
    {
        $this->get('/admin/equipment/'.$this->equipment->id)
            ->assertOk()
            ->assertSee('Котёл КВ-ГМ-10');
    }

    public function test_equipment_create_page_renders(): void
    {
        $this->get('/admin/equipment/create')->assertOk();
    }

    public function test_equipment_edit_page_renders(): void
    {
        $this->get('/admin/equipment/'.$this->equipment->id.'/edit')->assertOk();
    }

    public function test_equipment_view_page_shows_components(): void
    {
        $this->equipment->components()->create([
            'name' => 'Контроллер горелки',
            'type' => 'Контроллер',
            'serial_number' => 'CTRL-001',
        ]);

        $this->get('/admin/equipment/'.$this->equipment->id)
            ->assertOk()
            ->assertSee('Составные элементы')
            ->assertSee('Контроллер горелки');
    }

    public function test_equipment_edit_page_has_components_repeater(): void
    {
        $this->get('/admin/equipment/'.$this->equipment->id.'/edit')
            ->assertOk()
            ->assertSee('Составные элементы')
            ->assertSee('Добавить элемент');
    }

    public function test_equipment_view_page_shows_component_photos(): void
    {
        \Illuminate\Support\Facades\Storage::disk('public')->put(
            'equipment/photo-1.jpg',
            '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"></svg>',
        );

        $this->equipment->components()->create([
            'name' => 'Контроллер горелки',
            'photos' => ['equipment/photo-1.jpg'],
        ]);

        $this->get('/admin/equipment/'.$this->equipment->id)
            ->assertOk()
            ->assertSee('Контроллер горелки')
            ->assertSee('storage/equipment/photo-1.jpg');
    }

    public function test_work_orders_list_renders(): void
    {
        $this->get('/admin/work-orders')
            ->assertOk()
            ->assertSee('Плановое обслуживание: Замена вибровставки');
    }

    public function test_work_orders_create_page_renders(): void
    {
        $this->get('/admin/work-orders/create')->assertOk();
    }

    public function test_work_order_edit_page_renders_with_photos(): void
    {
        $this->order->update([
            'photos' => [
                'work-orders/1-before.jpg',
                'work-orders/1-after.jpg',
            ],
        ]);

        $this->get('/admin/work-orders/'.$this->order->id.'/edit')
            ->assertOk()
            ->assertSee('Фотографии');
    }

    public function test_work_orders_table_shows_photo_thumbnails(): void
    {
        \Illuminate\Support\Facades\Storage::disk('public')
            ->put('work-orders/thumbs/1-before.jpg', 'jpg');

        $this->order->update(['photos' => ['work-orders/thumbs/1-before.jpg']]);

        $this->get('/admin/work-orders')
            ->assertOk()
            ->assertSee('/storage/work-orders/thumbs/1-before.jpg', escape: false);
    }

    public function test_work_journal_list_renders(): void
    {
        $this->get('/admin/work-journal-entries')->assertOk();
    }

    public function test_work_journal_create_page_renders(): void
    {
        $this->get('/admin/work-journal-entries/create')->assertOk();
    }

    public function test_maintenance_planning_page_renders(): void
    {
        $this->get('/admin/maintenance-planning')
            ->assertOk()
            ->assertSee('Календарь месяца')
            ->assertSee('Просроченные задачи')
            ->assertSee('Замена вибровставки');
    }

    public function test_facility_passport_pdf_is_generated(): void
    {
        $this->facility->load([
            'equipment' => fn ($query) => $query->with('serviceTasks'),
            'workOrders' => fn ($query) => $query->with('equipment'),
            'workJournalEntries' => fn ($query) => $query->with('equipment'),
        ]);

        $tasks = $this->facility->equipment->flatMap->serviceTasks
            ->sortBy(fn ($task) => $task->next_due_date ?? now()->endOfAllTime())
            ->values();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('facilities.passport-pdf', [
            'facility' => $this->facility,
            'tasksSorted' => $tasks,
        ]);

        $output = $pdf->output();

        $this->assertStringStartsWith('%PDF', $output);
        $this->assertGreaterThan(5000, strlen($output));
    }

    public function test_passport_pdf_download_returns_binary_file_response(): void
    {
        $page = new \App\Filament\Resources\Facilities\Pages\ViewFacility();

        $response = $page->generatePassportPdf($this->facility);

        $this->assertInstanceOf(
            \Symfony\Component\HttpFoundation\BinaryFileResponse::class,
            $response,
        );

        if (is_file($response->getFile()->getRealPath())) {
            unlink($response->getFile()->getRealPath());
        }
    }
}
