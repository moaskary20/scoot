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
        Schema::create('scooter_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scooter_id')->constrained()->onDelete('cascade');
            $table->foreignId('trip_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            
            $table->enum('event_type', [
                'battery_drop',
                'zone_exit',
                'forced_movement',
                'manual_lock',
                'manual_unlock',
                'auto_lock',
                'auto_unlock',
                'gps_update',
                'status_change',
                'maintenance_start',
                'maintenance_end',
                'other'
            ])->default('other');
            
            $table->string('title'); // عنوان الحدث
            $table->text('description')->nullable(); // وصف تفصيلي
            $table->enum('severity', ['info', 'warning', 'critical'])->default('info'); // مستوى الخطورة
            
            // بيانات إضافية (JSON)
            $table->json('data')->nullable(); // بيانات إضافية مثل الإحداثيات القديمة والجديدة، قيمة البطارية، إلخ
            
            // إحداثيات الموقع عند الحدث
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            
            // حالة السكوتر عند الحدث
            $table->enum('scooter_status', ['available', 'rented', 'charging', 'maintenance'])->nullable();
            $table->unsignedTinyInteger('battery_percentage')->nullable();
            $table->boolean('was_locked')->nullable();
            
            $table->boolean('is_resolved')->default(false); // تم حل المشكلة أم لا
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['scooter_id', 'created_at']);
            $table->index(['event_type', 'severity']);
            $table->index('is_resolved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scooter_logs');
    }
};
