<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreRestaurantRequest;
use App\Http\Requests\SuperAdmin\UpdateRestaurantStatusRequest;
use App\Http\Resources\RestaurantResource;
use App\Models\Restaurant;
use App\Models\RestaurantSetting;
use App\Models\Subscription;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RestaurantController extends Controller
{
    public function index()
    {
        $restaurants = Restaurant::with(['owner', 'settings', 'activeSubscription.plan'])->get();
        return RestaurantResource::collection($restaurants);
    }

    public function store(StoreRestaurantRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $restaurant = Restaurant::create([
                'user_id' => $request->owner_id,
                'slug' => $request->slug,
                'status' => 'active',
            ]);

            RestaurantSetting::create([
                'restaurant_id' => $restaurant->id,
                'name_ar' => $request->name_ar,
                'name_en' => $request->name_en,
            ]);

            $plan = DB::table('subscription_plans')->where('id', $request->plan_id)->first();
            Subscription::create([
                'restaurant_id' => $restaurant->id,
                'subscription_plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => now()->addDays($plan->duration_days),
            ]);

            ActivityLogger::log('create_restaurant', 'Super admin created restaurant: ' . $request->name_en, null, $restaurant->id);

            return response()->json(new RestaurantResource($restaurant->load(['settings', 'owner', 'activeSubscription.plan'])), 201);
        });
    }

    public function show(Restaurant $restaurant)
    {
        return new RestaurantResource($restaurant->load(['owner', 'settings', 'activeSubscription.plan']));
    }

    public function updateStatus(UpdateRestaurantStatusRequest $request, Restaurant $restaurant): JsonResponse
    {
        $oldStatus = $restaurant->status;
        $restaurant->update(['status' => $request->status]);

        ActivityLogger::log(
            'restaurant_status_update',
            "Super admin changed restaurant status of slug '{$restaurant->slug}' from '{$oldStatus}' to '{$request->status}'.",
            ['old_status' => $oldStatus, 'new_status' => $request->status],
            $restaurant->id
        );

        return response()->json([
            'message' => 'Status updated successfully',
            'restaurant' => new RestaurantResource($restaurant->fresh()->load(['settings', 'owner', 'activeSubscription.plan'])),
        ]);
    }

    public function destroy(Restaurant $restaurant): JsonResponse
    {
        ActivityLogger::log('delete_restaurant', "Super admin deleted restaurant: {$restaurant->slug}", null, $restaurant->id);
        $restaurant->delete();
        return response()->json(['message' => 'Restaurant deleted successfully']);
    }
}
