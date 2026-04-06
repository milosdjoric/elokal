<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->timestamp('sale_price_from')->nullable();
            $table->timestamp('sale_price_to')->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->string('unit_label')->nullable();
            $table->string('sku')->unique()->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
