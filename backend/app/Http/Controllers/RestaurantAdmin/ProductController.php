<?php

namespace App\Http\Controllers\RestaurantAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantAdmin\ReorderRequest;
use App\Http\Requests\RestaurantAdmin\StoreProductRequest;
use App\Http\Requests\RestaurantAdmin\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $restaurant = $request->get('tenant_restaurant');
        $products = $restaurant->products()->with('category')->get();
        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $restaurant = $request->get('tenant_restaurant');

        $restaurant->load('activeSubscription.plan');
        $activeSub = $restaurant->activeSubscription;
        $limit = $activeSub ? $activeSub->plan->limit_products : 15;
        $currentCount = $restaurant->products()->count();

        if ($currentCount >= $limit) {
            return response()->json([
                'error' => "You have reached the limit of {$limit} products allowed by your plan. Please upgrade."
            ], 403);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $imagePath = asset('storage/' . $imagePath);
        }

        $maxOrder = Product::where('category_id', $request->category_id)->max('order') ?? 0;

        $product = Product::create([
            'category_id' => $request->category_id,
            'restaurant_id' => $restaurant->id,
            'name' => $request->name_en ?? $request->name,
            'name_ar' => $request->name_ar ?? $request->name,
            'name_en' => $request->name_en ?? $request->name,
            'description' => $request->description_en ?? $request->description,
            'description_ar' => $request->description_ar ?? $request->description,
            'description_en' => $request->description_en ?? $request->description,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'image' => $imagePath,
            'is_available' => $request->input('is_available', true),
            'is_featured' => $request->input('is_featured', false),
            'is_recommended' => $request->input('is_recommended', false),
            'tags' => $request->tags,
            'order' => $maxOrder + 1,
        ]);

        ActivityLogger::log('create_product', "Created product: {$product->name}", $product->toArray(), $restaurant->id);

        return response()->json(new ProductResource($product), 201);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $restaurant = $request->get('tenant_restaurant');

        if ($product->restaurant_id !== $restaurant->id) {
            return response()->json(['error' => 'Unauthorized access to this product.'], 403);
        }

        $data = $request->only([
            'category_id', 'name', 'name_ar', 'name_en', 'description', 'description_ar', 'description_en',
            'price', 'discount_price', 'is_available', 'is_featured', 'is_recommended', 'tags',
        ]);
        $data['name_ar'] = $data['name_ar'] ?? $data['name'];
        $data['name_en'] = $data['name_en'] ?? $data['name'];
        $data['description_ar'] = $data['description_ar'] ?? ($data['description'] ?? null);
        $data['description_en'] = $data['description_en'] ?? ($data['description'] ?? null);

        if ($request->hasFile('image')) {
            if ($product->image) {
                $oldPath = str_replace(asset('storage/'), '', $product->image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = asset('storage/' . $path);
        }

        $product->update($data);

        ActivityLogger::log('update_product', "Updated product: {$product->name}", $product->toArray(), $restaurant->id);

        return response()->json(new ProductResource($product));
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $restaurant = $request->get('tenant_restaurant');

        DB::transaction(function () use ($request, $restaurant) {
            foreach ($request->order as $item) {
                Product::where('id', $item['id'])
                    ->where('restaurant_id', $restaurant->id)
                    ->update(['order' => $item['order']]);
            }
        });

        ActivityLogger::log('reorder_products', 'Reordered product catalog.', $request->order, $restaurant->id);

        return response()->json(['message' => 'Products reordered successfully']);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $restaurant = $request->get('tenant_restaurant');

        if ($product->restaurant_id !== $restaurant->id) {
            return response()->json(['error' => 'Unauthorized access to this product.'], 403);
        }

        if ($product->image) {
            $oldPath = str_replace(asset('storage/'), '', $product->image);
            Storage::disk('public')->delete($oldPath);
        }

        ActivityLogger::log('delete_product', "Deleted product: {$product->name}", $product->toArray(), $restaurant->id);

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
