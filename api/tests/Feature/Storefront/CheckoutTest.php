<?php

namespace Tests\Feature\Storefront;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function validCheckoutData(array $items, array $overrides = []): array
    {
        return array_merge([
            'email' => 'kupac@test.com',
            'phone' => '+381641234567',
            'shipping_first_name' => 'Marko',
            'shipping_last_name' => 'Marković',
            'shipping_address_line_1' => 'Knez Mihailova 10',
            'shipping_city' => 'Beograd',
            'shipping_postal_code' => '11000',
            'shipping_country' => 'RS',
            'items' => $items,
        ], $overrides);
    }

    private function createProduct(array $overrides = []): Product
    {
        return Product::factory()->create(array_merge([
            'price' => 1000,
            'stock_quantity' => 10,
            'is_active' => true,
        ], $overrides));
    }

    public function test_guest_can_checkout(): void
    {
        $product = $this->createProduct();

        $response = $this->postJson('/api/v1/checkout', $this->validCheckoutData([
            ['product_id' => $product->id, 'quantity' => 2],
        ]));

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.email', 'kupac@test.com')
            ->assertJsonPath('data.total', '2000.00');

        $this->assertDatabaseHas('orders', ['email' => 'kupac@test.com']);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'line_total' => '2000.00',
        ]);
    }

    public function test_authenticated_user_checkout_links_user(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct();

        $response = $this->actingAs($user)->postJson('/api/v1/checkout', $this->validCheckoutData([
            ['product_id' => $product->id, 'quantity' => 1],
        ]));

        $response->assertCreated();
        $this->assertDatabaseHas('orders', ['user_id' => $user->id]);
    }

    public function test_order_number_is_generated(): void
    {
        $product = $this->createProduct();

        $response = $this->postJson('/api/v1/checkout', $this->validCheckoutData([
            ['product_id' => $product->id, 'quantity' => 1],
        ]));

        $orderNumber = $response->json('data.order_number');
        $this->assertMatchesRegularExpression('/^EL-\d{6}-[A-Z0-9]{4}$/', $orderNumber);
    }

    public function test_stock_is_decremented(): void
    {
        $product = $this->createProduct(['stock_quantity' => 10]);

        $this->postJson('/api/v1/checkout', $this->validCheckoutData([
            ['product_id' => $product->id, 'quantity' => 3],
        ]));

        $this->assertEquals(7, $product->fresh()->stock_quantity);
    }

    public function test_checkout_fails_with_insufficient_stock(): void
    {
        $product = $this->createProduct(['stock_quantity' => 2]);

        $response = $this->postJson('/api/v1/checkout', $this->validCheckoutData([
            ['product_id' => $product->id, 'quantity' => 5],
        ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['items']);

        // Stock ostaje nepromenjen
        $this->assertEquals(2, $product->fresh()->stock_quantity);
    }

    public function test_checkout_fails_with_inactive_product(): void
    {
        $product = $this->createProduct(['is_active' => false]);

        $response = $this->postJson('/api/v1/checkout', $this->validCheckoutData([
            ['product_id' => $product->id, 'quantity' => 1],
        ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['items']);
    }

    public function test_checkout_uses_sale_price_when_active(): void
    {
        $product = $this->createProduct([
            'price' => 1000,
            'sale_price' => 750,
            'sale_price_from' => now()->subDay(),
            'sale_price_to' => now()->addDay(),
        ]);

        $response = $this->postJson('/api/v1/checkout', $this->validCheckoutData([
            ['product_id' => $product->id, 'quantity' => 2],
        ]));

        $response->assertCreated()
            ->assertJsonPath('data.total', '1500.00');
    }

    public function test_checkout_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/checkout', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email', 'shipping_first_name', 'shipping_last_name',
                'shipping_address_line_1', 'shipping_city', 'shipping_postal_code',
                'items',
            ]);
    }

    public function test_checkout_validates_items_not_empty(): void
    {
        $response = $this->postJson('/api/v1/checkout', $this->validCheckoutData([]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['items']);
    }

    public function test_checkout_validates_product_exists(): void
    {
        $response = $this->postJson('/api/v1/checkout', $this->validCheckoutData([
            ['product_id' => 99999, 'quantity' => 1],
        ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['items.0.product_id']);
    }

    public function test_checkout_with_multiple_products(): void
    {
        $product1 = $this->createProduct(['price' => 500]);
        $product2 = $this->createProduct(['price' => 300]);

        $response = $this->postJson('/api/v1/checkout', $this->validCheckoutData([
            ['product_id' => $product1->id, 'quantity' => 2],
            ['product_id' => $product2->id, 'quantity' => 3],
        ]));

        $response->assertCreated()
            ->assertJsonPath('data.total', '1900.00');
        $this->assertCount(2, $response->json('data.items'));
    }

    public function test_checkout_saves_notes(): void
    {
        $product = $this->createProduct();

        $response = $this->postJson('/api/v1/checkout', $this->validCheckoutData(
            [['product_id' => $product->id, 'quantity' => 1]],
            ['notes' => 'Molim brzu dostavu'],
        ));

        $response->assertCreated()
            ->assertJsonPath('data.notes', 'Molim brzu dostavu');
    }

    public function test_checkout_is_atomic_on_failure(): void
    {
        $product1 = $this->createProduct(['stock_quantity' => 10]);
        $product2 = $this->createProduct(['stock_quantity' => 1]);

        $this->postJson('/api/v1/checkout', $this->validCheckoutData([
            ['product_id' => $product1->id, 'quantity' => 2],
            ['product_id' => $product2->id, 'quantity' => 5],
        ]));

        // Oba stock-a ostaju nepromenjeni jer je transakcija rollback-ovana
        $this->assertEquals(10, $product1->fresh()->stock_quantity);
        $this->assertEquals(1, $product2->fresh()->stock_quantity);
        $this->assertEquals(0, Order::count());
    }
}
