<?php

namespace Tests\Feature\Storefront;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->product = Product::factory()->create(['is_active' => true]);
    }

    public function test_can_list_approved_reviews(): void
    {
        Review::factory()->approved()->count(3)->create(['product_id' => $this->product->id]);
        Review::factory()->create(['product_id' => $this->product->id]); // pending

        $response = $this->getJson("/api/v1/products/{$this->product->id}/reviews");

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['stats' => ['average_rating', 'total_reviews', 'distribution']]);
    }

    public function test_reviews_include_stats(): void
    {
        Review::factory()->approved()->create(['product_id' => $this->product->id, 'rating' => 5]);
        Review::factory()->approved()->create(['product_id' => $this->product->id, 'rating' => 3]);

        $response = $this->getJson("/api/v1/products/{$this->product->id}/reviews");

        $stats = $response->json('stats');
        $this->assertEquals(4.0, $stats['average_rating']);
        $this->assertEquals(2, $stats['total_reviews']);
        $this->assertEquals(1, $stats['distribution'][5]);
        $this->assertEquals(1, $stats['distribution'][3]);
    }

    public function test_authenticated_user_can_create_review(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/v1/products/{$this->product->id}/reviews", [
            'rating' => 4,
            'title' => 'Odlično!',
            'content' => 'Veoma zadovoljan proizvodom, preporučujem.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.rating', 4)
            ->assertJsonPath('data.title', 'Odlično!');

        $this->assertDatabaseHas('reviews', [
            'product_id' => $this->product->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    public function test_unauthenticated_cannot_create_review(): void
    {
        $this->postJson("/api/v1/products/{$this->product->id}/reviews", [
            'rating' => 5,
            'content' => 'Nešto nešto nešto.',
        ])->assertUnauthorized();
    }

    public function test_user_cannot_review_same_product_twice(): void
    {
        $user = User::factory()->create();
        Review::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->postJson("/api/v1/products/{$this->product->id}/reviews", [
            'rating' => 3,
            'content' => 'Druga recenzija za isti proizvod.',
        ]);

        $response->assertUnprocessable();
    }

    public function test_review_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson("/api/v1/products/{$this->product->id}/reviews", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['rating', 'content']);
    }

    public function test_review_validates_rating_range(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson("/api/v1/products/{$this->product->id}/reviews", [
            'rating' => 6,
            'content' => 'Nešto nešto nešto.',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['rating']);
    }

    public function test_can_vote_helpful(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->approved()->create(['product_id' => $this->product->id]);

        $response = $this->actingAs($user)->postJson("/api/v1/reviews/{$review->id}/helpful", [
            'helpful' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('helpful_count', 1)
            ->assertJsonPath('not_helpful_count', 0);
    }

    public function test_helpful_vote_is_unique_per_user(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->approved()->create(['product_id' => $this->product->id]);

        $this->actingAs($user)->postJson("/api/v1/reviews/{$review->id}/helpful", ['helpful' => true]);
        $this->actingAs($user)->postJson("/api/v1/reviews/{$review->id}/helpful", ['helpful' => false]);

        $this->assertEquals(0, $review->helpful()->where('helpful', true)->count());
        $this->assertEquals(1, $review->helpful()->where('helpful', false)->count());
    }

    public function test_reviews_can_be_sorted(): void
    {
        Review::factory()->approved()->create([
            'product_id' => $this->product->id,
            'rating' => 2,
            'created_at' => now()->subDay(),
        ]);
        Review::factory()->approved()->create([
            'product_id' => $this->product->id,
            'rating' => 5,
            'created_at' => now(),
        ]);

        $response = $this->getJson("/api/v1/products/{$this->product->id}/reviews?sort=highest");

        $data = $response->json('data');
        $this->assertEquals(5, $data[0]['rating']);
    }
}
