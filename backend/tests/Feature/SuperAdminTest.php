<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\RestaurantSetting;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SubscriptionPlanSeeder::class);

        $this->superAdmin = User::factory()->create([
            'email' => 'admin@test.com',
        ]);
        $this->superAdmin->assignRole('super-admin');
        $this->token = $this->superAdmin->createToken('test')->plainTextToken;
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    public function test_can_view_dashboard(): void
    {
        $response = $this->getJson('/api/super-admin/dashboard', $this->headers());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'metrics' => [
                    'total_restaurants', 'active_restaurants', 'suspended_restaurants',
                    'total_menu_views', 'monthly_revenue', 'total_products', 'total_categories',
                ],
                'charts' => ['registrations_growth', 'views_growth'],
            ]);
    }

    public function test_can_list_restaurants(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('restaurant-admin');
        $restaurant = Restaurant::create(['user_id' => $owner->id, 'slug' => 'test-place', 'status' => 'active']);
        RestaurantSetting::create(['restaurant_id' => $restaurant->id, 'name_ar' => 'ar', 'name_en' => 'en']);

        $response = $this->getJson('/api/super-admin/restaurants', $this->headers());

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_create_restaurant(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('restaurant-admin');
        $plan = SubscriptionPlan::first();

        $response = $this->postJson('/api/super-admin/restaurants', [
            'owner_id' => $owner->id,
            'slug' => 'new-restaurant',
            'name_ar' => 'مطعم جديد',
            'name_en' => 'New Restaurant',
            'plan_id' => $plan->id,
        ], $this->headers());

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'slug']]);

        $this->assertDatabaseHas('restaurants', ['slug' => 'new-restaurant']);
    }

    public function test_can_view_restaurant(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('restaurant-admin');
        $restaurant = Restaurant::create(['user_id' => $owner->id, 'slug' => 'view-test', 'status' => 'active']);
        RestaurantSetting::create(['restaurant_id' => $restaurant->id, 'name_ar' => 'ar', 'name_en' => 'en']);

        $response = $this->getJson("/api/super-admin/restaurants/{$restaurant->id}", $this->headers());

        $response->assertStatus(200)
            ->assertJsonPath('data.slug', 'view-test');
    }

    public function test_can_update_restaurant_status(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('restaurant-admin');
        $restaurant = Restaurant::create(['user_id' => $owner->id, 'slug' => 'status-test', 'status' => 'active']);

        $response = $this->postJson("/api/super-admin/restaurants/{$restaurant->id}/status", [
            'status' => 'suspended',
        ], $this->headers());

        $response->assertStatus(200);
        $this->assertDatabaseHas('restaurants', ['id' => $restaurant->id, 'status' => 'suspended']);
    }

    public function test_can_delete_restaurant(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('restaurant-admin');
        $restaurant = Restaurant::create(['user_id' => $owner->id, 'slug' => 'delete-test', 'status' => 'active']);

        $response = $this->deleteJson("/api/super-admin/restaurants/{$restaurant->id}", [], $this->headers());

        $response->assertStatus(200);
        $this->assertModelMissing($restaurant);
    }

    public function test_can_list_subscription_plans(): void
    {
        $response = $this->getJson('/api/super-admin/plans', $this->headers());

        $response->assertStatus(200)
            ->assertJsonCount(4, 'data');
    }

    public function test_can_create_subscription_plan(): void
    {
        $response = $this->postJson('/api/super-admin/plans', [
            'name' => 'Test Plan',
            'price' => 99.99,
            'duration_days' => 30,
            'features' => ['a', 'b'],
            'limit_categories' => 50,
            'limit_products' => 500,
        ], $this->headers());

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Test Plan');
    }

    public function test_cannot_delete_plan_with_active_subscriptions(): void
    {
        $plan = SubscriptionPlan::first();
        $owner = User::factory()->create();
        $owner->assignRole('restaurant-admin');
        $restaurant = Restaurant::create(['user_id' => $owner->id, 'slug' => 'active-sub', 'status' => 'active']);
        Subscription::create([
            'restaurant_id' => $restaurant->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);

        $response = $this->deleteJson("/api/super-admin/plans/{$plan->id}", [], $this->headers());

        $response->assertStatus(422);
    }

    public function test_can_list_users(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('restaurant-admin');

        $response = $this->getJson('/api/super-admin/users', $this->headers());

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_reset_user_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('oldpass')]);
        $user->assignRole('restaurant-admin');

        $response = $this->postJson("/api/super-admin/users/{$user->id}/reset-password", [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ], $this->headers());

        $response->assertStatus(200)
            ->assertJson(['message' => 'Password reset successfully']);
    }

    public function test_can_toggle_user_status(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('restaurant-admin');
        $restaurant = Restaurant::create(['user_id' => $owner->id, 'slug' => 'toggle-test', 'status' => 'active']);

        $response = $this->postJson("/api/super-admin/users/{$owner->id}/status", [], $this->headers());

        $response->assertStatus(200);
        $this->assertDatabaseHas('restaurants', ['id' => $restaurant->id, 'status' => 'suspended']);
    }

    public function test_can_view_system_settings(): void
    {
        $response = $this->getJson('/api/super-admin/settings', $this->headers());

        $response->assertStatus(200);
    }

    public function test_can_update_system_settings(): void
    {
        $response = $this->postJson('/api/super-admin/settings', [
            'platform_name' => 'My Platform',
            'storage_driver' => 'local',
            'maintenance_mode' => false,
        ], $this->headers());

        $response->assertStatus(200)
            ->assertJsonPath('settings.platform_name', 'My Platform');
    }

    public function test_can_view_activity_logs(): void
    {
        $response = $this->getJson('/api/super-admin/logs', $this->headers());

        $response->assertStatus(200);
    }

    public function test_super_admin_cannot_access_restaurant_admin_routes(): void
    {
        $response = $this->getJson('/api/restaurant-admin/dashboard', $this->headers());

        $response->assertStatus(403);
    }
}
