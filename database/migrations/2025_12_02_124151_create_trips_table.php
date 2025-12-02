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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('scooter_id')->constrained()->onDelete('cascade');
            
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable(); // مدة الرحلة بالدقائق
            
            $table->decimal('cost', 10, 2)->default(0); // التكلفة الإجمالية
            $table->decimal('base_cost', 10, 2)->default(0); // التكلفة الأساسية
            $table->decimal('discount_amount', 10, 2)->default(0); // قيمة الخصم
            $table->decimal('penalty_amount', 10, 2)->default(0); // قيمة الغرامة إن وجدت
            
            // إحداثيات البداية
            $table->decimal('start_latitude', 10, 7)->nullable();
            $table->decimal('start_longitude', 10, 7)->nullable();
            
            // إحداثيات النهاية
            $table->decimal('end_latitude', 10, 7)->nullable();
            $table->decimal('end_longitude', 10, 7)->nullable();
            
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            
            $table->boolean('zone_exit_detected')->default(false); // كشف خروج من المنطقة
            $table->text('zone_exit_details')->nullable(); // تفاصيل الخروج من المنطقة
            
            $table->foreignId('coupon_id')->nullable()->constrained()->onDelete('set null'); // الكوبون المستخدم
            $table->foreignId('penalty_id')->nullable()->constrained()->onDelete('set null'); // الغرامة المرتبطة
            
            $table->text('notes')->nullable(); // ملاحظات إضافية
            
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['scooter_id', 'status']);
            $table->index('start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
