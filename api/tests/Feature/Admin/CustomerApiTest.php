<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerApiTest extends TestCase
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

    public function test_can_list_customers(): void
    {
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/admin/customers', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_customers_include_order_stats(): void
    {
        $user = User::factory()->create();
        Order::create([
            'order_number' => 'EL-260407-TEST',
            'user_id' => $user->id,
            'status' => 'completed',
            'email' => $user->email,
            'shipping_first_name' => 'Test',
            'shipping_last_name' => 'User',
            'shipping_address_line_1' => 'Test 1',
            'shipping_city' => 'Beograd',
            'shipping_postal_code' => '11000',
            'shipping_country' => 'RS',
            'subtotal' => 2500,
            'total' => 2500,
        ]);

        $response = $this->getJson('/api/admin/customers', $this->authHeaders());

        $response->assertOk();
        $customer = $response->json('data.0');
        $this->assertEquals(1, $customer['orders_count']);
        $this->assertEquals('2500.00', $customer['total_spent']);
    }

    public function test_can_search_customers(): void
    {
        User::factory()->create(['name' => 'Marko Marković', 'email' => 'marko@test.com']);
        User::factory()->create(['name' => 'Petar Petrović', 'email' => 'petar@test.com']);

        $response = $this->getJson('/api/admin/customers?search=marko', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Marko Marković');
    }

    public function test_can_show_customer_with_orders(): void
    {
        $user = User::factory()->create();
        Order::create([
            'order_number' => 'EL-260407-SHOW',
            'user_id' => $user->id,
            'status' => 'pending',
            'email' => $user->email,
            'shipping_first_name' => 'Test',
            'shipping_last_name' => 'User',
            'shipping_address_line_1' => 'Test 1',
            'shipping_city' => 'Beograd',
            'shipping_postal_code' => '11000',
            'shipping_country' => 'RS',
            'subtotal' => 1000,
            'total' => 1000,
        ]);

        $response = $this->getJson("/api/admin/customers/{$user->id}", $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.name', $user->name)
            ->assertJsonCount(1, 'data.orders');
    }

    public function test_unauthenticated_cannot_list_customers(): void
    {
        $this->getJson('/api/admin/customers')
            ->assertUnauthorized();
    }
}
