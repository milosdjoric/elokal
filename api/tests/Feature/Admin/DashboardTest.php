<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(Admin::factory()->superAdmin()->create(), ['*']);
    }

    public function test_dashboard_returns_stats(): void
    {
        Product::factory(5)->create(['is_active' => true, 'featured' => false]);
        Product::factory(2)->create(['is_active' => false, 'featured' => false]);
        Product::factory(3)->create(['featured' => true, 'is_active' => true]);
        Product::factory()->create(['stock_quantity' => 0, 'is_active' => true, 'featured' => false]);
        Category::factory(4)->create();

        $response = $this->getJson('/api/admin/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'total_products',
                'active_products',
                'total_categories',
                'featured_products',
                'out_of_stock',
            ]);

        $data = $response->json();
        $this->assertEquals(11, $data['total_products']);
        $this->assertEquals(9, $data['active_products']);
        $this->assertEquals(4, $data['total_categories']);
        $this->assertEquals(3, $data['featured_products']);
        $this->assertEquals(1, $data['out_of_stock']);
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $this->app['auth']->forgetGuards();

        $response = $this->getJson('/api/admin/dashboard');

        $response->assertUnauthorized();
    }
}
