<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('restaurant-admin')) {
            $restaurant = $user->restaurant;
            if (!$restaurant) {
                return response()->json(['error' => 'You do not have a restaurant associated with your account.'], 403);
            }

            if ($restaurant->status === 'suspended') {
                return response()->json(['error' => 'Your restaurant is suspended. Access denied.'], 403);
            }

            // Bind the tenant restaurant to the request for easy controller access
            $request->merge(['tenant_restaurant' => $restaurant]);
        }

        return $next($request);
    }
}
