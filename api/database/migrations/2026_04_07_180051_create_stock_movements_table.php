<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity'); // positive = in, negative = out
            $table->string('type'); // sale, return, adjustment, restock, cancellation
            $table->string('reference_type')->nullable(); // order, admin
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('note')->nullable();
            $table->integer('stock_after');
            $table->timestamp('created_at')->useCurrent();

            $table->index('product_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
