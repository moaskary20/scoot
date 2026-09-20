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

        if (Schema::hasTable('trips')) {
            Schema::table('trips', function (Blueprint $table) {

                if (!Schema::hasColumn('trips', 'coupon_id')) {
                    $table->unsignedBigInteger('coupon_id')->nullable()->after('zone_exit_details');
                }

                if (!Schema::hasColumn('trips', 'penalty_id')) {
                    $table->unsignedBigInteger('penalty_id')->nullable()->after('coupon_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
