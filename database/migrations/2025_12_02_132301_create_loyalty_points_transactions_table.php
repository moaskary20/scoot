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
        Schema::create('loyalty_points_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('trip_id')->nullable()->constrained()->onDelete('set null');
            
            $table->enum('type', ['earned', 'redeemed', 'adjusted', 'expired'])->default('earned');
            $table->integer('points'); // عدد النقاط (موجب للإضافة، سالب للخصم)
            $table->integer('balance_after'); // الرصيد بعد المعاملة
            
            $table->string('description')->nullable(); // وصف المعاملة
            $table->text('metadata')->nullable(); // بيانات إضافية (JSON)
            
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_points_transactions');
    }
};
