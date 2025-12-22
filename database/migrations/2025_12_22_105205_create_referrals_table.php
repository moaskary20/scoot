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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade'); // المستخدم الذي قام بالإحالة
            $table->foreignId('referred_id')->constrained('users')->onDelete('cascade'); // المستخدم الذي تمت إحالته
            $table->string('referral_code'); // كود الإحالة
            $table->foreignId('trip_id')->nullable()->constrained('trips')->onDelete('set null'); // الرحلة التي أكملها المستخدم المُحال
            $table->enum('status', ['pending', 'completed', 'rewarded'])->default('pending'); // pending: تم التسجيل، completed: أكمل رحلة، rewarded: تم إعطاء المكافأة
            $table->decimal('reward_amount', 10, 2)->default(0); // المبلغ الممنوح
            $table->timestamp('registered_at')->nullable(); // تاريخ التسجيل
            $table->timestamp('trip_completed_at')->nullable(); // تاريخ إتمام الرحلة
            $table->timestamp('rewarded_at')->nullable(); // تاريخ إعطاء المكافأة
            $table->timestamps();
            
            $table->index(['referrer_id', 'status']);
            $table->index('referral_code');
            $table->unique(['referrer_id', 'referred_id']); // كل مستخدم يمكن إحالته مرة واحدة فقط
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
