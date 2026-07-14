<?php

namespace Tests\Feature\Storefront;

use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistFeatureFlagTest extends TestCase
{
    use RefreshDatabase;

    public function test_wishlist_routes_return_403_when_feature_disabled(): void
    {
        Setting::setValue('features', 'feature_wishlist', 'false');

        $user = User::factory()->create();
        $product = Product::factory()->create(['is_active' => true, 'stock_quantity' => 5]);

        $this->actingAs($user)->getJson('/api/v1/wishlist')->assertForbidden();
        $this->actingAs($user)->postJson("/api/v1/wishlist/{$product->id}")->assertForbidden();

        $this->assertDatabaseCount('wishlists', 0);
    }

    public function test_wishlist_routes_work_when_feature_enabled(): void
    {
        Setting::setValue('features', 'feature_wishlist', 'true');

        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/v1/wishlist')->assertOk();
    }
}
