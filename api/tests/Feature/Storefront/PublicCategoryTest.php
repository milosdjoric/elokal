<?php

namespace Tests\Feature\Storefront;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_categories_as_tree(): void
    {
        $parent = Category::factory()->create(['is_active' => true]);
        Category::factory(2)->create(['parent_id' => $parent->id, 'is_active' => true]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(2, 'data.0.children');
    }

    public function test_inactive_categories_are_hidden(): void
    {
        Category::factory()->create(['is_active' => false]);
        Category::factory()->create(['is_active' => true]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_inactive_children_are_hidden(): void
    {
        $parent = Category::factory()->create(['is_active' => true]);
        Category::factory()->create(['parent_id' => $parent->id, 'is_active' => true]);
        Category::factory()->create(['parent_id' => $parent->id, 'is_active' => false]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonCount(1, 'data.0.children');
    }

    public function test_can_show_category_with_products(): void
    {
        $category = Category::factory()->create(['slug' => 'voce', 'is_active' => true]);
        $product = Product::factory()->create(['is_active' => true]);
        $product->categories()->attach($category);

        $response = $this->getJson('/api/v1/categories/voce');

        $response->assertOk()
            ->assertJsonPath('category.name', $category->name)
            ->assertJsonCount(1, 'data');
    }

    public function test_category_show_only_includes_active_products(): void
    {
        $category = Category::factory()->create(['slug' => 'test', 'is_active' => true]);
        $active = Product::factory()->create(['is_active' => true]);
        $inactive = Product::factory()->create(['is_active' => false]);
        $active->categories()->attach($category);
        $inactive->categories()->attach($category);

        $response = $this->getJson('/api/v1/categories/test');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_cannot_show_inactive_category(): void
    {
        Category::factory()->create(['slug' => 'hidden', 'is_active' => false]);

        $response = $this->getJson('/api/v1/categories/hidden');

        $response->assertNotFound();
    }

    public function test_show_returns_404_for_nonexistent_slug(): void
    {
        $response = $this->getJson('/api/v1/categories/nonexistent');

        $response->assertNotFound();
    }
}
