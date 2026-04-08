<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // products, inventory
            $table->string('filename');
            $table->integer('rows_total')->default(0);
            $table->integer('rows_created')->default(0);
            $table->integer('rows_updated')->default(0);
            $table->integer('rows_failed')->default(0);
            $table->json('errors')->nullable();
            $table->string('status')->default('completed'); // completed, partial, failed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};
