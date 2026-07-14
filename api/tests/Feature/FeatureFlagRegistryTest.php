<?php

namespace Tests\Feature;

use App\Enums\FeatureFlag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Registry test: enum je jedini izvor istine za ključeve flagova
 * i mora ostati sinhronizovan sa config/features.php.
 */
class FeatureFlagRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_enum_cases_match_config_keys_exactly(): void
    {
        $enumKeys = array_map(fn (FeatureFlag $c) => $c->value, FeatureFlag::cases());
        $configKeys = array_keys(config('features'));

        sort($enumKeys);
        sort($configKeys);

        $this->assertSame($configKeys, $enumKeys, 'Enum i config/features.php moraju imati identične ključeve.');
    }

    public function test_feature_helper_accepts_enum(): void
    {
        // Bez Setting reda u bazi — važi config default
        $this->assertTrue(feature(FeatureFlag::Wishlist));
        $this->assertFalse(feature(FeatureFlag::Webhooks));
    }

    public function test_unknown_flag_in_middleware_fails_loudly(): void
    {
        Route::get('/_test/unknown-flag', fn () => 'ok')->middleware('feature:nepostojeci');

        // Typo u imenu flaga = greška u konfiguraciji → 500, ne tihi 403
        $this->getJson('/_test/unknown-flag')->assertStatus(500);
    }
}
