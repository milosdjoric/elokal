<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupon_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // min_items, max_items, specific_products, specific_categories, first_order, user_registered_days
            $table->string('operator')->default('eq'); // eq, gt, lt, gte, lte, in, not_in
            $table->text('value'); // JSON za in/not_in, string za ostalo
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_conditions');
    }
};
