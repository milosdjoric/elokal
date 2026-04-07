<?php

namespace Tests\Feature\Storefront;

use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_subscribe(): void
    {
        $response = $this->postJson('/api/v1/newsletter/subscribe', [
            'email' => 'test@test.com',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'test@test.com',
            'status' => 'pending',
            'source' => 'footer',
        ]);
    }

    public function test_subscribe_with_source(): void
    {
        $this->postJson('/api/v1/newsletter/subscribe', [
            'email' => 'test@test.com',
            'source' => 'popup',
        ]);

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'test@test.com',
            'source' => 'popup',
        ]);
    }

    public function test_duplicate_confirmed_returns_message(): void
    {
        NewsletterSubscriber::create([
            'email' => 'test@test.com',
            'token' => 'abc123',
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/newsletter/subscribe', [
            'email' => 'test@test.com',
        ]);

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Već ste prijavljeni na newsletter.']);
    }

    public function test_resubscribe_after_unsubscribe(): void
    {
        $sub = NewsletterSubscriber::create([
            'email' => 'test@test.com',
            'token' => 'old-token',
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        $this->postJson('/api/v1/newsletter/subscribe', ['email' => 'test@test.com']);

        $sub->refresh();
        $this->assertEquals('pending', $sub->status);
        $this->assertNull($sub->unsubscribed_at);
        $this->assertNotEquals('old-token', $sub->token);
    }

    public function test_can_confirm(): void
    {
        $sub = NewsletterSubscriber::create([
            'email' => 'test@test.com',
            'token' => 'confirm-token',
            'status' => 'pending',
        ]);

        $response = $this->getJson('/api/v1/newsletter/confirm/confirm-token');

        $response->assertOk();
        $this->assertEquals('confirmed', $sub->fresh()->status);
        $this->assertNotNull($sub->fresh()->confirmed_at);
    }

    public function test_confirm_invalid_token(): void
    {
        $this->getJson('/api/v1/newsletter/confirm/invalid-token')
            ->assertNotFound();
    }

    public function test_can_unsubscribe(): void
    {
        $sub = NewsletterSubscriber::create([
            'email' => 'test@test.com',
            'token' => 'unsub-token',
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/newsletter/unsubscribe/unsub-token');

        $response->assertOk();
        $this->assertEquals('unsubscribed', $sub->fresh()->status);
        $this->assertNotNull($sub->fresh()->unsubscribed_at);
    }

    public function test_subscribe_validates_email(): void
    {
        $this->postJson('/api/v1/newsletter/subscribe', ['email' => 'not-an-email'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}
