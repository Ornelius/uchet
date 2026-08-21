<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->foreignId('service_task_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category')->default('planned_service');
            $table->date('work_date');
            $table->text('description');
            $table->string('performed_by')->nullable();
            $table->string('document_number')->nullable();
            $table->json('photos')->nullable();
            $table->timestamps();

            $table->index('work_date');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_journal_entries');
    }
};
