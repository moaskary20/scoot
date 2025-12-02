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
        Schema::create('loyalty_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // إدراج الإعدادات الافتراضية
        \DB::table('loyalty_settings')->insert([
            [
                'key' => 'points_per_minute',
                'value' => '1',
                'description' => 'عدد النقاط لكل دقيقة رحلة',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'bronze_threshold',
                'value' => '0',
                'description' => 'الحد الأدنى للنقاط لمستوى Bronze',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'silver_threshold',
                'value' => '500',
                'description' => 'الحد الأدنى للنقاط لمستوى Silver',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'gold_threshold',
                'value' => '1000',
                'description' => 'الحد الأدنى للنقاط لمستوى Gold',
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
        Schema::dropIfExists('loyalty_settings');
    }
};
