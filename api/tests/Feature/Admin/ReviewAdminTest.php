<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewAdminTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
        $this->token = $this->admin->createToken('test')->plainTextToken;
    }

    private function authHeaders(): array
    {
        return ['Authorization' => "Bearer {$this->token}", 'Accept' => 'application/json'];
    }

    public function test_can_list_all_reviews(): void
    {
        Review::factory()->count(3)->create();

        $response = $this->getJson('/api/admin/reviews', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_filter_reviews_by_status(): void
    {
        Review::factory()->create(['status' => 'pending']);
        Review::factory()->approved()->create();

        $response = $this->getJson('/api/admin/reviews?status=pending', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_reviews_by_rating(): void
    {
        Review::factory()->create(['rating' => 5]);
        Review::factory()->create(['rating' => 1]);

        $response = $this->getJson('/api/admin/reviews?rating=5', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_reviews_by_product(): void
    {
        $product = Product::factory()->create();
        Review::factory()->create(['product_id' => $product->id]);
        Review::factory()->create();

        $response = $this->getJson("/api/admin/reviews?product_id={$product->id}", $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_approve_review(): void
    {
        $review = Review::factory()->create(['status' => 'pending']);

        $response = $this->patchJson("/api/admin/reviews/{$review->id}/approve", [], $this->authHeaders());

        $response->assertOk();
        $this->assertEquals('approved', $review->fresh()->status);
    }

    public function test_can_reject_review(): void
    {
        $review = Review::factory()->create(['status' => 'pending']);

        $response = $this->patchJson("/api/admin/reviews/{$review->id}/reject", [], $this->authHeaders());

        $response->assertOk();
        $this->assertEquals('rejected', $review->fresh()->status);
    }

    public function test_can_reply_to_review(): void
    {
        $review = Review::factory()->approved()->create();

        $response = $this->postJson("/api/admin/reviews/{$review->id}/reply", [
            'admin_reply' => 'Hvala na recenziji!',
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.admin_reply', 'Hvala na recenziji!');

        $this->assertNotNull($review->fresh()->admin_replied_at);
    }

    public function test_unauthenticated_cannot_access_admin_reviews(): void
    {
        $this->getJson('/api/admin/reviews')
            ->assertUnauthorized();
    }
}
