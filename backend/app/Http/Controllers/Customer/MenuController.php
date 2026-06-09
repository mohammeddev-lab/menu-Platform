<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\OfferResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\RestaurantSettingResource;
use App\Models\MenuView;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function getMenu(Request $request, $slug)
    {
        $restaurant = Restaurant::where('slug', $slug)->first();

        if (!$restaurant) {
            return response()->json(['error' => 'Restaurant not found.'], 404);
        }

        if ($restaurant->status === 'suspended') {
            return response()->json(['error' => 'This menu is currently suspended.'], 403);
        }

        $settings = $restaurant->settings;
        $categories = $restaurant->categories()->with(['products' => function ($query) {
            $query->where('is_available', true)->orderBy('order');
        }])->orderBy('order')->get();

        $offers = $restaurant->offers()->active()->get();
        
        $recommendedProducts = $restaurant->products()
            ->where('is_available', true)
            ->where('is_recommended', true)
            ->limit(6)
            ->get();

        // Log Menu View Hit
        $userAgent = $request->header('User-Agent');
        $deviceType = 'desktop';
        if (str_contains($userAgent, 'Mobi')) {
            $deviceType = 'mobile';
        } elseif (str_contains($userAgent, 'iPad') || str_contains($userAgent, 'Tablet')) {
            $deviceType = 'tablet';
        }

        MenuView::create([
            'restaurant_id' => $restaurant->id,
            'ip_address' => $request->ip(),
            'user_agent' => $userAgent,
            'device_type' => $deviceType,
            'viewed_at' => now(),
        ]);

        return response()->json([
            'restaurant' => [
                'slug' => $restaurant->slug,
                'status' => $restaurant->status,
            ],
            'settings' => new RestaurantSettingResource($settings),
            'categories' => $categories->map(fn ($c) => new CategoryResource($c)),
            'offers' => $offers->map(fn ($o) => new OfferResource($o)),
            'recommended' => $recommendedProducts->map(fn ($p) => new ProductResource($p)),
        ]);
    }

    public function logProductView(Request $request, Product $product)
    {
        // Log a product hit
        $userAgent = $request->header('User-Agent');
        $deviceType = 'desktop';
        if (str_contains($userAgent, 'Mobi')) {
            $deviceType = 'mobile';
        } elseif (str_contains($userAgent, 'iPad') || str_contains($userAgent, 'Tablet')) {
            $deviceType = 'tablet';
        }

        ProductView::create([
            'product_id' => $product->id,
            'restaurant_id' => $product->restaurant_id,
            'ip_address' => $request->ip(),
            'user_agent' => $userAgent,
            'device_type' => $deviceType,
            'viewed_at' => now(),
        ]);

        return response()->json(['message' => 'Product view logged successfully']);
    }
}
