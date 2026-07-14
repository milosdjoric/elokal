<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookFeatureFlagTest extends TestCase
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

    public function test_webhook_routes_return_403_when_feature_disabled(): void
    {
        Setting::setValue('features', 'feature_webhooks', 'false');

        $this->getJson('/api/admin/webhooks', $this->authHeaders())
            ->assertForbidden();

        $this->postJson('/api/admin/webhooks', [
            'name' => 'Test',
            'url' => 'https://example.com/hook',
            'events' => ['order.created'],
        ], $this->authHeaders())
            ->assertForbidden();

        $this->assertDatabaseCount('webhooks', 0);
    }

    public function test_webhook_routes_work_when_feature_enabled(): void
    {
        Setting::setValue('features', 'feature_webhooks', 'true');

        $this->getJson('/api/admin/webhooks', $this->authHeaders())
            ->assertOk();
    }
}
