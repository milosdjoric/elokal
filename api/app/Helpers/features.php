<?php

use App\Enums\FeatureFlag;
use App\Models\Setting;

if (! function_exists('feature')) {
    /**
     * Proveri da li je feature uključen.
     * Čita iz Settings tabele, fallback na config/features.php.
     */
    function feature(FeatureFlag|string $key): bool
    {
        $key = $key instanceof FeatureFlag ? $key->value : $key;

        $value = Setting::getValue($key);

        if ($value !== null) {
            return in_array($value, ['true', '1', true, 1], true);
        }

        return (bool) config("features.{$key}", false);
    }
}
