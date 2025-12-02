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
        // إضافة foreign keys بعد إنشاء جميع الجداول
        Schema::table('trips', function (Blueprint $table) {
            // إضافة foreign key للكوبونات (بعد إنشاء coupons table)
            $table->foreign('coupon_id')
                ->references('id')
                ->on('coupons')
                ->onDelete('set null');
            
            // إضافة foreign key للغرامات (بعد إنشاء penalties table)
            $table->foreign('penalty_id')
                ->references('id')
                ->on('penalties')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropForeign(['penalty_id']);
        });
    }
};
