<?php

namespace Tests\Feature\Storefront;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_active_products(): void
    {
        Product::factory(3)->create(['is_active' => true]);
        Product::factory(2)->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/products');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_inactive_products_are_hidden(): void
    {
        Product::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/products');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_can_filter_by_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['is_active' => true]);
        $product->categories()->attach($category);
        Product::factory(2)->create(['is_active' => true]);

        $response = $this->getJson("/api/v1/products?category={$category->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_by_featured(): void
    {
        Product::factory(2)->create(['featured' => true, 'is_active' => true]);
        Product::factory(3)->create(['featured' => false, 'is_active' => true]);

        $response = $this->getJson('/api/v1/products?featured=1');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_by_price_range(): void
    {
        Product::factory()->create(['price' => 100, 'is_active' => true]);
        Product::factory()->create(['price' => 500, 'is_active' => true]);
        Product::factory()->create(['price' => 1000, 'is_active' => true]);

        $response = $this->getJson('/api/v1/products?min_price=200&max_price=600');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_search_products(): void
    {
        Product::factory()->create(['name' => 'Organic Honey', 'is_active' => true]);
        Product::factory()->create(['name' => 'Fresh Milk', 'is_active' => true]);

        $response = $this->getJson('/api/v1/products?search=Honey');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_sort_products(): void
    {
        Product::factory()->create(['name' => 'Banana', 'price' => 200, 'is_active' => true]);
        Product::factory()->create(['name' => 'Apple', 'price' => 100, 'is_active' => true]);

        $response = $this->getJson('/api/v1/products?sort=price&direction=asc');

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Apple');
    }

    public function test_products_are_paginated(): void
    {
        Product::factory(15)->create(['is_active' => true]);

        $response = $this->getJson('/api/v1/products?per_page=5');

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 15);
    }

    public function test_can_show_product_by_slug(): void
    {
        $product = Product::factory()->create([
            'slug' => 'test-product',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/products/test-product');

        $response->assertOk()
            ->assertJsonPath('data.slug', 'test-product');
    }

    public function test_cannot_show_inactive_product(): void
    {
        Product::factory()->create([
            'slug' => 'hidden',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/products/hidden');

        $response->assertNotFound();
    }

    public function test_show_returns_404_for_nonexistent_slug(): void
    {
        $response = $this->getJson('/api/v1/products/nonexistent');

        $response->assertNotFound();
    }

    public function test_response_includes_price_fields(): void
    {
        Product::factory()->create(['is_active' => true]);

        $response = $this->getJson('/api/v1/products');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'name', 'price', 'effective_price', 'is_on_sale']],
            ]);
    }
}
