<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\RestaurantSetting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicMenuTest extends TestCase
{
    use RefreshDatabase;

    private Restaurant $restaurant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('restaurant-admin');

        $this->restaurant = Restaurant::create([
            'user_id' => $user->id,
            'slug' => 'test-cafe',
            'status' => 'active',
        ]);
        RestaurantSetting::create([
            'restaurant_id' => $this->restaurant->id,
            'name_ar' => 'كافيه',
            'name_en' => 'Cafe',
        ]);

        $cat = Category::create(['restaurant_id' => $this->restaurant->id, 'name_ar' => 'مشروبات', 'name_en' => 'Drinks', 'order' => 1]);
        Product::create([
            'category_id' => $cat->id,
            'restaurant_id' => $this->restaurant->id,
            'name_ar' => 'قهوة',
            'name_en' => 'Coffee',
            'price' => 15.00,
            'is_available' => true,
            'is_recommended' => true,
            'order' => 1,
        ]);
    }

    public function test_can_view_public_menu(): void
    {
        $response = $this->getJson('/api/menus/test-cafe');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'restaurant' => ['slug', 'status'],
                'settings',
                'categories',
                'offers',
                'recommended',
            ]);
    }

    public function test_returns_404_for_nonexistent_slug(): void
    {
        $response = $this->getJson('/api/menus/nonexistent');

        $response->assertStatus(404);
    }

    public function test_returns_403_for_suspended_restaurant(): void
    {
        $this->restaurant->update(['status' => 'suspended']);

        $response = $this->getJson('/api/menus/test-cafe');

        $response->assertStatus(403);
    }

    public function test_can_log_product_view(): void
    {
        $product = Product::first();

        $response = $this->postJson("/api/products/{$product->id}/view");

        $response->assertStatus(200);
        $this->assertDatabaseHas('product_views', ['product_id' => $product->id]);
    }
}
