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
        Schema::create('penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('trip_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('scooter_id')->nullable()->constrained()->onDelete('set null');
            
            $table->enum('type', ['zone_exit', 'forbidden_parking', 'unlocked_scooter', 'other'])->default('other');
            $table->string('title');
            $table->text('description')->nullable();
            
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'waived', 'cancelled'])->default('pending');
            
            $table->boolean('is_auto_applied')->default(false); // تم تطبيقه تلقائياً أم يدوياً
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            $table->text('evidence_data')->nullable(); // بيانات إضافية (JSON) مثل إحداثيات الخروج
            
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['trip_id']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penalties');
    }
};
