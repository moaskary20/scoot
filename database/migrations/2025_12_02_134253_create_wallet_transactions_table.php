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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('trip_id')->nullable()->constrained()->onDelete('set null'); // ربط بالرحلة إن وجدت
            
            $table->enum('type', [
                'top_up',           // شحن المحفظة
                'trip_payment',     // دفع رحلة
                'penalty',          // غرامة
                'refund',           // استرداد
                'adjustment',       // تعديل يدوي
                'subscription',     // اشتراك
                'other'             // أخرى
            ])->default('other');
            
            $table->enum('transaction_type', ['credit', 'debit']); // credit = إضافة، debit = خصم
            
            $table->decimal('amount', 10, 2); // المبلغ
            $table->decimal('balance_before', 10, 2); // الرصيد قبل المعاملة
            $table->decimal('balance_after', 10, 2); // الرصيد بعد المعاملة
            
            $table->string('reference')->nullable(); // رقم مرجعي (مثل رقم معاملة الدفع)
            $table->string('payment_method')->nullable(); // طريقة الدفع (cash, card, online, etc.)
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('completed');
            
            $table->text('description')->nullable(); // وصف المعاملة
            $table->text('notes')->nullable(); // ملاحظات إضافية
            
            // بيانات إضافية (JSON)
            $table->json('metadata')->nullable(); // بيانات إضافية مثل تفاصيل الدفع
            
            $table->timestamp('processed_at')->useCurrent(); // تاريخ المعاملة
            
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'status']);
            $table->index('trip_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
