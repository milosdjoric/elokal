<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Price history za Product — neophodno za prikaz "najniža cena u poslednjih 30 dana"
 * (Zakon o trgovini RS, čl. 32a — kod oglašavanja sniženja cena).
 *
 * Snimamo svaki put kad se promeni `price` ili `sale_price` na Product modelu
 * (vidi App\Observers\ProductObserver).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->decimal('effective_price', 10, 2)->comment('min(price, active sale_price) na trenutku snimanja');
            $table->timestamp('recorded_at')->index();
            $table->timestamps();

            $table->index(['product_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_price_histories');
    }
};
