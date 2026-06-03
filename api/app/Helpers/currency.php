<?php

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;

if (! function_exists('currency_symbol')) {
    /**
     * Simbol podrazumevane valute (npr. "RSD", "€", "$").
     *
     * Izvor istine je Currency model (is_default), tj. po-bazi -> po-klijentu.
     * Kesirano jer se zove i iz Product accessora koji ide u liste (N+1 zastita).
     */
    function currency_symbol(): string
    {
        return Cache::rememberForever('default_currency_symbol', function () {
            return Currency::getDefault()?->symbol ?? 'RSD';
        });
    }
}

if (! function_exists('forget_currency_cache')) {
    /**
     * Ponistava kes podrazumevane valute. Zvati posle promene default valute.
     */
    function forget_currency_cache(): void
    {
        Cache::forget('default_currency_symbol');
    }
}
