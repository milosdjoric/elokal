<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('downloadable_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('file_path');
            $table->integer('file_size')->default(0); // bajti
            $table->integer('max_downloads')->nullable(); // null = neograničeno
            $table->integer('expires_days')->nullable(); // null = ne ističe
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('product_id');
        });

        Schema::create('download_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('downloadable_file_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('download_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->string('token')->unique();
            $table->timestamps();

            $table->index(['user_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('download_logs');
        Schema::dropIfExists('downloadable_files');
    }
};
