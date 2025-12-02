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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // كود الكوبون
            $table->string('name'); // اسم الكوبون
            
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage'); // نوع الخصم
            $table->decimal('discount_value', 10, 2); // قيمة الخصم (% أو مبلغ ثابت)
            $table->decimal('max_discount', 10, 2)->nullable(); // الحد الأقصى للخصم (للنسبة المئوية)
            $table->decimal('min_amount', 10, 2)->default(0); // الحد الأدنى لاستخدام الكوبون
            
            $table->unsignedInteger('usage_limit')->nullable(); // عدد مرات الاستخدام الإجمالي
            $table->unsignedInteger('usage_count')->default(0); // عدد مرات الاستخدام الحالي
            $table->unsignedInteger('user_usage_limit')->default(1); // عدد مرات الاستخدام لكل مستخدم
            
            $table->enum('applicable_to', ['trips', 'subscriptions', 'all'])->default('all'); // ينطبق على
            $table->timestamp('starts_at')->nullable(); // تاريخ بداية
            $table->timestamp('expires_at')->nullable(); // تاريخ انتهاء
            
            $table->boolean('is_active')->default(true); // مفعل أم لا
            $table->text('description')->nullable(); // وصف الكوبون
            
            $table->timestamps();
            
            $table->index(['code', 'is_active']);
            $table->index(['expires_at', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
