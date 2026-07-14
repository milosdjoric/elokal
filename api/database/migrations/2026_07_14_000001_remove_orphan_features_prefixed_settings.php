<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Admin Settings UI je pre fixa snimao flagove pod `features_*` ključevima
     * (grupni prefiks), a kanonski ključevi su `feature_*` (jednina) — te redove
     * niko nikad nije čitao, pa se bezbedno brišu.
     */
    public function up(): void
    {
        DB::table('settings')
            ->where('group', 'features')
            ->where('key', 'like', 'features\_%')
            ->delete();
    }

    public function down(): void
    {
        // Obrisani ključevi nikad nisu bili u upotrebi — nema šta da se vraća.
    }
};
