<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyFeatureFlagTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(): array
    {
        $admin = Admin::factory()->create();

        return ['Authorization' => 'Bearer '.$admin->createToken('test')->plainTextToken];
    }

    public function test_admin_currency_routes_return_403_when_feature_disabled(): void
    {
        Setting::setValue('features', 'feature_multi_currency', 'false');

        $headers = $this->authHeaders();

        $this->getJson('/api/admin/currencies', $headers)->assertForbidden();

        $this->postJson('/api/admin/currencies', [
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
            'exchange_rate' => 117.5,
        ], $headers)->assertForbidden();

        $this->assertDatabaseCount('currencies', 0);
    }

    public function test_admin_currency_routes_work_when_feature_enabled(): void
    {
        Setting::setValue('features', 'feature_multi_currency', 'true');

        $this->getJson('/api/admin/currencies', $this->authHeaders())->assertOk();
    }
}
