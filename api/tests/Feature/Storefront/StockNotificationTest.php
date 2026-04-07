<?php

namespace Tests\Feature\Storefront;

use App\Models\Product;
use App\Models\StockNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_subscribe_to_out_of_stock_product(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 0]);

        $response = $this->postJson("/api/v1/products/{$product->id}/notify-me", [
            'email' => 'kupac@test.com',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('stock_notifications', [
            'product_id' => $product->id,
            'email' => 'kupac@test.com',
            'notified' => false,
        ]);
    }

    public function test_cannot_subscribe_to_in_stock_product(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 5]);

        $this->postJson("/api/v1/products/{$product->id}/notify-me", [
            'email' => 'kupac@test.com',
        ])->assertUnprocessable();
    }

    public function test_duplicate_subscription_does_not_fail(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 0]);
        StockNotification::create([
            'product_id' => $product->id,
            'email' => 'kupac@test.com',
        ]);

        $response = $this->postJson("/api/v1/products/{$product->id}/notify-me", [
            'email' => 'kupac@test.com',
        ]);

        $response->assertCreated();
        $this->assertEquals(1, StockNotification::where('product_id', $product->id)->count());
    }

    public function test_validates_email(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 0]);

        $this->postJson("/api/v1/products/{$product->id}/notify-me", [
            'email' => 'not-valid',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}
