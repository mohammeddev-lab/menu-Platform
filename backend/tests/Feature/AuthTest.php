<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\RestaurantSetting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test Owner',
            'email' => 'owner@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'restaurant_name_ar' => 'مطعم اختبار',
            'restaurant_name_en' => 'Test Restaurant',
            'restaurant_slug' => 'test-restaurant',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'role'],
                'token',
                'restaurant_slug',
            ]);

        $this->assertDatabaseHas('users', ['email' => 'owner@test.com']);
        $this->assertDatabaseHas('restaurants', ['slug' => 'test-restaurant']);
    }

    public function test_user_cannot_register_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@test.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test',
            'email' => 'existing@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'restaurant_name_ar' => 'مطعم',
            'restaurant_name_en' => 'Test',
            'restaurant_slug' => 'test-slug',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@test.com',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole('restaurant-admin');
        Restaurant::create(['user_id' => $user->id, 'slug' => 'test-restaurant', 'status' => 'active']);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'owner@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'role'],
                'token',
                'restaurant_slug',
            ]);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJson(['error' => 'Invalid credentials']);
    }

    public function test_suspended_restaurant_owner_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'suspended@test.com',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole('restaurant-admin');
        Restaurant::create(['user_id' => $user->id, 'slug' => 'suspended-place', 'status' => 'suspended']);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'suspended@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Your restaurant has been suspended. Please contact support.']);
    }

    public function test_authenticated_user_can_get_own_profile(): void
    {
        $user = User::factory()->create();
        $user->assignRole('restaurant-admin');
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure(['user' => ['id', 'name', 'email', 'role']]);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $user->assignRole('restaurant-admin');
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }
}
