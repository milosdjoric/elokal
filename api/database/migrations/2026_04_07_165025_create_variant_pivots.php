<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variant_attribute_value', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained()->cascadeOnDelete();
            $table->primary(['product_variant_id', 'attribute_value_id'], 'variant_attr_val_primary');
        });

        Schema::create('product_variant_image', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_image_id')->constrained()->cascadeOnDelete();
            $table->primary(['product_variant_id', 'product_image_id'], 'variant_image_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_image');
        Schema::dropIfExists('product_variant_attribute_value');
    }
};
