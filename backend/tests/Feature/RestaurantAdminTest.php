<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\RestaurantSetting;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RestaurantAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Restaurant $restaurant;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SubscriptionPlanSeeder::class);

        $this->user = User::factory()->create();
        $this->user->assignRole('restaurant-admin');

        $plan = SubscriptionPlan::first();
        $this->restaurant = Restaurant::create([
            'user_id' => $this->user->id,
            'slug' => 'test-restaurant',
            'status' => 'active',
        ]);
        RestaurantSetting::create(['restaurant_id' => $this->restaurant->id, 'name_ar' => 'ar', 'name_en' => 'en']);
        Subscription::create([
            'restaurant_id' => $this->restaurant->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);

        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    public function test_can_view_dashboard(): void
    {
        $response = $this->getJson('/api/restaurant-admin/dashboard', $this->headers());

        $response->assertStatus(200)
            ->assertJsonStructure(['metrics', 'most_viewed_products']);
    }

    public function test_can_view_analytics(): void
    {
        $response = $this->getJson('/api/restaurant-admin/analytics', $this->headers());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'daily_views', 'monthly_views',
                'most_viewed_categories', 'most_viewed_products', 'device_stats',
            ]);
    }

    public function test_can_create_category(): void
    {
        $response = $this->postJson('/api/restaurant-admin/categories', [
            'name_ar' => 'مشروبات ساخنة',
            'name_en' => 'Hot Drinks',
            'subtitle_ar' => 'أجود أنواع القهوة',
            'subtitle_en' => 'Finest coffee',
            'icon' => '☕',
        ], $this->headers());

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'name_ar', 'name_en', 'order']);
    }

    public function test_category_respects_plan_limit(): void
    {
        $plan = $this->restaurant->activeSubscription->plan;
        $plan->update(['limit_categories' => 0]);

        $response = $this->postJson('/api/restaurant-admin/categories', [
            'name_ar' => 'كاف',
            'name_en' => 'Cafe',
        ], $this->headers());

        $response->assertStatus(403);
    }

    public function test_can_update_category(): void
    {
        $category = Category::create([
            'restaurant_id' => $this->restaurant->id,
            'name_ar' => 'قديم',
            'name_en' => 'Old',
            'order' => 1,
        ]);

        $response = $this->putJson("/api/restaurant-admin/categories/{$category->id}", [
            'name_ar' => 'جديد',
            'name_en' => 'New',
        ], $this->headers());

        $response->assertStatus(200)
            ->assertJsonPath('data.name_en', 'New');
    }

    public function test_can_reorder_categories(): void
    {
        $cat1 = Category::create(['restaurant_id' => $this->restaurant->id, 'name_ar' => 'أ', 'name_en' => 'A', 'order' => 1]);
        $cat2 = Category::create(['restaurant_id' => $this->restaurant->id, 'name_ar' => 'ب', 'name_en' => 'B', 'order' => 2]);

        $response = $this->postJson('/api/restaurant-admin/categories/reorder', [
            'order' => [
                ['id' => $cat1->id, 'order' => 2],
                ['id' => $cat2->id, 'order' => 1],
            ],
        ], $this->headers());

        $response->assertStatus(200);
    }

    public function test_cannot_update_other_restaurants_category(): void
    {
        $other = Restaurant::create(['user_id' => User::factory()->create()->id, 'slug' => 'other']);
        $category = Category::create(['restaurant_id' => $other->id, 'name_ar' => 'x', 'name_en' => 'x', 'order' => 1]);

        $response = $this->putJson("/api/restaurant-admin/categories/{$category->id}", [
            'name_ar' => 'hack',
            'name_en' => 'hack',
        ], $this->headers());

        $response->assertStatus(403);
    }

    public function test_can_delete_category(): void
    {
        $category = Category::create([
            'restaurant_id' => $this->restaurant->id,
            'name_ar' => 'حذف',
            'name_en' => 'Delete',
            'order' => 1,
        ]);

        $response = $this->deleteJson("/api/restaurant-admin/categories/{$category->id}", [], $this->headers());

        $response->assertStatus(200);
        $this->assertModelMissing($category);
    }

    public function test_can_create_product(): void
    {
        $category = Category::create([
            'restaurant_id' => $this->restaurant->id,
            'name_ar' => 'قسم',
            'name_en' => 'Category',
            'order' => 1,
        ]);

        $response = $this->postJson('/api/restaurant-admin/products', [
            'category_id' => $category->id,
            'name_ar' => 'قهوة',
            'name_en' => 'Coffee',
            'description_ar' => 'وصف',
            'description_en' => 'Description',
            'price' => 15.00,
            'is_available' => true,
            'tags' => ['Popular'],
        ], $this->headers());

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'name_en', 'price', 'active_price']]);
    }

    public function test_product_respects_plan_limit(): void
    {
        $plan = $this->restaurant->activeSubscription->plan;
        $plan->update(['limit_products' => 0]);

        $category = Category::create(['restaurant_id' => $this->restaurant->id, 'name_ar' => 'x', 'name_en' => 'x', 'order' => 1]);

        $response = $this->postJson('/api/restaurant-admin/products', [
            'category_id' => $category->id,
            'name_ar' => 'شاي',
            'name_en' => 'Tea',
            'price' => 10.00,
        ], $this->headers());

        $response->assertStatus(403);
    }

    public function test_can_update_product(): void
    {
        $category = Category::create(['restaurant_id' => $this->restaurant->id, 'name_ar' => 'x', 'name_en' => 'x', 'order' => 1]);
        $product = Product::create([
            'category_id' => $category->id,
            'restaurant_id' => $this->restaurant->id,
            'name_ar' => 'قديم',
            'name_en' => 'Old',
            'price' => 10.00,
            'order' => 1,
        ]);

        $response = $this->putJson("/api/restaurant-admin/products/{$product->id}", [
            'category_id' => $category->id,
            'name_ar' => 'جديد',
            'name_en' => 'New',
            'price' => 20.00,
        ], $this->headers());

        $response->assertStatus(200)
            ->assertJsonPath('data.name_en', 'New')
            ->assertJsonPath('data.price', 20.0);
    }

    public function test_can_reorder_products(): void
    {
        $category = Category::create(['restaurant_id' => $this->restaurant->id, 'name_ar' => 'x', 'name_en' => 'x', 'order' => 1]);
        $prod1 = Product::create(['category_id' => $category->id, 'restaurant_id' => $this->restaurant->id, 'name_ar' => 'أ', 'name_en' => 'A', 'price' => 10, 'order' => 1]);
        $prod2 = Product::create(['category_id' => $category->id, 'restaurant_id' => $this->restaurant->id, 'name_ar' => 'ب', 'name_en' => 'B', 'price' => 10, 'order' => 2]);

        $response = $this->postJson('/api/restaurant-admin/products/reorder', [
            'order' => [
                ['id' => $prod1->id, 'order' => 2],
                ['id' => $prod2->id, 'order' => 1],
            ],
        ], $this->headers());

        $response->assertStatus(200);
    }

    public function test_can_delete_product(): void
    {
        $category = Category::create(['restaurant_id' => $this->restaurant->id, 'name_ar' => 'x', 'name_en' => 'x', 'order' => 1]);
        $product = Product::create([
            'category_id' => $category->id,
            'restaurant_id' => $this->restaurant->id,
            'name_ar' => 'x',
            'name_en' => 'Del Prod',
            'price' => 5.00,
            'order' => 1,
        ]);

        $response = $this->deleteJson("/api/restaurant-admin/products/{$product->id}", [], $this->headers());

        $response->assertStatus(200);
        $this->assertModelMissing($product);
    }

    public function test_can_create_offer(): void
    {
        $response = $this->postJson('/api/restaurant-admin/offers', [
            'title_ar' => 'عرض الصيف',
            'title_en' => 'Summer Offer',
            'description_ar' => 'خصم 10%',
            'description_en' => '10% discount',
            'discount_percentage' => 10,
            'is_active' => true,
        ], $this->headers());

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'title_en', 'discount_percentage']]);
    }

    public function test_can_update_offer(): void
    {
        $offer = Offer::create([
            'restaurant_id' => $this->restaurant->id,
            'title_ar' => 'قديم',
            'title_en' => 'Old Offer',
        ]);

        $response = $this->putJson("/api/restaurant-admin/offers/{$offer->id}", [
            'title_ar' => 'جديد',
            'title_en' => 'New Offer',
        ], $this->headers());

        $response->assertStatus(200)
            ->assertJsonPath('data.title_en', 'New Offer');
    }

    public function test_can_view_settings(): void
    {
        $response = $this->getJson('/api/restaurant-admin/settings', $this->headers());

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'name_ar', 'name_en']]);
    }

    public function test_can_update_settings(): void
    {
        $response = $this->postJson('/api/restaurant-admin/settings', [
            'name_ar' => 'مطعم جديد',
            'name_en' => 'New Restaurant',
            'primary_color' => '#ff0000',
            'secondary_color' => '#00ff00',
            'accent_color' => '#0000ff',
            'button_style' => 'rounded-lg',
            'typography_selection' => 'Inter',
        ], $this->headers());

        $response->assertStatus(200)
            ->assertJsonPath('settings.data.name_en', 'New Restaurant');
    }

    public function test_can_view_qr_code(): void
    {
        $response = $this->getJson('/api/restaurant-admin/settings/qr', $this->headers());

        $response->assertStatus(200)
            ->assertJsonStructure(['url', 'qr_code_base64']);
    }

    public function test_restaurant_admin_cannot_access_super_admin_routes(): void
    {
        $response = $this->getJson('/api/super-admin/dashboard', $this->headers());

        $response->assertStatus(403);
    }
}
