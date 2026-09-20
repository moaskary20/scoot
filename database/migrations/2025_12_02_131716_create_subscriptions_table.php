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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('name');
            $table->enum('type', ['minutes', 'unlimited'])->default('minutes');
            $table->unsignedInteger('minutes_included')->nullable();

            $table->decimal('price', 10, 2);
            $table->enum('billing_period', ['daily', 'weekly', 'monthly', 'yearly'])->default('monthly');

            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->timestamp('renewed_at')->nullable();

            $table->boolean('auto_renew')->default(false);
            $table->enum('status', ['active', 'expired', 'cancelled', 'suspended'])->default('active');

            $table->unsignedInteger('minutes_used')->default(0);
            $table->unsignedInteger('trips_count')->default(0);

            $table->foreignId('coupon_id')->nullable()->constrained()->onDelete('set null');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['expires_at', 'status']);
            $table->index('auto_renew');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
