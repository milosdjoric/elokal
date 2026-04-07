<?php

namespace Tests\Feature\Storefront;

use App\Models\BlogCategory;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_published_posts(): void
    {
        Post::factory()->published()->count(3)->create();
        Post::factory()->create(); // draft

        $response = $this->getJson('/api/v1/blog');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_show_post_by_slug(): void
    {
        $post = Post::factory()->published()->create(['title' => 'Test Post']);

        $response = $this->getJson("/api/v1/blog/{$post->slug}");

        $response->assertOk()
            ->assertJsonPath('data.title', 'Test Post')
            ->assertJsonStructure(['data', 'related']);
    }

    public function test_draft_posts_not_visible(): void
    {
        $post = Post::factory()->create(['status' => 'draft']);

        $this->getJson("/api/v1/blog/{$post->slug}")
            ->assertNotFound();
    }

    public function test_scheduled_future_posts_not_visible(): void
    {
        $post = Post::factory()->scheduled()->create();

        $this->getJson("/api/v1/blog/{$post->slug}")
            ->assertNotFound();
    }

    public function test_can_filter_by_category(): void
    {
        $category = BlogCategory::create(['name' => 'Tech', 'slug' => 'tech']);
        $post = Post::factory()->published()->create();
        $post->categories()->attach($category);

        Post::factory()->published()->create(); // uncategorized

        $response = $this->getJson('/api/v1/blog/category/tech');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('category.name', 'Tech');
    }

    public function test_can_filter_by_tag(): void
    {
        $tag = Tag::create(['name' => 'Laravel', 'slug' => 'laravel']);
        $post = Post::factory()->published()->create();
        $post->tags()->attach($tag);

        $response = $this->getJson('/api/v1/blog/tag/laravel');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('tag.name', 'Laravel');
    }

    public function test_sidebar_returns_data(): void
    {
        Post::factory()->published()->count(2)->create();
        BlogCategory::create(['name' => 'Cat', 'slug' => 'cat']);
        Tag::create(['name' => 'Tag1', 'slug' => 'tag1']);

        $response = $this->getJson('/api/v1/blog/sidebar');

        $response->assertOk()
            ->assertJsonStructure(['recent_posts', 'categories', 'tags']);
    }

    public function test_can_search_posts(): void
    {
        Post::factory()->published()->create(['title' => 'Laravel Tips']);
        Post::factory()->published()->create(['title' => 'Vue Guide']);

        $response = $this->getJson('/api/v1/blog?search=laravel');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
