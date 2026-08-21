<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('maintenance');
            $table->unsignedInteger('interval_amount');
            $table->string('interval_unit')->default('months');
            $table->date('last_executed_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['equipment_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_tasks');
    }
};
