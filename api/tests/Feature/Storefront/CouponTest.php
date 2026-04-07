<?php

namespace Tests\Feature\Storefront;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_validate_coupon(): void
    {
        Coupon::create([
            'code' => 'SAVE10', 'type' => 'percentage', 'value' => 10, 'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/coupon/validate', [
            'code' => 'SAVE10',
            'subtotal' => 1000,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.code', 'SAVE10')
            ->assertJsonPath('data.discount', '100.00');
    }

    public function test_invalid_coupon_code(): void
    {
        $this->postJson('/api/v1/coupon/validate', [
            'code' => 'NOPE',
            'subtotal' => 1000,
        ])->assertUnprocessable();
    }

    public function test_expired_coupon(): void
    {
        Coupon::create([
            'code' => 'EXPIRED', 'type' => 'percentage', 'value' => 10,
            'is_active' => true, 'expires_at' => now()->subDay(),
        ]);

        $this->postJson('/api/v1/coupon/validate', [
            'code' => 'EXPIRED', 'subtotal' => 1000,
        ])->assertUnprocessable();
    }

    public function test_min_order_amount(): void
    {
        Coupon::create([
            'code' => 'MIN500', 'type' => 'fixed_amount', 'value' => 100,
            'min_order_amount' => 500, 'is_active' => true,
        ]);

        $this->postJson('/api/v1/coupon/validate', [
            'code' => 'MIN500', 'subtotal' => 200,
        ])->assertUnprocessable();
    }

    public function test_max_discount_capped(): void
    {
        Coupon::create([
            'code' => 'CAP50', 'type' => 'percentage', 'value' => 50,
            'max_discount_amount' => 200, 'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/coupon/validate', [
            'code' => 'CAP50', 'subtotal' => 1000,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.discount', '200.00');
    }

    public function test_checkout_with_coupon(): void
    {
        $coupon = Coupon::create([
            'code' => 'CHECKOUT10', 'type' => 'percentage', 'value' => 10, 'is_active' => true,
        ]);
        $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10, 'is_active' => true]);

        $response = $this->postJson('/api/v1/checkout', [
            'email' => 'test@test.com',
            'shipping_first_name' => 'Test',
            'shipping_last_name' => 'User',
            'shipping_address_line_1' => 'Test 1',
            'shipping_city' => 'Beograd',
            'shipping_postal_code' => '11000',
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'coupon_code' => 'CHECKOUT10',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.discount', '200.00')
            ->assertJsonPath('data.total', '1800.00');

        $this->assertEquals(1, $coupon->fresh()->times_used);
    }

    public function test_checkout_with_invalid_coupon_fails(): void
    {
        $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10, 'is_active' => true]);

        $this->postJson('/api/v1/checkout', [
            'email' => 'test@test.com',
            'shipping_first_name' => 'Test',
            'shipping_last_name' => 'User',
            'shipping_address_line_1' => 'Test 1',
            'shipping_city' => 'Beograd',
            'shipping_postal_code' => '11000',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'coupon_code' => 'FAKE',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['coupon_code']);
    }

    public function test_max_uses_exceeded(): void
    {
        Coupon::create([
            'code' => 'ONCE', 'type' => 'fixed_amount', 'value' => 50,
            'max_uses' => 1, 'times_used' => 1, 'is_active' => true,
        ]);

        $this->postJson('/api/v1/coupon/validate', [
            'code' => 'ONCE', 'subtotal' => 1000,
        ])->assertUnprocessable();
    }
}
