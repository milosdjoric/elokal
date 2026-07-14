<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Product;
use App\Models\StoreCreditAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kontraktni test celog lanca: admin Settings UI → settings endpoint → feature() gate.
 * Payload je identičan onome što admin Settings stranica šalje (kanonski `feature_*`
 * ključevi u grupi `features`, bez grupnog prefiksa).
 */
class FeatureFlagSettingsChainTest extends TestCase
{
    use RefreshDatabase;

    private function adminHeaders(): array
    {
        $admin = Admin::factory()->create();

        return ['Authorization' => 'Bearer '.$admin->createToken('test')->plainTextToken];
    }

    public function test_disabling_store_credits_via_settings_endpoint_blocks_checkout(): void
    {
        $this->putJson('/api/admin/settings', [
            'settings' => [
                ['group' => 'features', 'key' => 'feature_store_credits', 'value' => 'false'],
            ],
        ], $this->adminHeaders())->assertOk();

        $user = User::factory()->create();
        StoreCreditAccount::create(['user_id' => $user->id, 'balance' => 500]);
        $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 5, 'is_active' => true]);

        $this->actingAs($user)->postJson('/api/v1/checkout', [
            'email' => 'kupac@test.com',
            'shipping_first_name' => 'Marko',
            'shipping_last_name' => 'Marković',
            'shipping_address_line_1' => 'Knez Mihailova 10',
            'shipping_city' => 'Beograd',
            'shipping_postal_code' => '11000',
            'shipping_country' => 'RS',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'store_credits' => 200,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['store_credits']);
    }

    public function test_disabling_webhooks_via_settings_endpoint_blocks_webhook_routes(): void
    {
        $headers = $this->adminHeaders();

        $this->putJson('/api/admin/settings', [
            'settings' => [
                ['group' => 'features', 'key' => 'feature_webhooks', 'value' => 'false'],
            ],
        ], $headers)->assertOk();

        $this->getJson('/api/admin/webhooks', $headers)->assertForbidden();
    }
}
