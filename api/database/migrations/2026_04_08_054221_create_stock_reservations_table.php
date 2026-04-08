<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('session_id');
            $table->integer('quantity');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index('expires_at');
            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
    }
};
