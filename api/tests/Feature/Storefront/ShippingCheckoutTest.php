<?php

namespace Tests\Feature\Storefront;

use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private ShippingZone $zone;
    private ShippingMethod $standardMethod;
    private ShippingMethod $expressMethod;

    protected function setUp(): void
    {
        parent::setUp();
        $this->zone = ShippingZone::create([
            'name' => 'Srbija', 'countries' => ['RS'], 'is_active' => true,
        ]);
        $this->standardMethod = $this->zone->methods()->create([
            'name' => 'Standardna', 'type' => 'flat', 'cost' => 350,
            'free_above' => 5000, 'estimated_days' => '2-4', 'is_active' => true,
        ]);
        $this->expressMethod = $this->zone->methods()->create([
            'name' => 'Express', 'type' => 'flat', 'cost' => 700,
            'estimated_days' => '1-2', 'is_active' => true,
        ]);
    }

    private function checkoutData(Product $product, int $qty = 1, array $overrides = []): array
    {
        return array_merge([
            'email' => 'test@test.com',
            'shipping_first_name' => 'Test',
            'shipping_last_name' => 'User',
            'shipping_address_line_1' => 'Test 1',
            'shipping_city' => 'Beograd',
            'shipping_postal_code' => '11000',
            'shipping_country' => 'RS',
            'items' => [['product_id' => $product->id, 'quantity' => $qty]],
        ], $overrides);
    }

    public function test_checkout_with_shipping_method_adds_cost(): void
    {
        $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10, 'is_active' => true]);

        $response = $this->postJson('/api/v1/checkout', $this->checkoutData($product, 2, [
            'shipping_method_id' => $this->standardMethod->id,
        ]));

        $response->assertCreated()
            ->assertJsonPath('data.subtotal', '2000.00')
            ->assertJsonPath('data.shipping_cost', '350.00')
            ->assertJsonPath('data.total', '2350.00');
    }

    public function test_checkout_free_shipping_above_threshold(): void
    {
        $product = Product::factory()->create(['price' => 3000, 'stock_quantity' => 10, 'is_active' => true]);

        $response = $this->postJson('/api/v1/checkout', $this->checkoutData($product, 2, [
            'shipping_method_id' => $this->standardMethod->id,
        ]));

        // 6000 > 5000 free_above → shipping = 0
        $response->assertCreated()
            ->assertJsonPath('data.shipping_cost', '0.00')
            ->assertJsonPath('data.total', '6000.00');
    }

    public function test_checkout_express_shipping(): void
    {
        $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10, 'is_active' => true]);

        $response = $this->postJson('/api/v1/checkout', $this->checkoutData($product, 1, [
            'shipping_method_id' => $this->expressMethod->id,
        ]));

        $response->assertCreated()
            ->assertJsonPath('data.shipping_cost', '700.00')
            ->assertJsonPath('data.total', '1700.00');
    }

    public function test_checkout_without_method_uses_cheapest(): void
    {
        $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10, 'is_active' => true]);

        $response = $this->postJson('/api/v1/checkout', $this->checkoutData($product, 1));

        // Auto-select standardna (350) jer je najjeftinija
        $response->assertCreated()
            ->assertJsonPath('data.shipping_cost', '350.00')
            ->assertJsonPath('data.total', '1350.00');
    }

    public function test_shipping_config_endpoint(): void
    {
        $response = $this->getJson('/api/v1/shipping/config');

        $response->assertOk()
            ->assertJsonPath('data.free_shipping_threshold', 5000)
            ->assertJsonPath('data.default_shipping_cost', 350)
            ->assertJsonCount(2, 'data.methods');
    }

    public function test_shipping_config_without_zones(): void
    {
        $this->zone->delete();

        $response = $this->getJson('/api/v1/shipping/config');

        $response->assertOk()
            ->assertJsonPath('data.free_shipping_threshold', null)
            ->assertJsonPath('data.default_shipping_cost', null);
    }
}
