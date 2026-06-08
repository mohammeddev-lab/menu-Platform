<?php

namespace App\Services\Analytics;

use App\Models\MenuView;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
{
    public function restaurantDashboard(Restaurant $restaurant): array
    {
        $categoriesCount = $restaurant->categories()->count();
        $productsCount = $restaurant->products()->count();
        $offersCount = $restaurant->offers()->where('is_active', true)->count();
        $totalViews = MenuView::where('restaurant_id', $restaurant->id)->count();
        $dailyViews = MenuView::where('restaurant_id', $restaurant->id)
            ->whereDate('viewed_at', today())
            ->count();
        $monthlyViews = MenuView::where('restaurant_id', $restaurant->id)
            ->where(DB::raw("strftime('%m', viewed_at)"), now()->format('m'))
            ->where(DB::raw("strftime('%Y', viewed_at)"), now()->format('Y'))
            ->count();

        $mostViewedProducts = DB::table('product_views')
            ->join('products', 'product_views.product_id', '=', 'products.id')
            ->select('products.name_ar', 'products.name_en', DB::raw('count(*) as views_count'))
            ->where('product_views.restaurant_id', $restaurant->id)
            ->groupBy('products.id', 'products.name_ar', 'products.name_en')
            ->orderBy('views_count', 'desc')
            ->limit(5)
            ->get();

        return [
            'metrics' => [
                'total_views' => $totalViews,
                'daily_views' => $dailyViews,
                'monthly_views' => $monthlyViews,
                'categories_count' => $categoriesCount,
                'products_count' => $productsCount,
                'active_offers' => $offersCount,
            ],
            'most_viewed_products' => $mostViewedProducts,
        ];
    }

    public function restaurantAnalytics(Restaurant $restaurant): array
    {
        $dailyViews = MenuView::where('restaurant_id', $restaurant->id)
            ->where('viewed_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(viewed_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $monthlyViews = MenuView::where('restaurant_id', $restaurant->id)
            ->where('viewed_at', '>=', now()->subMonths(12))
            ->select(DB::raw("strftime('%Y', viewed_at) as year"), DB::raw("strftime('%m', viewed_at) as month"), DB::raw('count(*) as count'))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $mostViewedCategories = DB::table('product_views')
            ->join('products', 'product_views.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name_ar', 'categories.name_en', DB::raw('count(*) as views_count'))
            ->where('product_views.restaurant_id', $restaurant->id)
            ->groupBy('categories.id', 'categories.name_ar', 'categories.name_en')
            ->orderBy('views_count', 'desc')
            ->limit(5)
            ->get();

        $mostViewedProducts = DB::table('product_views')
            ->join('products', 'product_views.product_id', '=', 'products.id')
            ->select('products.name_ar', 'products.name_en', DB::raw('count(*) as views_count'))
            ->where('product_views.restaurant_id', $restaurant->id)
            ->groupBy('products.id', 'products.name_ar', 'products.name_en')
            ->orderBy('views_count', 'desc')
            ->limit(5)
            ->get();

        $deviceStats = MenuView::where('restaurant_id', $restaurant->id)
            ->select('device_type', DB::raw('count(*) as count'))
            ->groupBy('device_type')
            ->get();

        return [
            'daily_views' => $dailyViews,
            'monthly_views' => $monthlyViews,
            'most_viewed_categories' => $mostViewedCategories,
            'most_viewed_products' => $mostViewedProducts,
            'device_stats' => $deviceStats,
        ];
    }

    public function superAdminDashboard(): array
    {
        $totalRestaurants = Restaurant::count();
        $activeRestaurants = Restaurant::where('status', 'active')->count();
        $suspendedRestaurants = Restaurant::where('status', 'suspended')->count();
        $totalMenuViews = MenuView::count();

        $mrr = DB::table('subscriptions')
            ->join('subscription_plans', 'subscriptions.subscription_plan_id', '=', 'subscription_plans.id')
            ->where('subscriptions.status', 'active')
            ->sum('subscription_plans.price');

        $totalProducts = Product::count();
        $totalCategories = \App\Models\Category::count();

        $registrationsGrowth = User::role('restaurant-admin')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $viewsGrowth = MenuView::select(DB::raw('DATE(viewed_at) as date'), DB::raw('count(*) as count'))
            ->where('viewed_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'metrics' => [
                'total_restaurants' => $totalRestaurants,
                'active_restaurants' => $activeRestaurants,
                'suspended_restaurants' => $suspendedRestaurants,
                'total_menu_views' => $totalMenuViews,
                'monthly_revenue' => $mrr,
                'total_products' => $totalProducts,
                'total_categories' => $totalCategories,
            ],
            'charts' => [
                'registrations_growth' => $registrationsGrowth,
                'views_growth' => $viewsGrowth,
            ],
        ];
    }
}
