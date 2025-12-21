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
            $table->decimal('trip_start_fee', 8, 2)->default(0.00)->after('price_per_minute');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('geo_zones', function (Blueprint $table) {
            $table->dropColumn('trip_start_fee');
        });
    }
};
