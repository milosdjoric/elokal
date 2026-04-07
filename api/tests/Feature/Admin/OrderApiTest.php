<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
        $this->token = $this->admin->createToken('test')->plainTextToken;
    }

    private function authHeaders(): array
    {
        return ['Authorization' => "Bearer {$this->token}", 'Accept' => 'application/json'];
    }

    private function createOrder(array $overrides = []): Order
    {
        $order = Order::create(array_merge([
            'order_number' => Order::generateOrderNumber(),
            'status' => 'pending',
            'email' => 'test@test.com',
            'shipping_first_name' => 'Marko',
            'shipping_last_name' => 'Marković',
            'shipping_address_line_1' => 'Knez Mihailova 10',
            'shipping_city' => 'Beograd',
            'shipping_postal_code' => '11000',
            'shipping_country' => 'RS',
            'subtotal' => 1000,
            'total' => 1000,
        ], $overrides));

        $product = Product::factory()->create(['price' => 500, 'stock_quantity' => 10]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'price' => 500,
            'quantity' => 2,
            'line_total' => 1000,
        ]);

        return $order;
    }

    // --- Index ---

    public function test_can_list_orders(): void
    {
        $this->createOrder();
        $this->createOrder();

        $response = $this->getJson('/api/admin/orders', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_orders_by_status(): void
    {
        $this->createOrder(['status' => 'pending']);
        $this->createOrder(['status' => 'shipped']);

        $response = $this->getJson('/api/admin/orders?status=shipped', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'shipped');
    }

    public function test_can_search_orders(): void
    {
        $this->createOrder(['order_number' => 'EL-260407-ABCD']);
        $this->createOrder(['order_number' => 'EL-260407-EFGH']);

        $response = $this->getJson('/api/admin/orders?search=ABCD', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.order_number', 'EL-260407-ABCD');
    }

    public function test_unauthenticated_cannot_list_orders(): void
    {
        $this->getJson('/api/admin/orders')
            ->assertUnauthorized();
    }

    // --- Show ---

    public function test_can_show_order_with_timeline(): void
    {
        $order = $this->createOrder();
        $order->timeline()->create([
            'status' => 'pending',
            'note' => 'Kreirana.',
            'actor_type' => 'system',
        ]);

        $response = $this->getJson("/api/admin/orders/{$order->id}", $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.order_number', $order->order_number)
            ->assertJsonCount(1, 'data.timeline');
    }

    // --- Update Status ---

    public function test_can_update_order_status(): void
    {
        $order = $this->createOrder(['status' => 'pending']);

        $response = $this->patchJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'confirmed',
            'admin_notes' => 'Potvrđena telefonom.',
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.status', 'confirmed');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'confirmed']);
    }

    public function test_status_change_creates_timeline_entry(): void
    {
        $order = $this->createOrder(['status' => 'pending']);

        $this->patchJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'confirmed',
            'admin_notes' => 'OK',
        ], $this->authHeaders());

        $this->assertDatabaseHas('order_timeline', [
            'order_id' => $order->id,
            'status' => 'confirmed',
            'old_status' => 'pending',
            'note' => 'OK',
            'actor_type' => 'admin',
            'actor_id' => $this->admin->id,
        ]);
    }

    public function test_cancellation_restores_stock(): void
    {
        $order = $this->createOrder(['status' => 'pending']);
        $product = $order->items->first()->product;
        $stockBefore = $product->stock_quantity;

        $this->patchJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'cancelled',
        ], $this->authHeaders());

        $this->assertEquals($stockBefore + 2, $product->fresh()->stock_quantity);
    }

    public function test_double_cancellation_does_not_double_restore_stock(): void
    {
        $order = $this->createOrder(['status' => 'cancelled']);
        $product = $order->items->first()->product;
        $stockBefore = $product->stock_quantity;

        $this->patchJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'cancelled',
        ], $this->authHeaders());

        $this->assertEquals($stockBefore, $product->fresh()->stock_quantity);
    }

    public function test_update_status_validates_status_value(): void
    {
        $order = $this->createOrder();

        $this->patchJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'invalid_status',
        ], $this->authHeaders())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }
}
