<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('refunded_amount', 12, 2)->default(0)->after('total');
            $table->text('refund_reason')->nullable()->after('refunded_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['refunded_amount', 'refund_reason']);
        });
    }
};
