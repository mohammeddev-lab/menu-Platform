<?php

namespace App\Http\Controllers\RestaurantAdmin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\DashboardAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardAnalyticsService $analyticsService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $restaurant = $request->get('tenant_restaurant');

        return response()->json(
            $this->analyticsService->restaurantDashboard($restaurant)
        );
    }
}
