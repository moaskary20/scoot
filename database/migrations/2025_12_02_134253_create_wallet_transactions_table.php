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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('trip_id')->nullable()->constrained()->onDelete('set null');

            $table->enum('type', [
                'top_up',
                'trip_payment',
                'penalty',
                'refund',
                'adjustment',
                'subscription',
                'other'
            ])->default('other');

            $table->enum('transaction_type', ['credit', 'debit']);

            $table->decimal('amount', 10, 2);
            $table->decimal('balance_before', 10, 2);
            $table->decimal('balance_after', 10, 2);

            $table->string('reference')->nullable();
            $table->string('payment_method')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('completed');

            $table->text('description')->nullable();
            $table->text('notes')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamp('processed_at')->useCurrent();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'status']);
            $table->index('trip_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
