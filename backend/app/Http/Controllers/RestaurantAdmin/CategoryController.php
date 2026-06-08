<?php

namespace App\Http\Controllers\RestaurantAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantAdmin\ReorderRequest;
use App\Http\Requests\RestaurantAdmin\StoreCategoryRequest;
use App\Http\Requests\RestaurantAdmin\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $restaurant = $request->get('tenant_restaurant');
        $categories = $restaurant->categories()->withCount('products')->get();
        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $restaurant = $request->get('tenant_restaurant');

        $activeSub = $restaurant->activeSubscription;
        $limit = $activeSub ? $activeSub->plan->limit_categories : 2;
        $currentCount = $restaurant->categories()->count();

        if ($currentCount >= $limit) {
            return response()->json([
                'error' => "You have reached the limit of {$limit} categories allowed by your plan. Please upgrade."
            ], 403);
        }

        $maxOrder = $restaurant->categories()->max('order') ?? 0;

        $category = Category::create([
            'restaurant_id' => $restaurant->id,
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'subtitle_ar' => $request->subtitle_ar,
            'subtitle_en' => $request->subtitle_en,
            'icon' => $request->icon,
            'order' => $maxOrder + 1,
        ]);

        ActivityLogger::log('create_category', "Created category: {$category->name_en}", $category->toArray(), $restaurant->id);

        return response()->json(new CategoryResource($category), 201);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $restaurant = $request->get('tenant_restaurant');

        if ($category->restaurant_id !== $restaurant->id) {
            return response()->json(['error' => 'Unauthorized access to this category.'], 403);
        }

        $category->update($request->only([
            'name_ar', 'name_en', 'subtitle_ar', 'subtitle_en', 'icon'
        ]));

        ActivityLogger::log('update_category', "Updated category: {$category->name_en}", $category->toArray(), $restaurant->id);

        return response()->json(new CategoryResource($category));
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $restaurant = $request->get('tenant_restaurant');

        DB::transaction(function () use ($request, $restaurant) {
            foreach ($request->order as $item) {
                Category::where('id', $item['id'])
                    ->where('restaurant_id', $restaurant->id)
                    ->update(['order' => $item['order']]);
            }
        });

        ActivityLogger::log('reorder_categories', 'Reordered category layout.', $request->order, $restaurant->id);

        return response()->json(['message' => 'Categories reordered successfully']);
    }

    public function destroy(Request $request, Category $category): JsonResponse
    {
        $restaurant = $request->get('tenant_restaurant');

        if ($category->restaurant_id !== $restaurant->id) {
            return response()->json(['error' => 'Unauthorized access to this category.'], 403);
        }

        ActivityLogger::log('delete_category', "Deleted category: {$category->name_en}", $category->toArray(), $restaurant->id);

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
