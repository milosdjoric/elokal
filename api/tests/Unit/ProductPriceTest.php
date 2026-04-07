<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPriceTest extends TestCase
{
    use RefreshDatabase;

    public function test_effective_price_returns_regular_price_without_sale(): void
    {
        $product = Product::factory()->create(['price' => 1000, 'sale_price' => null]);

        $this->assertEquals('1000.00', $product->effective_price);
    }

    public function test_effective_price_returns_sale_price_when_active(): void
    {
        $product = Product::factory()->create([
            'price' => 1000,
            'sale_price' => 750,
            'sale_price_from' => now()->subDay(),
            'sale_price_to' => now()->addDay(),
        ]);

        $this->assertEquals('750.00', $product->effective_price);
    }

    public function test_effective_price_returns_regular_when_sale_expired(): void
    {
        $product = Product::factory()->create([
            'price' => 1000,
            'sale_price' => 750,
            'sale_price_from' => now()->subDays(10),
            'sale_price_to' => now()->subDay(),
        ]);

        $this->assertEquals('1000.00', $product->effective_price);
    }

    public function test_effective_price_returns_regular_when_sale_not_started(): void
    {
        $product = Product::factory()->create([
            'price' => 1000,
            'sale_price' => 750,
            'sale_price_from' => now()->addDay(),
            'sale_price_to' => now()->addDays(10),
        ]);

        $this->assertEquals('1000.00', $product->effective_price);
    }

    public function test_sale_is_active_without_date_range(): void
    {
        $product = Product::factory()->create([
            'price' => 1000,
            'sale_price' => 750,
            'sale_price_from' => null,
            'sale_price_to' => null,
        ]);

        $this->assertTrue($product->isSaleActive());
        $this->assertEquals('750.00', $product->effective_price);
    }

    public function test_sale_percentage_calculation(): void
    {
        $product = Product::factory()->create([
            'price' => 1000,
            'sale_price' => 750,
            'sale_price_from' => null,
            'sale_price_to' => null,
        ]);

        $this->assertEquals(25, $product->sale_percentage);
    }

    public function test_sale_percentage_is_null_when_no_sale(): void
    {
        $product = Product::factory()->create([
            'price' => 1000,
            'sale_price' => null,
        ]);

        $this->assertNull($product->sale_percentage);
    }

    public function test_sale_percentage_is_null_when_sale_expired(): void
    {
        $product = Product::factory()->create([
            'price' => 1000,
            'sale_price' => 750,
            'sale_price_from' => now()->subDays(10),
            'sale_price_to' => now()->subDay(),
        ]);

        $this->assertNull($product->sale_percentage);
    }

    public function test_formatted_unit_price(): void
    {
        $product = Product::factory()->create([
            'unit_price' => 250.50,
            'unit_label' => 'kg',
        ]);

        $this->assertEquals('250,50 RSD/kg', $product->formatted_unit_price);
    }

    public function test_formatted_unit_price_is_null_without_data(): void
    {
        $product = Product::factory()->create([
            'unit_price' => null,
            'unit_label' => null,
        ]);

        $this->assertNull($product->formatted_unit_price);
    }

    public function test_on_sale_scope(): void
    {
        Product::factory()->create([
            'sale_price' => 500,
            'sale_price_from' => now()->subDay(),
            'sale_price_to' => now()->addDay(),
        ]);
        Product::factory()->create([
            'sale_price' => 500,
            'sale_price_from' => now()->subDays(10),
            'sale_price_to' => now()->subDay(),
        ]);
        Product::factory()->create(['sale_price' => null]);

        $this->assertEquals(1, Product::onSale()->count());
    }

    public function test_on_sale_scope_includes_no_date_range(): void
    {
        Product::factory()->create([
            'sale_price' => 500,
            'sale_price_from' => null,
            'sale_price_to' => null,
        ]);

        $this->assertEquals(1, Product::onSale()->count());
    }
}
