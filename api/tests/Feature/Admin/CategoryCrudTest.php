<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(Admin::factory()->superAdmin()->create(), ['*']);
    }

    public function test_can_list_categories_as_tree(): void
    {
        $parent = Category::factory()->create();
        Category::factory(2)->create(['parent_id' => $parent->id]);

        $response = $this->getJson('/api/admin/categories');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(2, 'data.0.children');
    }

    public function test_categories_include_products_count(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create();
        $product->categories()->attach($category);

        $response = $this->getJson('/api/admin/categories');

        $response->assertOk()
            ->assertJsonPath('data.0.products_count', 1);
    }

    public function test_can_create_category(): void
    {
        $response = $this->postJson('/api/admin/categories', [
            'name' => 'Nova Kategorija',
            'slug' => 'nova-kategorija',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Nova Kategorija');

        $this->assertDatabaseHas('categories', ['slug' => 'nova-kategorija']);
    }

    public function test_can_create_subcategory(): void
    {
        $parent = Category::factory()->create();

        $response = $this->postJson('/api/admin/categories', [
            'name' => 'Podkategorija',
            'slug' => 'podkategorija',
            'parent_id' => $parent->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.parent_id', $parent->id);
    }

    public function test_create_validates_required_fields(): void
    {
        $response = $this->postJson('/api/admin/categories', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'slug']);
    }

    public function test_create_validates_unique_slug(): void
    {
        Category::factory()->create(['slug' => 'taken']);

        $response = $this->postJson('/api/admin/categories', [
            'name' => 'Test',
            'slug' => 'taken',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('slug');
    }

    public function test_can_update_category(): void
    {
        $category = Category::factory()->create(['name' => 'Old']);

        $response = $this->putJson("/api/admin/categories/{$category->id}", [
            'name' => 'New',
            'slug' => $category->slug,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New');
    }

    public function test_update_allows_own_slug(): void
    {
        $category = Category::factory()->create(['slug' => 'my-slug']);

        $response = $this->putJson("/api/admin/categories/{$category->id}", [
            'name' => 'Updated',
            'slug' => 'my-slug',
        ]);

        $response->assertOk();
    }

    public function test_can_delete_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/admin/categories/{$category->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_deleting_parent_nullifies_children(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        $this->deleteJson("/api/admin/categories/{$parent->id}");

        $this->assertNull($child->fresh()->parent_id);
    }

    public function test_unauthenticated_user_cannot_access_categories(): void
    {
        $this->app['auth']->forgetGuards();

        $response = $this->getJson('/api/admin/categories');

        $response->assertUnauthorized();
    }
}
