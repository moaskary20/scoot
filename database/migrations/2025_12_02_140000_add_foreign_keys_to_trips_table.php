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

        if (!Schema::hasTable('trips')) {
            return;
        }

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

        try {
            if (Schema::hasTable('coupons') && Schema::hasColumn('trips', 'coupon_id')) {
                Schema::table('trips', function (Blueprint $table) {

                    try {
                        $table->dropForeign(['trips_coupon_id_foreign']);
                    } catch (\Exception $e) {

                    }

                    $table->foreign('coupon_id')
                        ->references('id')
                        ->on('coupons')
                        ->onDelete('set null');
                });
            }
        } catch (\Exception $e) {

        }

        try {
            if (Schema::hasTable('penalties') && Schema::hasColumn('trips', 'penalty_id')) {
                Schema::table('trips', function (Blueprint $table) {

                    try {
                        $table->dropForeign(['trips_penalty_id_foreign']);
                    } catch (\Exception $e) {

                    }

                    $table->foreign('penalty_id')
                        ->references('id')
                        ->on('penalties')
                        ->onDelete('set null');
                });
            }
        } catch (\Exception $e) {

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
