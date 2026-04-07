<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
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

    public function test_can_list_inventory(): void
    {
        Product::factory()->count(3)->create();

        $this->getJson('/api/admin/inventory', $this->authHeaders())
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_filter_low_stock(): void
    {
        Product::factory()->create(['stock_quantity' => 3]);
        Product::factory()->create(['stock_quantity' => 50]);

        $this->getJson('/api/admin/inventory?low_stock=1', $this->authHeaders())
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_out_of_stock(): void
    {
        Product::factory()->create(['stock_quantity' => 0]);
        Product::factory()->create(['stock_quantity' => 10]);

        $this->getJson('/api/admin/inventory?out_of_stock=1', $this->authHeaders())
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_adjust_stock(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $response = $this->postJson("/api/admin/inventory/{$product->id}/adjust", [
            'quantity' => 5,
            'note' => 'Restock',
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.stock_quantity', 15);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'quantity' => 5,
            'type' => 'adjustment',
        ]);
    }

    public function test_negative_adjustment_clamps_to_zero(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 3]);

        $this->postJson("/api/admin/inventory/{$product->id}/adjust", [
            'quantity' => -10,
        ], $this->authHeaders());

        $this->assertEquals(0, $product->fresh()->stock_quantity);
    }

    public function test_can_view_stock_history(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);
        StockMovement::record($product, -2, 'sale');
        StockMovement::record($product, 5, 'restock');

        $response = $this->getJson("/api/admin/inventory/{$product->id}/history", $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_bulk_adjust(): void
    {
        $p1 = Product::factory()->create(['stock_quantity' => 10]);
        $p2 = Product::factory()->create(['stock_quantity' => 20]);

        $this->postJson('/api/admin/inventory/bulk-adjust', [
            'adjustments' => [
                ['product_id' => $p1->id, 'quantity' => 5],
                ['product_id' => $p2->id, 'quantity' => -3],
            ],
        ], $this->authHeaders())->assertOk();

        $this->assertEquals(15, $p1->fresh()->stock_quantity);
        $this->assertEquals(17, $p2->fresh()->stock_quantity);
    }

    public function test_checkout_creates_stock_movement(): void
    {
        $product = Product::factory()->create(['price' => 500, 'stock_quantity' => 10, 'is_active' => true]);

        $this->postJson('/api/v1/checkout', [
            'email' => 'test@test.com',
            'shipping_first_name' => 'Test',
            'shipping_last_name' => 'User',
            'shipping_address_line_1' => 'Test 1',
            'shipping_city' => 'Beograd',
            'shipping_postal_code' => '11000',
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'quantity' => -2,
            'type' => 'sale',
        ]);
    }
}
