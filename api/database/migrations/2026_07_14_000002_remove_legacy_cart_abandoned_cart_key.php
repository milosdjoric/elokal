<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Abandoned cart je imao dva ključa: kanonski `feature_abandoned_cart`
     * (features grupa, čita ga API) i `cart_feature_abandoned_cart` (cart grupa,
     * čitao ga samo ExitIntentPopup). Popup je prebačen na kanonski ključ —
     * stari se briše.
     */
    public function up(): void
    {
        DB::table('settings')
            ->where('key', 'cart_feature_abandoned_cart')
            ->delete();
    }

    public function down(): void
    {
        // Stari ključ je napušten — nema šta da se vraća.
    }
};
