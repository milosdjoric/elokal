<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abandoned_carts', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('items'); // cart snapshot
            $table->decimal('total', 12, 2);
            $table->string('token')->unique();
            $table->string('status')->default('abandoned'); // abandoned, recovered, expired
            $table->integer('emails_sent')->default(0);
            $table->timestamp('last_email_at')->nullable();
            $table->timestamp('recovered_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abandoned_carts');
    }
};
