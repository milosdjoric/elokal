<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Proizvodi — najčešće filtrirani
        Schema::table('products', function (Blueprint $table) {
            $table->index(['is_active', 'created_at']);
            $table->index(['is_active', 'price']);
            $table->index('stock_quantity');
        });

        // Narudžbine — filtriranje po statusu i datumu
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
            $table->index('user_id');
        });

        // Order items — join-ovi na izveštajima
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('product_id');
        });

        // Recenzije — filtriranje po statusu
        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'created_at']);
            $table->dropIndex(['is_active', 'price']);
            $table->dropIndex(['stock_quantity']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'status']);
        });
    }
};
