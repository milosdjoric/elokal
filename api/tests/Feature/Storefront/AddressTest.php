<?php

namespace Tests\Feature\Storefront;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressTest extends TestCase
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

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'label' => 'Kuća',
            'first_name' => 'Marko',
            'last_name' => 'Marković',
            'address_line_1' => 'Knez Mihailova 10',
            'city' => 'Beograd',
            'postal_code' => '11000',
            'country' => 'RS',
            'phone' => '+381641234567',
        ], $overrides);
    }

    public function test_can_list_addresses(): void
    {
        Address::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/addresses', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_unauthenticated_cannot_list_addresses(): void
    {
        $this->getJson('/api/v1/addresses')
            ->assertUnauthorized();
    }

    public function test_can_create_address(): void
    {
        $response = $this->postJson('/api/v1/addresses', $this->validData(), $this->authHeaders());

        $response->assertCreated()
            ->assertJsonFragment(['first_name' => 'Marko', 'city' => 'Beograd']);

        $this->assertDatabaseHas('addresses', [
            'user_id' => $this->user->id,
            'first_name' => 'Marko',
        ]);
    }

    public function test_first_address_is_automatically_default(): void
    {
        $this->postJson('/api/v1/addresses', $this->validData(), $this->authHeaders());

        $this->assertDatabaseHas('addresses', [
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);
    }

    public function test_setting_default_unsets_previous(): void
    {
        $first = Address::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);

        $this->postJson('/api/v1/addresses', $this->validData(['is_default' => true]), $this->authHeaders());

        $this->assertFalse($first->fresh()->is_default);
        $this->assertEquals(1, $this->user->addresses()->where('is_default', true)->count());
    }

    public function test_create_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/addresses', [], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name', 'last_name', 'address_line_1', 'city', 'postal_code']);
    }

    public function test_can_update_address(): void
    {
        $address = Address::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson(
            "/api/v1/addresses/{$address->id}",
            $this->validData(['first_name' => 'Petar']),
            $this->authHeaders(),
        );

        $response->assertOk()
            ->assertJsonFragment(['first_name' => 'Petar']);
    }

    public function test_cannot_update_other_users_address(): void
    {
        $other = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $other->id]);

        $this->putJson(
            "/api/v1/addresses/{$address->id}",
            $this->validData(),
            $this->authHeaders(),
        )->assertForbidden();
    }

    public function test_can_delete_address(): void
    {
        $address = Address::factory()->create(['user_id' => $this->user->id]);

        $this->deleteJson("/api/v1/addresses/{$address->id}", [], $this->authHeaders())
            ->assertOk()
            ->assertJsonFragment(['message' => 'Adresa obrisana.']);

        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_cannot_delete_other_users_address(): void
    {
        $other = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $other->id]);

        $this->deleteJson("/api/v1/addresses/{$address->id}", [], $this->authHeaders())
            ->assertForbidden();
    }

    public function test_deleting_default_promotes_next(): void
    {
        $default = Address::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);
        $second = Address::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => false,
        ]);

        $this->deleteJson("/api/v1/addresses/{$default->id}", [], $this->authHeaders());

        $this->assertTrue($second->fresh()->is_default);
    }

    public function test_default_addresses_listed_first(): void
    {
        Address::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => false,
            'first_name' => 'Drugi',
        ]);
        Address::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true,
            'first_name' => 'Podrazumevani',
        ]);

        $response = $this->getJson('/api/v1/addresses', $this->authHeaders());

        $data = $response->json('data');
        $this->assertEquals('Podrazumevani', $data[0]['first_name']);
    }
}
