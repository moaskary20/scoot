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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->decimal('wallet_balance', 10, 2)->default(0)->after('phone');
            $table->unsignedInteger('loyalty_points')->default(0)->after('wallet_balance');
            $table->enum('loyalty_level', ['bronze', 'silver', 'gold'])->default('bronze')->after('loyalty_points');
            $table->string('avatar')->nullable()->after('loyalty_level');
            $table->boolean('is_active')->default(true)->after('avatar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'wallet_balance', 'loyalty_points', 'loyalty_level', 'avatar', 'is_active']);
        });
    }
};
