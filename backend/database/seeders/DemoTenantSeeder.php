<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\MenuView;
use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\Restaurant;
use App\Models\RestaurantSetting;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Super Admin
        $superAdmin = User::create([
            'name' => 'SaaS Admin',
            'email' => 'superadmin@menuplatform.com',
            'password' => Hash::make('password'),
        ]);
        $superAdmin->assignRole('super-admin');

        // Fetch Plans
        $freePlan = SubscriptionPlan::where('name', 'Free Plan')->first();
        $basicPlan = SubscriptionPlan::where('name', 'Basic Plan')->first();
        $premiumPlan = SubscriptionPlan::where('name', 'Premium Plan')->first();
        $enterprisePlan = SubscriptionPlan::where('name', 'Enterprise Plan')->first();

        // 2. Create Star Coffee Tenant (Premium Theme Cafe matching reference)
        $starOwner = User::create([
            'name' => 'Ahmad Star Coffee Owner',
            'email' => 'starcoffee@menuplatform.com',
            'password' => Hash::make('password'),
        ]);
        $starOwner->assignRole('restaurant-admin');

        $starRestaurant = Restaurant::create([
            'user_id' => $starOwner->id,
            'slug' => 'starcoffee',
            'status' => 'active',
        ]);

        RestaurantSetting::create([
            'restaurant_id' => $starRestaurant->id,
            'name_ar' => 'دارك كوفي',
            'name_en' => 'Dark Coffee',
            'logo' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&q=80&w=120&h=120',
            'cover_image' => 'https://images.unsplash.com/photo-1498804103079-a6351b050096?auto=format&fit=crop&q=80&w=1200&h=400',
            'contact_email' => 'info@darkcoffee.com',
            'contact_phone' => '+966500000001',
            'address_ar' => 'طريق الملك فهد، الرياض، المملكة العربية السعودية',
            'address_en' => 'King Fahd Road, Riyadh, Saudi Arabia',
            'working_hours_ar' => [
                'sat_wed' => 'من 6:00 صباحاً إلى 12:00 منتصف الليل',
                'thu_fri' => 'من 6:00 صباحاً إلى 2:00 بعد منتصف الليل'
            ],
            'working_hours_en' => [
                'sat_wed' => '6:00 AM to 12:00 AM',
                'thu_fri' => '6:00 AM to 2:00 AM'
            ],
            'social_links' => [
                'facebook' => 'https://facebook.com/darkcoffee',
                'instagram' => 'https://instagram.com/darkcoffee',
                'twitter' => 'https://twitter.com/darkcoffee',
                'whatsapp' => 'https://wa.me/966500000001'
            ],
            // Theme variables
            'primary_color' => '#1a3c2f', // Dark Green
            'secondary_color' => '#c8a97e', // Gold
            'accent_color' => '#f5f0e8', // Cream
            'button_style' => 'rounded-full',
            'typography_selection' => 'Cairo',
        ]);

        Subscription::create([
            'restaurant_id' => $starRestaurant->id,
            'subscription_plan_id' => $premiumPlan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);

        // Categories for Star Coffee
        $starCats = [
            [
                'name_ar' => 'مشروبات ساخنة', 'name_en' => 'Hot Drinks',
                'subtitle_ar' => 'أجود حبوب البن المحمصة بعناية', 'subtitle_en' => 'Finest roasted coffee beans',
                'icon' => '☕', 'order' => 1,
                'products' => [
                    [
                        'name_ar' => 'إسبريسو', 'name_en' => 'Espresso',
                        'description_ar' => 'جرعة مركزة من البن الغني بالنكهة الكلاسيكية القوية القوام.', 'description_en' => 'A concentrated shot of rich coffee with a strong classic body.',
                        'price' => 12.00, 'discount_price' => null, 'is_featured' => false, 'is_recommended' => false,
                        'tags' => ['Classic', 'Strong'], 'image' => 'https://images.unsplash.com/photo-1510707577719-5d6878021d47?auto=format&fit=crop&q=80&w=300'
                    ],
                    [
                        'name_ar' => 'كابتشينو', 'name_en' => 'Cappuccino',
                        'description_ar' => 'مزيج متوازن من الإسبريسو الفاخر مع حليب مبخر مغطى برغوة كثيفة ورشة كاكاو.', 'description_en' => 'Balanced blend of premium espresso with steamed milk, thick foam, and cocoa sprinkle.',
                        'price' => 16.00, 'discount_price' => 14.00, 'is_featured' => true, 'is_recommended' => true,
                        'tags' => ['Popular'], 'image' => 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?auto=format&fit=crop&q=80&w=300'
                    ],
                    [
                        'name_ar' => 'لاتيه كراميل', 'name_en' => 'Caramel Latte',
                        'description_ar' => 'إسبريسو غني مع حليب مبخر مغطى بطبقة من صوص الكراميل اللذيذ والدافئ.', 'description_en' => 'Rich espresso with steamed milk topped with a layer of warm delicious caramel sauce.',
                        'price' => 18.00, 'discount_price' => null, 'is_featured' => false, 'is_recommended' => true,
                        'tags' => ['Sweet', 'Best Seller'], 'image' => 'https://images.unsplash.com/photo-1570968915860-54d5c301fc9f?auto=format&fit=crop&q=80&w=300'
                    ]
                ]
            ],
            [
                'name_ar' => 'مشروبات باردة', 'name_en' => 'Cold Coffee',
                'subtitle_ar' => 'مشروبات باردة ومنعشة لكل الأوقات', 'subtitle_en' => 'Cold and refreshing drinks for all times',
                'icon' => '🥤', 'order' => 2,
                'products' => [
                    [
                        'name_ar' => 'سبانش لاتيه بارد', 'name_en' => 'Iced Spanish Latte',
                        'description_ar' => 'إسبريسو فاخر مع حليب مكثف ومحلى، يقدّم بارداً مع قطع الثلج.', 'description_en' => 'Premium espresso with condensed sweetened milk, served cold with ice cubes.',
                        'price' => 20.00, 'discount_price' => null, 'is_featured' => true, 'is_recommended' => true,
                        'tags' => ['Sweet', 'Best Seller'], 'image' => 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&q=80&w=300'
                    ],
                    [
                        'name_ar' => 'آيس أمريكانو', 'name_en' => 'Iced Americano',
                        'description_ar' => 'جرعتان من الإسبريسو الممزوج بالماء البارد والثلج لنكهة منعشة وحادة.', 'description_en' => 'Two shots of espresso blended with cold water and ice for a sharp, refreshing taste.',
                        'price' => 14.00, 'discount_price' => null, 'is_featured' => false, 'is_recommended' => false,
                        'tags' => ['Sugar-Free'], 'image' => 'https://images.unsplash.com/photo-1513530534585-c7b1394c6d51?auto=format&fit=crop&q=80&w=300'
                    ],
                ]
            ],
            [
                'name_ar' => 'الحلويات', 'name_en' => 'Desserts',
                'subtitle_ar' => 'حلوياتنا الطازجة والمعدة بحب يومياً', 'subtitle_en' => 'Our fresh desserts made daily with love',
                'icon' => '🍰', 'order' => 3,
                'products' => [
                    [
                        'name_ar' => 'كيكة سان سيباستيان', 'name_en' => 'San Sebastian Cake',
                        'description_ar' => 'كيكة تشيز كيك محروقة الأطراف وقشطية القوام، تقدّم مع الشوكولاتة البلجيكية.', 'description_en' => 'Creamy basque burnt cheesecake served with warm premium Belgian chocolate sauce.',
                        'price' => 24.00, 'discount_price' => null, 'is_featured' => true, 'is_recommended' => true,
                        'tags' => ['Signature', 'Must Try'], 'image' => 'https://images.unsplash.com/photo-1524351199679-46cddf530c04?auto=format&fit=crop&q=80&w=300'
                    ],
                    [
                        'name_ar' => 'تيراميسو كلاسيكي', 'name_en' => 'Classic Tiramisu',
                        'description_ar' => 'حلوى إيطالية كلاسيكية بنكهة الإسبريسو ومغطاة بطبقات من جبن الماسكاربوني الفاخر والكاكاو.', 'description_en' => 'Classic Italian dessert flavored with espresso layered with premium mascarpone cheese and cocoa.',
                        'price' => 22.00, 'discount_price' => 19.00, 'is_featured' => false, 'is_recommended' => false,
                        'tags' => ['Traditional'], 'image' => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?auto=format&fit=crop&q=80&w=300'
                    ]
                ]
            ]
        ];

        $productModels = [];
        foreach ($starCats as $cData) {
            $cat = Category::create([
                'restaurant_id' => $starRestaurant->id,
                'name_ar' => $cData['name_ar'],
                'name_en' => $cData['name_en'],
                'subtitle_ar' => $cData['subtitle_ar'],
                'subtitle_en' => $cData['subtitle_en'],
                'icon' => $cData['icon'],
                'order' => $cData['order'],
            ]);

            foreach ($cData['products'] as $pIdx => $pData) {
                $p = Product::create([
                    'category_id' => $cat->id,
                    'restaurant_id' => $starRestaurant->id,
                    'name_ar' => $pData['name_ar'],
                    'name_en' => $pData['name_en'],
                    'description_ar' => $pData['description_ar'],
                    'description_en' => $pData['description_en'],
                    'price' => $pData['price'],
                    'discount_price' => $pData['discount_price'],
                    'is_available' => true,
                    'is_featured' => $pData['is_featured'],
                    'is_recommended' => $pData['is_recommended'],
                    'tags' => $pData['tags'],
                    'image' => $pData['image'],
                    'order' => $pIdx + 1,
                ]);
                $productModels[] = $p;
            }
        }

        // Active Offer for Star Coffee
        Offer::create([
            'restaurant_id' => $starRestaurant->id,
            'title_ar' => 'عرض الصيف المنعش 🍹',
            'title_en' => 'Refreshing Summer Offer 🍹',
            'description_ar' => 'احصل على خصم 15% على جميع المشروبات الباردة بمناسبة فصل الصيف.',
            'description_en' => 'Get 15% off on all cold drinks to beat the heat this summer.',
            'discount_percentage' => 15.00,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(15),
            'is_active' => true,
            'image' => 'https://images.unsplash.com/photo-1536935338788-846bb9981813?auto=format&fit=crop&q=80&w=500&h=250',
        ]);


        // 3. Create Al Reef Tenant (Traditional Grills)
        $reefOwner = User::create([
            'name' => 'Fahad Al Reef Owner',
            'email' => 'alreef@menuplatform.com',
            'password' => Hash::make('password'),
        ]);
        $reefOwner->assignRole('restaurant-admin');

        $reefRestaurant = Restaurant::create([
            'user_id' => $reefOwner->id,
            'slug' => 'alreef',
            'status' => 'active',
        ]);

        RestaurantSetting::create([
            'restaurant_id' => $reefRestaurant->id,
            'name_ar' => 'مطعم الريف للمشويات',
            'name_en' => 'Al Reef Grills',
            'logo' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&q=80&w=120&h=120',
            'cover_image' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&q=80&w=1200&h=400',
            'contact_email' => 'contact@alreefgrills.com',
            'contact_phone' => '+966500000002',
            'address_ar' => 'شارع التخصصي، الرياض، المملكة العربية السعودية',
            'address_en' => 'Takhassusi St, Riyadh, Saudi Arabia',
            'working_hours_ar' => [
                'daily' => 'من 12:00 ظهراً إلى 1:00 بعد منتصف الليل'
            ],
            'working_hours_en' => [
                'daily' => '12:00 PM to 1:00 AM'
            ],
            'social_links' => [
                'instagram' => 'https://instagram.com/alreef',
                'twitter' => 'https://twitter.com/alreef',
                'whatsapp' => 'https://wa.me/966500000002'
            ],
            'primary_color' => '#8b0000', // Crimson Red
            'secondary_color' => '#d4af37', // Copper
            'accent_color' => '#fdf5e6', // Warm White
            'button_style' => 'rounded-md',
            'typography_selection' => 'Cairo',
        ]);

        Subscription::create([
            'restaurant_id' => $reefRestaurant->id,
            'subscription_plan_id' => $enterprisePlan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays(365),
        ]);

        // Categories & Products for Al Reef
        $reefCat = Category::create([
            'restaurant_id' => $reefRestaurant->id,
            'name_ar' => 'مشويات الريف', 'name_en' => 'Al Reef Grills',
            'subtitle_ar' => 'مشوية على الفحم الطبيعي بلحم طازج يومياً', 'subtitle_en' => 'Grilled on natural charcoal using fresh meat daily',
            'icon' => '🍖', 'order' => 1,
        ]);

        $reefProduct = Product::create([
            'category_id' => $reefCat->id,
            'restaurant_id' => $reefRestaurant->id,
            'name_ar' => 'صحن مشويات مشكل', 'name_en' => 'Mix Grill Platter',
            'description_ar' => 'تشكيلة فاخرة من شيش طاووق، كباب لحم، كباب دجاج وريش غنم طازجة مشوية على الفحم، مع أرز مقبلات وبطاطس.', 'description_en' => 'Premium selection of shish tawook, beef kebab, chicken kebab, and lamb chops charcoal grilled, served with rice, appetizers, and fries.',
            'price' => 65.00, 'discount_price' => 55.00,
            'is_available' => true, 'is_featured' => true, 'is_recommended' => true,
            'tags' => ['Must Try', 'Best Seller'],
            'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&q=80&w=300',
        ]);


        // 4. Create Burger House Tenant (Standard / Free Tier Burger shop)
        $burgerOwner = User::create([
            'name' => 'Khalid Burger House Owner',
            'email' => 'burgerhouse@menuplatform.com',
            'password' => Hash::make('password'),
        ]);
        $burgerOwner->assignRole('restaurant-admin');

        $burgerRestaurant = Restaurant::create([
            'user_id' => $burgerOwner->id,
            'slug' => 'burgerhouse',
            'status' => 'active',
        ]);

        RestaurantSetting::create([
            'restaurant_id' => $burgerRestaurant->id,
            'name_ar' => 'بيت البرجر',
            'name_en' => 'Burger House',
            'logo' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&q=80&w=120&h=120',
            'cover_image' => 'https://images.unsplash.com/photo-1550547660-d9450f859349?auto=format&fit=crop&q=80&w=1200&h=400',
            'contact_email' => 'hello@burgerhouse.com',
            'contact_phone' => '+966500000003',
            'address_ar' => 'شارع العليا العام، الرياض، المملكة العربية السعودية',
            'address_en' => 'Olaya Main St, Riyadh, Saudi Arabia',
            'working_hours_ar' => [
                'daily' => 'من 1:00 ظهراً إلى 3:00 بعد منتصف الليل'
            ],
            'working_hours_en' => [
                'daily' => '1:00 PM to 3:00 AM'
            ],
            'social_links' => [
                'instagram' => 'https://instagram.com/burgerhouse',
                'twitter' => 'https://twitter.com/burgerhouse',
            ],
            'primary_color' => '#0f0f0f', // Matte black
            'secondary_color' => '#ffe4c4', // Bisque
            'accent_color' => '#ffffff',
            'button_style' => 'rounded-none',
            'typography_selection' => 'Inter',
        ]);

        Subscription::create([
            'restaurant_id' => $burgerRestaurant->id,
            'subscription_plan_id' => $freePlan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);

        $burgerCat = Category::create([
            'restaurant_id' => $burgerRestaurant->id,
            'name_ar' => 'البرجر', 'name_en' => 'Burgers',
            'subtitle_ar' => 'برجر محضر بصلصاتنا السرية الخاصة', 'subtitle_en' => 'Burgers prepared with our secret home-made sauces',
            'icon' => '🍔', 'order' => 1,
        ]);

        $burgerProduct = Product::create([
            'category_id' => $burgerCat->id,
            'restaurant_id' => $burgerRestaurant->id,
            'name_ar' => 'برجر اللحم الكلاسيكي', 'name_en' => 'Classic Beef Burger',
            'description_ar' => 'لحم أنغوس بقري مشوي مع جبنة شيدر ذائبة، خس، طماطم، مخلل وصلصة بيت البرجر اللذيذة.', 'description_en' => 'Grilled Angus beef patty with melted cheddar cheese, lettuce, tomato, pickles, and delicious Burger House sauce.',
            'price' => 28.00, 'discount_price' => null,
            'is_available' => true, 'is_featured' => true, 'is_recommended' => false,
            'tags' => ['Fresh', 'Original'],
            'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&q=80&w=300',
        ]);


        // 5. Generate Simulated Time-Series Analytics Data (Past 30 Days)
        $devices = ['mobile', 'mobile', 'mobile', 'tablet', 'desktop']; // Heavy mobile distribution
        $userAgentList = [
            'mobile' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Mobile/15E148 Safari/604.1',
            'tablet' => 'Mozilla/5.0 (iPad; CPU OS 16_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Mobile/15E148 Safari/604.1',
            'desktop' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36'
        ];

        // Let's seed for Star Coffee and Al Reef
        $targetRestaurants = [$starRestaurant, $reefRestaurant];

        foreach ($targetRestaurants as $rest) {
            $prods = $rest->products;
            if ($prods->isEmpty()) continue;

            $now = Carbon::now();

            for ($dayOffset = 30; $dayOffset >= 0; $dayOffset--) {
                $targetDate = (clone $now)->subDays($dayOffset);

                // Set random views count for this day (simulate lower views in mid-week and higher on weekends)
                $isWeekend = in_array($targetDate->dayOfWeek, [Carbon::THURSDAY, Carbon::FRIDAY, Carbon::SATURDAY]);
                $menuViewsCount = $isWeekend ? rand(30, 80) : rand(15, 40);

                // Create Menu Views
                for ($v = 0; $v < $menuViewsCount; $v++) {
                    $device = $devices[array_rand($devices)];
                    MenuView::create([
                        'restaurant_id' => $rest->id,
                        'ip_address' => '192.168.1.' . rand(1, 254),
                        'user_agent' => $userAgentList[$device],
                        'device_type' => $device,
                        'viewed_at' => (clone $targetDate)->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
                    ]);
                }

                // Create Product Views (usually 2-3x menu views as visitors browse items)
                $productViewsCount = $menuViewsCount * rand(2, 4);
                for ($v = 0; $v < $productViewsCount; $v++) {
                    $device = $devices[array_rand($devices)];
                    $p = $prods->random();
                    ProductView::create([
                        'product_id' => $p->id,
                        'restaurant_id' => $rest->id,
                        'ip_address' => '192.168.1.' . rand(1, 254),
                        'user_agent' => $userAgentList[$device],
                        'device_type' => $device,
                        'viewed_at' => (clone $targetDate)->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
                    ]);
                }
            }

            // Create some activity logs for each tenant
            ActivityLog::create([
                'user_id' => $rest->user_id,
                'restaurant_id' => $rest->id,
                'action' => 'update_settings',
                'description' => 'Restaurant owner updated contact phone and working hours.',
                'ip_address' => '192.168.1.100',
                'payload' => ['changed_fields' => ['contact_phone', 'working_hours_ar', 'working_hours_en']],
                'created_at' => now()->subDays(5),
            ]);

            ActivityLog::create([
                'user_id' => $rest->user_id,
                'restaurant_id' => $rest->id,
                'action' => 'create_product',
                'description' => 'Restaurant owner added a new featured product item.',
                'ip_address' => '192.168.1.100',
                'payload' => ['product_name' => 'New Premium Espresso Drink'],
                'created_at' => now()->subDays(2),
            ]);
        }
    }
}
