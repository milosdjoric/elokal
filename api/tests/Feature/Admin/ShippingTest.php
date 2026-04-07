<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingTest extends TestCase
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

    public function test_can_create_zone(): void
    {
        $response = $this->postJson('/api/admin/shipping-zones', [
            'name' => 'Srbija',
            'countries' => ['RS'],
        ], $this->authHeaders());

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Srbija');
    }

    public function test_can_list_zones_with_methods(): void
    {
        $zone = ShippingZone::create(['name' => 'RS', 'countries' => ['RS']]);
        $zone->methods()->create(['name' => 'Standard', 'type' => 'flat', 'cost' => 300]);

        $response = $this->getJson('/api/admin/shipping-zones', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.methods');
    }

    public function test_can_create_method(): void
    {
        $zone = ShippingZone::create(['name' => 'RS', 'countries' => ['RS']]);

        $response = $this->postJson("/api/admin/shipping-zones/{$zone->id}/methods", [
            'name' => 'Express',
            'type' => 'flat',
            'cost' => 500,
            'estimated_days' => '1-2',
        ], $this->authHeaders());

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Express');
    }

    public function test_can_delete_zone(): void
    {
        $zone = ShippingZone::create(['name' => 'Del', 'countries' => ['RS']]);

        $this->deleteJson("/api/admin/shipping-zones/{$zone->id}", [], $this->authHeaders())
            ->assertOk();
    }

    // --- Public ---

    public function test_public_can_get_shipping_methods(): void
    {
        $zone = ShippingZone::create(['name' => 'RS', 'countries' => ['RS'], 'is_active' => true]);
        $zone->methods()->create(['name' => 'Standard', 'type' => 'flat', 'cost' => 300, 'is_active' => true, 'estimated_days' => '2-4']);
        $zone->methods()->create(['name' => 'Free', 'type' => 'free', 'is_active' => true]);

        $response = $this->postJson('/api/v1/shipping/methods', [
            'country' => 'RS',
            'subtotal' => 1000,
        ]);

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.cost', '300.00')
            ->assertJsonPath('data.1.cost', '0.00');
    }

    public function test_free_above_threshold(): void
    {
        $zone = ShippingZone::create(['name' => 'RS', 'countries' => ['RS'], 'is_active' => true]);
        $zone->methods()->create(['name' => 'Standard', 'type' => 'flat', 'cost' => 300, 'free_above' => 5000, 'is_active' => true]);

        $under = $this->postJson('/api/v1/shipping/methods', ['country' => 'RS', 'subtotal' => 3000]);
        $under->assertJsonPath('data.0.cost', '300.00');

        $over = $this->postJson('/api/v1/shipping/methods', ['country' => 'RS', 'subtotal' => 6000]);
        $over->assertJsonPath('data.0.cost', '0.00');
    }

    public function test_no_methods_for_unknown_country(): void
    {
        ShippingZone::create(['name' => 'RS', 'countries' => ['RS'], 'is_active' => true]);

        $this->postJson('/api/v1/shipping/methods', ['country' => 'DE', 'subtotal' => 1000])
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
