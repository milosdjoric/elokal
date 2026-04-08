<?php

use App\Models\Setting;

if (! function_exists('feature')) {
    /**
     * Proveri da li je feature uključen.
     * Čita iz Settings tabele, fallback na config/features.php.
     */
    function feature(string $key): bool
    {
        $value = Setting::getValue($key);

        if ($value !== null) {
            return in_array($value, ['true', '1', true, 1], true);
        }

        return (bool) config("features.{$key}", false);
    }
}
