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
            $table->foreignId('scooter_log_id')->nullable()->constrained('scooter_logs')->onDelete('set null'); // الرابط بالحدث الذي أدى للصيانة
            
            $table->enum('type', [
                'scheduled',      // صيانة دورية
                'repair',         // إصلاح عطل
                'battery_replacement', // استبدال بطارية
                'firmware_update', // تحديث البرنامج
                'inspection',     // فحص
                'other'           // أخرى
            ])->default('repair');
            
            $table->string('title'); // عنوان الصيانة
            $table->text('description')->nullable(); // وصف المشكلة/العمل المطلوب
            $table->text('fault_details')->nullable(); // تفاصيل العطل إن وجد
            
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            
            // الفني المسؤول
            $table->string('technician_name')->nullable();
            $table->string('technician_phone')->nullable();
            $table->string('technician_email')->nullable();
            
            // التواريخ
            $table->timestamp('reported_at')->useCurrent(); // تاريخ الإبلاغ
            $table->timestamp('scheduled_at')->nullable(); // تاريخ الصيانة المقرر
            $table->timestamp('started_at')->nullable(); // تاريخ بدء الصيانة
            $table->timestamp('completed_at')->nullable(); // تاريخ إتمام الصيانة
            
            // التكلفة
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();
            
            // ملاحظات
            $table->text('technician_notes')->nullable(); // ملاحظات الفني
            $table->text('resolution_notes')->nullable(); // ملاحظات الحل
            $table->text('parts_replaced')->nullable(); // الأجزاء المستبدلة (JSON أو نص)
            
            // تقييم الصيانة
            $table->unsignedTinyInteger('quality_rating')->nullable(); // تقييم جودة الصيانة (1-5)
            $table->text('quality_notes')->nullable(); // ملاحظات التقييم
            
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
