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
        // استخدام try-catch لتجنب الأخطاء إذا كانت foreign keys موجودة بالفعل
        try {
            if (Schema::hasTable('coupons') && Schema::hasColumn('trips', 'coupon_id')) {
                Schema::table('trips', function (Blueprint $table) {
                    // محاولة إسقاط foreign key القديم إذا كان موجوداً
                    try {
                        $table->dropForeign(['trips_coupon_id_foreign']);
                    } catch (\Exception $e) {
                        // تجاهل الخطأ إذا لم يكن موجوداً
                    }
                    
                    // إضافة foreign key للكوبونات
                    $table->foreign('coupon_id')
                        ->references('id')
                        ->on('coupons')
                        ->onDelete('set null');
                });
            }
        } catch (\Exception $e) {
            // تجاهل الخطأ إذا كانت foreign key موجودة بالفعل
        }
        
        try {
            if (Schema::hasTable('penalties') && Schema::hasColumn('trips', 'penalty_id')) {
                Schema::table('trips', function (Blueprint $table) {
                    // محاولة إسقاط foreign key القديم إذا كان موجوداً
                    try {
                        $table->dropForeign(['trips_penalty_id_foreign']);
                    } catch (\Exception $e) {
                        // تجاهل الخطأ إذا لم يكن موجوداً
                    }
                    
                    // إضافة foreign key للغرامات
                    $table->foreign('penalty_id')
                        ->references('id')
                        ->on('penalties')
                        ->onDelete('set null');
                });
            }
        } catch (\Exception $e) {
            // تجاهل الخطأ إذا كانت foreign key موجودة بالفعل
        }
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
