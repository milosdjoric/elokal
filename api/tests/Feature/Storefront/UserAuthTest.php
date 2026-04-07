<?php

namespace Tests\Feature\Storefront;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAuthTest extends TestCase
{
    use RefreshDatabase;

    // --- Register ---

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Marko Marković',
            'email' => 'marko@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+381641234567',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['token', 'user'])
            ->assertJsonPath('user.name', 'Marko Marković');

        $this->assertDatabaseHas('users', ['email' => 'marko@test.com']);
    }

    public function test_register_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/register', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_register_validates_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@test.com']);

        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test',
            'email' => 'taken@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_register_validates_password_confirmation(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test',
            'email' => 'test@test.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }

    // --- Login ---

    public function test_user_can_login(): void
    {
        User::factory()->create([
            'email' => 'user@test.com',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'user@test.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'user@test.com']);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'user@test.com',
            'password' => 'wrong',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_login_is_rate_limited(): void
    {
        User::factory()->create(['email' => 'user@test.com']);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/login', [
                'email' => 'user@test.com',
                'password' => 'wrong',
            ]);
        }

        $response = $this->postJson('/api/v1/login', [
            'email' => 'user@test.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(429);
    }

    // --- Logout ---

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/logout');

        $response->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    // --- Me ---

    public function test_user_can_get_profile(): void
    {
        $user = User::factory()->create(['name' => 'Ana']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/me');

        $response->assertOk()
            ->assertJsonPath('name', 'Ana');
    }

    public function test_unauthenticated_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/v1/me');
        $response->assertUnauthorized();
    }

    // --- Update profile ---

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/me', ['name' => 'Novo Ime']);

        $response->assertOk()
            ->assertJsonPath('name', 'Novo Ime');
    }

    // --- Update password ---

    public function test_user_can_update_password(): void
    {
        $user = User::factory()->create(['password' => 'oldpassword']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/me/password', [
                'current_password' => 'oldpassword',
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ]);

        $response->assertOk();
    }

    public function test_update_password_fails_with_wrong_current(): void
    {
        $user = User::factory()->create(['password' => 'oldpassword']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/me/password', [
                'current_password' => 'wrong',
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('current_password');
    }

    // --- Delete account ---

    public function test_user_can_delete_account(): void
    {
        $user = User::factory()->create(['password' => 'password']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson('/api/v1/me', ['password' => 'password']);

        $response->assertOk();
        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertEquals('Obrisani korisnik', $user->fresh()->name);
    }

    public function test_delete_account_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => 'password']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson('/api/v1/me', ['password' => 'wrong']);

        $response->assertUnprocessable();
    }
}
