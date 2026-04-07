<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(Admin::factory()->superAdmin()->create(), ['*']);
    }

    public function test_can_list_products(): void
    {
        Product::factory(3)->create();

        $response = $this->getJson('/api/admin/products');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_products_are_paginated(): void
    {
        Product::factory(20)->create();

        $response = $this->getJson('/api/admin/products?per_page=5');

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 20);
    }

    public function test_can_filter_by_status(): void
    {
        Product::factory(3)->create(['is_active' => true]);
        Product::factory(2)->create(['is_active' => false]);

        $response = $this->getJson('/api/admin/products?status=active');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_filter_by_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create();
        $product->categories()->attach($category);
        Product::factory(2)->create();

        $response = $this->getJson("/api/admin/products?category={$category->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_by_featured(): void
    {
        Product::factory(2)->create(['featured' => true]);
        Product::factory(3)->create(['featured' => false]);

        $response = $this->getJson('/api/admin/products?featured=1');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_search_products(): void
    {
        Product::factory()->create(['name' => 'Organic Honey']);
        Product::factory()->create(['name' => 'Fresh Milk']);

        $response = $this->getJson('/api/admin/products?search=Honey');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Organic Honey');
    }

    public function test_can_sort_products(): void
    {
        Product::factory()->create(['name' => 'Banana', 'price' => 200]);
        Product::factory()->create(['name' => 'Apple', 'price' => 100]);

        $response = $this->getJson('/api/admin/products?sort=name&direction=asc');

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Apple')
            ->assertJsonPath('data.1.name', 'Banana');
    }

    public function test_can_create_product(): void
    {
        $category = Category::factory()->create();

        $response = $this->postJson('/api/admin/products', [
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 1500.00,
            'stock_quantity' => 10,
            'is_active' => true,
            'categories' => [$category->id],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Test Product')
            ->assertJsonPath('data.price', '1500.00');

        $this->assertDatabaseHas('products', ['slug' => 'test-product']);
        $this->assertDatabaseHas('category_product', [
            'category_id' => $category->id,
        ]);
    }

    public function test_create_product_validates_required_fields(): void
    {
        $response = $this->postJson('/api/admin/products', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'slug', 'price']);
    }

    public function test_create_product_validates_unique_slug(): void
    {
        Product::factory()->create(['slug' => 'taken-slug']);

        $response = $this->postJson('/api/admin/products', [
            'name' => 'Test',
            'slug' => 'taken-slug',
            'price' => 100,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('slug');
    }

    public function test_can_show_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/admin/products/{$product->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $product->id);
    }

    public function test_can_update_product(): void
    {
        $product = Product::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson("/api/admin/products/{$product->id}", [
            'name' => 'New Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Name');
    }

    public function test_update_syncs_categories(): void
    {
        $product = Product::factory()->create();
        $category = Category::factory()->create();

        $this->putJson("/api/admin/products/{$product->id}", [
            'categories' => [$category->id],
        ]);

        $this->assertTrue($product->fresh()->categories->contains($category));
    }

    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/admin/products/{$product->id}");

        $response->assertOk();
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_unauthenticated_user_cannot_access_products(): void
    {
        // Reset auth
        $this->app['auth']->forgetGuards();

        $response = $this->getJson('/api/admin/products');

        $response->assertUnauthorized();
    }
}
