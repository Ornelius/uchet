<?php

namespace Tests\Feature;

use App\Enums\IntervalUnit;
use App\Enums\TaskStatus;
use App\Models\Equipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_code_is_generated_when_equipment_is_created(): void
    {
        $equipment = Equipment::factory()->create()->refresh();

        $this->assertNotNull($equipment->qr_code);
        $this->assertFileExists(storage_path('app/public/'.$equipment->qr_code));
        $this->assertFileExists(storage_path('app/public/'.str_replace('.svg', '.png', $equipment->qr_code)));
    }

    public function test_qr_code_url_points_to_equipment_view(): void
    {
        $equipment = Equipment::factory()->create();

        $this->assertSame(
            rtrim(config('app.url'), '/').'/admin/equipment/'.$equipment->id,
            app(\App\Services\QrCodeService::class)->getUrl($equipment),
        );

        $this->assertSame(
            rtrim(config('app.url'), '/').'/storage/'.$equipment->qr_code,
            $equipment->qr_url,
        );
    }

    public function test_worst_task_status_aggregates_overdue_first(): void
    {
        $equipment = Equipment::factory()->create();

        $equipment->serviceTasks()->create([
            'name' => 'Ок',
            'type' => \App\Enums\TaskType::Maintenance,
            'interval_amount' => 5,
            'interval_unit' => IntervalUnit::Years,
            'last_executed_date' => now()->subMonths(1),
        ]);

        $this->assertSame(TaskStatus::Ok, $equipment->refresh()->worst_task_status);

        $equipment->serviceTasks()->create([
            'name' => 'Просрочка',
            'type' => \App\Enums\TaskType::Maintenance,
            'interval_amount' => 1,
            'interval_unit' => IntervalUnit::Years,
            'last_executed_date' => now()->subYears(2),
        ]);

        $this->assertSame(TaskStatus::Overdue, $equipment->refresh()->worst_task_status);
    }

    public function test_equipment_scopes_filter_by_attention(): void
    {
        $equipment = Equipment::factory()->create();

        $equipment->serviceTasks()->create([
            'name' => 'Просрочка',
            'type' => \App\Enums\TaskType::Maintenance,
            'interval_amount' => 1,
            'interval_unit' => IntervalUnit::Years,
            'last_executed_date' => now()->subYears(2),
        ]);

        $this->assertTrue(Equipment::overdue()->get()->contains($equipment));
        $this->assertFalse(Equipment::dueSoon()->get()->contains($equipment));

        $soon = Equipment::factory()->create();
        $soon->serviceTasks()->create([
            'name' => 'Скоро',
            'type' => \App\Enums\TaskType::Maintenance,
            'interval_amount' => 15,
            'interval_unit' => IntervalUnit::Days,
            'last_executed_date' => now()->subDays(10),
        ]);

        $this->assertTrue(Equipment::dueSoon()->get()->contains($soon));
    }
}
