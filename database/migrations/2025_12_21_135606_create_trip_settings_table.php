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
        Schema::create('trip_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // إدراج الإعدادات الافتراضية
        \DB::table('trip_settings')->insert([
            // الإعدادات المطلوبة
            [
                'key' => 'max_trip_duration_minutes',
                'value' => '120',
                'description' => 'الحد الأقصى لوقت الرحلة بالدقائق',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'max_trip_cost',
                'value' => '500',
                'description' => 'الحد الأقصى لتكلفة الرحلة بالجنيه',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'max_trips_per_day',
                'value' => '10',
                'description' => 'الحد الأقصى لعدد الرحلات في اليوم',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'max_coupon_uses_per_month',
                'value' => '5',
                'description' => 'الحد الأقصى لاستخدام الخصومات في الشهر',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'max_penalties_before_account_suspension',
                'value' => '3',
                'description' => 'الحد الأقصى لعدد مرات الغرامات ثم يتم إغلاق الحساب',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // اقتراحات إضافية
            [
                'key' => 'max_trip_distance_km',
                'value' => '50',
                'description' => 'الحد الأقصى لمسافة الرحلة بالكيلومتر',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'min_trip_duration_minutes',
                'value' => '1',
                'description' => 'الحد الأدنى لوقت الرحلة بالدقائق',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'min_trip_cost',
                'value' => '5',
                'description' => 'الحد الأدنى لتكلفة الرحلة بالجنيه',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'max_discount_percentage',
                'value' => '50',
                'description' => 'الحد الأقصى لنسبة الخصم (%)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'max_penalty_amount',
                'value' => '100',
                'description' => 'الحد الأقصى لمبلغ الغرامة بالجنيه',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'enable_trip_duration_warning',
                'value' => '1',
                'description' => 'تفعيل إشعار عند اقتراب الحد الأقصى لوقت الرحلة',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'trip_duration_warning_threshold',
                'value' => '90',
                'description' => 'نسبة الوقت لإظهار التحذير (%)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'enable_cost_warning',
                'value' => '1',
                'description' => 'تفعيل إشعار عند اقتراب الحد الأقصى لتكلفة الرحلة',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'cost_warning_threshold',
                'value' => '80',
                'description' => 'نسبة التكلفة لإظهار التحذير (%)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_settings');
    }
};
