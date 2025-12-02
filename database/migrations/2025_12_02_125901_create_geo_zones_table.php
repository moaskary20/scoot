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
        Schema::create('geo_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['allowed', 'forbidden', 'parking']); // نوع المنطقة
            $table->string('color')->default('#FFD600'); // لون العرض على الخريطة
            $table->json('polygon'); // نقاط الـ Polygon كـ JSON

            $table->decimal('center_latitude', 10, 7)->nullable();
            $table->decimal('center_longitude', 10, 7)->nullable();

            $table->boolean('is_active')->default(true);

            $table->text('description')->nullable();

            $table->timestamps();
            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geo_zones');
    }
};
