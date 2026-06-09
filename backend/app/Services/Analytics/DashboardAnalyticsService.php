<?php

namespace App\Services\Analytics;

use App\Models\Category;
use App\Models\MenuView;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
{
    private function dateExtract(string $column, string $part): string
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $format = $part === 'year' ? '%Y' : '%m';
            return "strftime('{$format}', {$column})";
        }
        return strtoupper($part) . "({$column})";
    }
    public function restaurantDashboard(Restaurant $restaurant): array
    {
        $categoriesCount = $restaurant->categories()->count();
        $productsCount = $restaurant->products()->count();
        $offersCount = $restaurant->offers()->where('is_active', true)->count();

        $viewStats = MenuView::where('restaurant_id', $restaurant->id)
            ->selectRaw('COUNT(*) as total_views')
            ->selectRaw('SUM(CASE WHEN viewed_at >= ? THEN 1 ELSE 0 END) as daily_views', [now()->startOfDay()])
            ->selectRaw('SUM(CASE WHEN viewed_at >= ? AND viewed_at <= ? THEN 1 ELSE 0 END) as monthly_views',
                [now()->startOfMonth(), now()->endOfMonth()])
            ->first();

        $mostViewedProducts = DB::table('product_views')
            ->join('products', 'product_views.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('count(*) as views_count'))
            ->where('product_views.restaurant_id', $restaurant->id)
            ->groupBy('products.id', 'products.name')
            ->orderBy('views_count', 'desc')
            ->limit(5)
            ->get();

        return [
            'metrics' => [
                'total_views' => (int) $viewStats->total_views,
                'daily_views' => (int) $viewStats->daily_views,
                'monthly_views' => (int) $viewStats->monthly_views,
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
            ->select(DB::raw($this->dateExtract('viewed_at', 'year') . ' as year'), DB::raw($this->dateExtract('viewed_at', 'month') . ' as month'), DB::raw('count(*) as count'))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $mostViewedCategories = DB::table('product_views')
            ->join('products', 'product_views.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('count(*) as views_count'))
            ->where('product_views.restaurant_id', $restaurant->id)
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('views_count', 'desc')
            ->limit(5)
            ->get();

        $mostViewedProducts = DB::table('product_views')
            ->join('products', 'product_views.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('count(*) as views_count'))
            ->where('product_views.restaurant_id', $restaurant->id)
            ->groupBy('products.id', 'products.name')
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
        $totalCategories = Category::count();

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
