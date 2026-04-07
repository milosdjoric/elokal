<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // cod, bank_transfer, stripe
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable(); // za offline metode
            $table->decimal('additional_cost', 10, 2)->default(0); // COD fee
            $table->boolean('is_active')->default(true);
            $table->boolean('is_online')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
