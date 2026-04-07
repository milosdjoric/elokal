<?php

namespace Tests\Feature\Storefront;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CallbackRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_callback_request(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson('/api/v1/callback-request', [
            'product_id' => $product->id,
            'name' => 'Marko',
            'phone' => '+381641234567',
            'channel' => 'call',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('callback_requests', [
            'product_id' => $product->id,
            'name' => 'Marko',
            'channel' => 'call',
            'status' => 'pending',
        ]);
    }

    public function test_can_create_without_product(): void
    {
        $response = $this->postJson('/api/v1/callback-request', [
            'name' => 'Ana',
            'phone' => '+381621234567',
            'channel' => 'whatsapp',
            'message' => 'Pitanje o dostavi.',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('callback_requests', [
            'product_id' => null,
            'name' => 'Ana',
            'channel' => 'whatsapp',
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $this->postJson('/api/v1/callback-request', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'phone', 'channel']);
    }

    public function test_validates_channel(): void
    {
        $this->postJson('/api/v1/callback-request', [
            'name' => 'Test',
            'phone' => '123',
            'channel' => 'telegram',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['channel']);
    }
}
