<?php

namespace Tests\Feature\Storefront;

use App\Models\Product;
use App\Models\Setting;
use App\Models\StoreCreditAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutFeatureFlagTest extends TestCase
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

    private function createUserWithCredits(float $balance): User
    {
        $user = User::factory()->create();
        StoreCreditAccount::create(['user_id' => $user->id, 'balance' => $balance]);

        return $user;
    }

    public function test_checkout_rejects_store_credits_when_feature_disabled(): void
    {
        Setting::setValue('features', 'feature_store_credits', 'false');

        $user = $this->createUserWithCredits(500);
        $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10, 'is_active' => true]);

        $response = $this->actingAs($user)->postJson('/api/v1/checkout', $this->validCheckoutData(
            [['product_id' => $product->id, 'quantity' => 1]],
            ['store_credits' => 500],
        ));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['store_credits']);

        // Ništa nije smelo da se desi: nema porudžbine, krediti netaknuti
        $this->assertDatabaseCount('orders', 0);
        $this->assertEquals(500.0, (float) StoreCreditAccount::where('user_id', $user->id)->first()->balance);
    }

    public function test_checkout_applies_store_credits_when_feature_enabled(): void
    {
        Setting::setValue('features', 'feature_store_credits', 'true');

        $user = $this->createUserWithCredits(500);
        $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10, 'is_active' => true]);

        $response = $this->actingAs($user)->postJson('/api/v1/checkout', $this->validCheckoutData(
            [['product_id' => $product->id, 'quantity' => 1]],
            ['store_credits' => 300],
        ));

        $response->assertCreated()
            ->assertJsonPath('data.total', '700.00');

        $account = StoreCreditAccount::where('user_id', $user->id)->first();
        $this->assertEquals(200.0, (float) $account->balance);

        // Transakcija zabeležena sa ispravnim kolonama
        $this->assertDatabaseHas('store_credit_transactions', [
            'store_credit_account_id' => $account->id,
            'type' => 'debit',
            'amount' => '-300.00',
            'balance_after' => '200.00',
        ]);
    }
}
