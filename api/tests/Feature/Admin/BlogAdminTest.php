<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\BlogCategory;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogAdminTest extends TestCase
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

    // --- Posts ---

    public function test_can_list_posts(): void
    {
        Post::factory()->count(3)->create();

        $this->getJson('/api/admin/posts', $this->authHeaders())
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_post(): void
    {
        $response = $this->postJson('/api/admin/posts', [
            'title' => 'Novi post',
            'content' => 'Sadržaj posta.',
            'status' => 'draft',
        ], $this->authHeaders());

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Novi post')
            ->assertJsonPath('data.slug', 'novi-post');
    }

    public function test_create_published_sets_published_at(): void
    {
        $response = $this->postJson('/api/admin/posts', [
            'title' => 'Published post',
            'content' => 'Content.',
            'status' => 'published',
        ], $this->authHeaders());

        $response->assertCreated();
        $this->assertNotNull($response->json('data.published_at'));
    }

    public function test_can_create_with_categories_and_tags(): void
    {
        $category = BlogCategory::create(['name' => 'Tech', 'slug' => 'tech']);
        $tag = Tag::create(['name' => 'Laravel', 'slug' => 'laravel']);

        $response = $this->postJson('/api/admin/posts', [
            'title' => 'Test',
            'content' => 'Content here.',
            'status' => 'draft',
            'category_ids' => [$category->id],
            'tag_ids' => [$tag->id],
        ], $this->authHeaders());

        $response->assertCreated();
        $post = Post::first();
        $this->assertCount(1, $post->categories);
        $this->assertCount(1, $post->tags);
    }

    public function test_can_update_post(): void
    {
        $post = Post::factory()->create();

        $this->putJson("/api/admin/posts/{$post->id}", [
            'title' => 'Updated Title',
        ], $this->authHeaders())
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated Title');
    }

    public function test_can_delete_post(): void
    {
        $post = Post::factory()->create();

        $this->deleteJson("/api/admin/posts/{$post->id}", [], $this->authHeaders())
            ->assertOk();

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_can_filter_by_status(): void
    {
        Post::factory()->create(['status' => 'draft']);
        Post::factory()->published()->create();

        $this->getJson('/api/admin/posts?status=draft', $this->authHeaders())
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    // --- Categories ---

    public function test_can_list_categories(): void
    {
        BlogCategory::create(['name' => 'Cat', 'slug' => 'cat']);

        $this->getJson('/api/admin/blog-categories', $this->authHeaders())
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_create_category(): void
    {
        $this->postJson('/api/admin/blog-categories', [
            'name' => 'New Cat',
        ], $this->authHeaders())
            ->assertCreated()
            ->assertJsonPath('data.slug', 'new-cat');
    }

    public function test_can_delete_category(): void
    {
        $cat = BlogCategory::create(['name' => 'Del', 'slug' => 'del']);

        $this->deleteJson("/api/admin/blog-categories/{$cat->id}", [], $this->authHeaders())
            ->assertOk();
    }

    // --- Tags ---

    public function test_can_list_tags(): void
    {
        Tag::create(['name' => 'Tag1', 'slug' => 'tag1']);

        $this->getJson('/api/admin/tags', $this->authHeaders())
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_create_tag(): void
    {
        $this->postJson('/api/admin/tags', [
            'name' => 'New Tag',
        ], $this->authHeaders())
            ->assertCreated()
            ->assertJsonPath('data.slug', 'new-tag');
    }

    public function test_unauthenticated_cannot_access(): void
    {
        $this->getJson('/api/admin/posts')->assertUnauthorized();
    }
}
