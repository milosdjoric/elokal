<?php

namespace Tests\Feature\Storefront;

use App\Models\GiftCard;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftCardFeatureFlagTest extends TestCase
{
    use RefreshDatabase;

    public function test_gift_card_routes_return_403_when_feature_disabled(): void
    {
        Setting::setValue('features', 'feature_gift_cards', 'false');

        $this->postJson('/api/v1/gift-card/check', ['code' => 'NEPOSTOJI'])->assertForbidden();
        $this->getJson('/api/v1/gift-cards/NEPOSTOJI/check')->assertForbidden();
        $this->postJson('/api/v1/gift-card/purchase', [
            'amount' => 1000,
            'recipient_email' => 'neko@test.com',
            'recipient_name' => 'Neko',
        ])->assertForbidden();

        $this->assertDatabaseCount('gift_cards', 0);
    }

    public function test_checkout_rejects_gift_card_code_when_feature_disabled(): void
    {
        Setting::setValue('features', 'feature_gift_cards', 'false');

        // Validna kartica sa stanjem — bez gate-a checkout bi je unovčio
        $card = GiftCard::create([
            'code' => 'GIFT-1234',
            'initial_amount' => 500,
            'balance' => 500,
            'is_active' => true,
        ]);

        $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 5, 'is_active' => true]);

        $response = $this->postJson('/api/v1/checkout', [
            'email' => 'kupac@test.com',
            'shipping_first_name' => 'Marko',
            'shipping_last_name' => 'Marković',
            'shipping_address_line_1' => 'Knez Mihailova 10',
            'shipping_city' => 'Beograd',
            'shipping_postal_code' => '11000',
            'shipping_country' => 'RS',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'gift_card_code' => 'GIFT-1234',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['gift_card_code']);

        $this->assertDatabaseCount('orders', 0);
        $this->assertEquals(500.0, (float) $card->fresh()->balance);
    }

    public function test_gift_card_routes_work_when_feature_enabled(): void
    {
        Setting::setValue('features', 'feature_gift_cards', 'true');

        // Nepostojeća kartica → 404 (ruta dostupna, poslovna logika radi)
        $this->postJson('/api/v1/gift-card/check', ['code' => 'NEPOSTOJI'])->assertNotFound();
        $this->getJson('/api/v1/gift-cards/NEPOSTOJI/check')->assertNotFound();
    }
}
