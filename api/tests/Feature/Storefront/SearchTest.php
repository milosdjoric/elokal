<?php

namespace Tests\Feature\Storefront;

use App\Models\Product;
use App\Models\SearchLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_search_products(): void
    {
        Product::factory()->create(['name' => 'Organic Honey', 'is_active' => true]);
        Product::factory()->create(['name' => 'Fresh Milk', 'is_active' => true]);

        $response = $this->getJson('/api/v1/search?q=Honey');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_search_includes_description(): void
    {
        Product::factory()->create([
            'name' => 'Product A',
            'description' => 'Contains organic honey',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/search?q=honey');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_search_includes_sku(): void
    {
        Product::factory()->create([
            'name' => 'Product A',
            'sku' => 'HON-001',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/search?q=HON-001');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_search_excludes_inactive_products(): void
    {
        Product::factory()->create(['name' => 'Honey Active', 'is_active' => true]);
        Product::factory()->create(['name' => 'Honey Hidden', 'is_active' => false]);

        $response = $this->getJson('/api/v1/search?q=Honey');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_search_logs_query(): void
    {
        Product::factory()->create(['name' => 'Honey', 'is_active' => true]);

        $this->getJson('/api/v1/search?q=Honey');

        $this->assertDatabaseHas('search_logs', [
            'query' => 'Honey',
            'results_count' => 1,
        ]);
    }

    public function test_search_logs_zero_results(): void
    {
        $this->getJson('/api/v1/search?q=nonexistent');

        $this->assertDatabaseHas('search_logs', [
            'query' => 'nonexistent',
            'results_count' => 0,
        ]);
    }

    public function test_search_requires_query_param(): void
    {
        $response = $this->getJson('/api/v1/search');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('q');
    }

    public function test_search_requires_minimum_length(): void
    {
        $response = $this->getJson('/api/v1/search?q=a');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('q');
    }

    public function test_search_returns_empty_for_no_results(): void
    {
        $response = $this->getJson('/api/v1/search?q=xyz123');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
