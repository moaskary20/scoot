<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scooter_id')->constrained()->onDelete('cascade');
            $table->foreignId('scooter_log_id')->nullable()->constrained('scooter_logs')->onDelete('set null');

            $table->enum('type', [
                'scheduled',
                'repair',
                'battery_replacement',
                'firmware_update',
                'inspection',
                'other'
            ])->default('repair');

            $table->string('title');
            $table->text('description')->nullable();
            $table->text('fault_details')->nullable();

            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');

            $table->string('technician_name')->nullable();
            $table->string('technician_phone')->nullable();
            $table->string('technician_email')->nullable();

            $table->timestamp('reported_at')->useCurrent();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();

            $table->text('technician_notes')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->text('parts_replaced')->nullable();

            $table->unsignedTinyInteger('quality_rating')->nullable();
            $table->text('quality_notes')->nullable();

            $table->timestamps();

            $table->index(['scooter_id', 'status']);
            $table->index(['status', 'priority']);
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
    }
};
