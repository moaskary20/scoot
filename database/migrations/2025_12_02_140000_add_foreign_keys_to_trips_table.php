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
        // التحقق من وجود الجدول أولاً
        if (!Schema::hasTable('trips')) {
            return;
        }
        
        // التحقق من وجود الأعمدة قبل إضافة foreign keys
        if (!Schema::hasColumn('trips', 'coupon_id')) {
            Schema::table('trips', function (Blueprint $table) {
                $table->unsignedBigInteger('coupon_id')->nullable()->after('zone_exit_details');
            });
        }
        
        if (!Schema::hasColumn('trips', 'penalty_id')) {
            Schema::table('trips', function (Blueprint $table) {
                $table->unsignedBigInteger('penalty_id')->nullable()->after('coupon_id');
            });
        }
        
        // إضافة foreign keys بعد إنشاء جميع الجداول
        Schema::table('trips', function (Blueprint $table) {
            // التحقق من وجود foreign key قبل إضافتها
            $foreignKeys = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableForeignKeys('trips');
            
            $hasCouponFK = false;
            $hasPenaltyFK = false;
            
            foreach ($foreignKeys as $foreignKey) {
                if ($foreignKey->getColumns()[0] === 'coupon_id') {
                    $hasCouponFK = true;
                }
                if ($foreignKey->getColumns()[0] === 'penalty_id') {
                    $hasPenaltyFK = true;
                }
            }
            
            // إضافة foreign key للكوبونات (بعد إنشاء coupons table)
            if (!$hasCouponFK && Schema::hasTable('coupons')) {
                $table->foreign('coupon_id')
                    ->references('id')
                    ->on('coupons')
                    ->onDelete('set null');
            }
            
            // إضافة foreign key للغرامات (بعد إنشاء penalties table)
            if (!$hasPenaltyFK && Schema::hasTable('penalties')) {
                $table->foreign('penalty_id')
                    ->references('id')
                    ->on('penalties')
                    ->onDelete('set null');
            }
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
