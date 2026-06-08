<?php

namespace App\Http\Middleware;

use App\Models\Restaurant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuspendedRestaurant
{
    public function handle(Request $request, Closure $next): Response
    {
        // Try to find restaurant slug from route parameter or request input/header
        $slug = $request->route('slug') ?? $request->input('restaurant_slug') ?? $request->header('X-Restaurant-Slug');

        if ($slug) {
            $restaurant = Restaurant::where('slug', $slug)->first();
            if ($restaurant && $restaurant->status === 'suspended') {
                return response()->json([
                    'error' => 'This restaurant menu is currently suspended. Please contact support.',
                    'status' => 'suspended'
                ], 403);
            }
        }

        return $next($request);
    }
}
