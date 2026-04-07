<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type'); // flat, weight_based, price_based, free
            $table->decimal('cost', 10, 2)->default(0);
            $table->decimal('free_above', 12, 2)->nullable(); // Free shipping above this order amount
            $table->decimal('per_kg_cost', 10, 2)->nullable();
            $table->string('estimated_days')->nullable(); // "2-4"
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};
