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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->string('name'); // اسم الباقة (30 دقيقة، 100 دقيقة، Unlimited)
            $table->enum('type', ['minutes', 'unlimited'])->default('minutes'); // نوع الباقة
            $table->unsignedInteger('minutes_included')->nullable(); // عدد الدقائق المضمنة (null للـ unlimited)
            
            $table->decimal('price', 10, 2); // سعر الاشتراك
            $table->enum('billing_period', ['daily', 'weekly', 'monthly', 'yearly'])->default('monthly'); // فترة الفوترة
            
            $table->timestamp('starts_at'); // تاريخ بداية الاشتراك
            $table->timestamp('expires_at'); // تاريخ انتهاء الاشتراك
            $table->timestamp('renewed_at')->nullable(); // تاريخ آخر تجديد
            
            $table->boolean('auto_renew')->default(false); // تجديد تلقائي
            $table->enum('status', ['active', 'expired', 'cancelled', 'suspended'])->default('active'); // حالة الاشتراك
            
            $table->unsignedInteger('minutes_used')->default(0); // الدقائق المستخدمة
            $table->unsignedInteger('trips_count')->default(0); // عدد الرحلات المستخدمة في هذا الاشتراك
            
            $table->foreignId('coupon_id')->nullable()->constrained()->onDelete('set null'); // الكوبون المستخدم
            
            $table->text('notes')->nullable(); // ملاحظات
            
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['expires_at', 'status']);
            $table->index('auto_renew');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
