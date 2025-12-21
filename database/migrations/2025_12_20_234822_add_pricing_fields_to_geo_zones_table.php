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
        Schema::table('geo_zones', function (Blueprint $table) {
            $table->boolean('allow_trip_start')->default(true)->after('is_active');
            $table->decimal('price_per_minute', 8, 2)->default(0.00)->after('allow_trip_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('geo_zones', function (Blueprint $table) {
            $table->dropColumn(['allow_trip_start', 'price_per_minute']);
        });
    }
};
