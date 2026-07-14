<?php

namespace Tests\Feature\Storefront;

use App\Models\LoyaltyAccount;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyFeatureFlagTest extends TestCase
{
    use RefreshDatabase;

    private function validCheckoutData(array $items, array $overrides = []): array
    {
        return array_merge([
            'email' => 'kupac@test.com',
            'shipping_first_name' => 'Marko',
            'shipping_last_name' => 'Marković',
            'shipping_address_line_1' => 'Knez Mihailova 10',
            'shipping_city' => 'Beograd',
            'shipping_postal_code' => '11000',
            'shipping_country' => 'RS',
            'items' => $items,
        ], $overrides);
    }

    private function createUserWithPoints(int $points): User
    {
        $user = User::factory()->create();
        LoyaltyAccount::create(['user_id' => $user->id, 'points_balance' => $points]);

        return $user;
    }

    public function test_checkout_rejects_loyalty_points_when_feature_disabled(): void
    {
        Setting::setValue('features', 'feature_loyalty', 'false');

        $user = $this->createUserWithPoints(500);
        $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10, 'is_active' => true]);

        $response = $this->actingAs($user)->postJson('/api/v1/checkout', $this->validCheckoutData(
            [['product_id' => $product->id, 'quantity' => 1]],
            ['loyalty_points' => 300],
        ));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['loyalty_points']);

        $this->assertDatabaseCount('orders', 0);
        $this->assertEquals(500, LoyaltyAccount::where('user_id', $user->id)->first()->points_balance);
    }

    public function test_loyalty_balance_route_returns_403_when_feature_disabled(): void
    {
        Setting::setValue('features', 'feature_loyalty', 'false');

        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/v1/loyalty/balance')->assertForbidden();
    }

    public function test_checkout_applies_loyalty_points_when_feature_enabled(): void
    {
        Setting::setValue('features', 'feature_loyalty', 'true');

        $user = $this->createUserWithPoints(500);
        $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10, 'is_active' => true]);

        $response = $this->actingAs($user)->postJson('/api/v1/checkout', $this->validCheckoutData(
            [['product_id' => $product->id, 'quantity' => 1]],
            ['loyalty_points' => 300],
        ));

        $response->assertCreated()
            ->assertJsonPath('data.total', '700.00');

        $account = LoyaltyAccount::where('user_id', $user->id)->first();
        $this->assertEquals(200, $account->points_balance);

        $this->assertDatabaseHas('loyalty_transactions', [
            'loyalty_account_id' => $account->id,
            'type' => 'redeem',
            'points' => -300,
        ]);
    }
}
