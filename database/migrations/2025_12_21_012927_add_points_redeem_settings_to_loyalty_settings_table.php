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
        // إضافة إعدادات استبدال النقاط
        \DB::table('loyalty_settings')->insert([
            [
                'key' => 'points_redeem_enabled',
                'value' => '1',
                'description' => 'تفعيل/تعطيل نظام استبدال النقاط',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'points_to_egp_rate',
                'value' => '100',
                'description' => 'عدد النقاط المطلوبة للحصول على 1 جنيه خصم (مثلاً: 100 نقطة = 1 جنيه)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'min_points_to_redeem',
                'value' => '100',
                'description' => 'الحد الأدنى للنقاط المطلوبة للاستبدال',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'max_redeem_percentage',
                'value' => '50',
                'description' => 'النسبة القصوى من تكلفة الرحلة التي يمكن استبدالها بالنقاط (بالمئة)',
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
        \DB::table('loyalty_settings')
            ->whereIn('key', [
                'points_redeem_enabled',
                'points_to_egp_rate',
                'min_points_to_redeem',
                'max_redeem_percentage',
            ])
            ->delete();
    }
};
