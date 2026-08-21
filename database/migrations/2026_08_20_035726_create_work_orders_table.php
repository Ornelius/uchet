<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->foreignId('service_task_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('type')->default('planned');
            $table->string('status')->default('open');
            $table->string('priority')->default('normal');
            $table->date('planned_date');
            $table->date('due_date')->nullable();
            $table->string('assigned_to')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_auto')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('planned_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
