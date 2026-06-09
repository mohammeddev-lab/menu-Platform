<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StorePlanRequest;
use App\Http\Requests\SuperAdmin\UpdatePlanRequest;
use App\Http\Resources\SubscriptionPlanResource;
use App\Models\SubscriptionPlan;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        return SubscriptionPlanResource::collection(SubscriptionPlan::all());
    }

    public function store(StorePlanRequest $request): JsonResponse
    {
        $plan = SubscriptionPlan::create($request->only([
            'name', 'price', 'duration_days', 'features', 'limit_categories', 'limit_products',
        ]));

        ActivityLogger::log('create_plan', "Super admin created subscription plan: {$plan->name}", $plan->toArray());

        return response()->json(new SubscriptionPlanResource($plan), 201);
    }

    public function update(UpdatePlanRequest $request, SubscriptionPlan $plan): JsonResponse
    {
        $plan->update($request->only([
            'name', 'price', 'duration_days', 'features', 'limit_categories', 'limit_products',
        ]));

        ActivityLogger::log('update_plan', "Super admin updated subscription plan: {$plan->name}", $plan->toArray());

        return response()->json(new SubscriptionPlanResource($plan));
    }

    public function destroy(SubscriptionPlan $plan): JsonResponse
    {
        if ($plan->subscriptions()->where('status', 'active')->exists()) {
            return response()->json(['error' => 'Cannot delete plan with active subscriptions.'], 422);
        }

        ActivityLogger::log('delete_plan', "Super admin deleted subscription plan: {$plan->name}");
        $plan->delete();

        return response()->json(['message' => 'Subscription plan deleted successfully']);
    }
}
