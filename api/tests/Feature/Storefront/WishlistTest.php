<?php

namespace Tests\Feature\Storefront;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    private function authHeaders(): array
    {
        return ['Authorization' => "Bearer {$this->token}", 'Accept' => 'application/json'];
    }

    public function test_can_list_wishlist(): void
    {
        $product = Product::factory()->create(['is_active' => true]);
        Wishlist::create(['user_id' => $this->user->id, 'product_id' => $product->id]);

        $response = $this->getJson('/api/v1/wishlist', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_inactive_products_hidden_from_wishlist(): void
    {
        $product = Product::factory()->create(['is_active' => false]);
        Wishlist::create(['user_id' => $this->user->id, 'product_id' => $product->id]);

        $response = $this->getJson('/api/v1/wishlist', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_can_add_to_wishlist(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson("/api/v1/wishlist/{$product->id}", [], $this->authHeaders());

        $response->assertCreated();
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $this->user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_adding_duplicate_does_not_fail(): void
    {
        $product = Product::factory()->create();
        Wishlist::create(['user_id' => $this->user->id, 'product_id' => $product->id]);

        $response = $this->postJson("/api/v1/wishlist/{$product->id}", [], $this->authHeaders());

        $response->assertCreated();
        $this->assertEquals(1, Wishlist::where('user_id', $this->user->id)->where('product_id', $product->id)->count());
    }

    public function test_can_remove_from_wishlist(): void
    {
        $product = Product::factory()->create();
        Wishlist::create(['user_id' => $this->user->id, 'product_id' => $product->id]);

        $response = $this->deleteJson("/api/v1/wishlist/{$product->id}", [], $this->authHeaders());

        $response->assertOk();
        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $this->user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_can_get_wishlist_ids(): void
    {
        $p1 = Product::factory()->create();
        $p2 = Product::factory()->create();
        Wishlist::create(['user_id' => $this->user->id, 'product_id' => $p1->id]);
        Wishlist::create(['user_id' => $this->user->id, 'product_id' => $p2->id]);

        $response = $this->getJson('/api/v1/wishlist/ids', $this->authHeaders());

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function test_can_sync_wishlist(): void
    {
        $p1 = Product::factory()->create();
        $p2 = Product::factory()->create();

        $response = $this->postJson('/api/v1/wishlist/sync', [
            'product_ids' => [$p1->id, $p2->id],
        ], $this->authHeaders());

        $response->assertOk();
        $this->assertEquals(2, $this->user->wishlists()->count());
    }

    public function test_unauthenticated_cannot_access_wishlist(): void
    {
        $this->getJson('/api/v1/wishlist')->assertUnauthorized();
    }
}
