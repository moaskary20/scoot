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
        Schema::create('scooters', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('qr_code')->nullable();

            $table->enum('status', ['available', 'rented', 'charging', 'maintenance'])->default('available');
            $table->unsignedTinyInteger('battery_percentage')->default(100);

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('last_seen_at')->nullable();

            $table->boolean('is_locked')->default(true);
            $table->boolean('is_active')->default(true);

            $table->string('device_imei')->nullable();
            $table->string('firmware_version')->nullable();

            $table->timestamp('last_maintenance_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scooters');
    }
};
