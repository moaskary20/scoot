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
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable(); // Changed to longText to support longer API keys
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // إدراج الإعدادات الافتراضية لـ Paymob
        \DB::table('payment_settings')->insert([
            [
                'key' => 'paymob_enabled',
                'value' => '0',
                'description' => 'تفعيل/تعطيل نظام الدفع Paymob',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'paymob_api_key',
                'value' => '',
                'description' => 'Paymob API Key',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'paymob_integration_id',
                'value' => '',
                'description' => 'Paymob Integration ID',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'paymob_hmac_key',
                'value' => '',
                'description' => 'Paymob HMAC Key',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'paymob_merchant_id',
                'value' => '',
                'description' => 'Paymob Merchant ID',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'paymob_iframe_id',
                'value' => '780724',
                'description' => 'Paymob Iframe ID',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'paymob_test_mode',
                'value' => '1',
                'description' => 'وضع الاختبار (1 = مفعل، 0 = معطل)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'paymob_callback_url',
                'value' => '',
                'description' => 'رابط الاستدعاء (Callback URL)',
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
        Schema::dropIfExists('payment_settings');
    }
};
