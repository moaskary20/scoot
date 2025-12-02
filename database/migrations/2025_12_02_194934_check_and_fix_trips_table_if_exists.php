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
        // إذا كان الجدول موجوداً بالفعل، نتحقق من الأعمدة المطلوبة
        if (Schema::hasTable('trips')) {
            Schema::table('trips', function (Blueprint $table) {
                // إضافة coupon_id إذا لم يكن موجوداً
                if (!Schema::hasColumn('trips', 'coupon_id')) {
                    $table->unsignedBigInteger('coupon_id')->nullable()->after('zone_exit_details');
                }
                
                // إضافة penalty_id إذا لم يكن موجوداً
                if (!Schema::hasColumn('trips', 'penalty_id')) {
                    $table->unsignedBigInteger('penalty_id')->nullable()->after('coupon_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // لا حاجة لعكس هذا migration
    }
};
