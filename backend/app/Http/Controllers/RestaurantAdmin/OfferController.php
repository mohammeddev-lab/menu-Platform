<?php

namespace App\Http\Controllers\RestaurantAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantAdmin\StoreOfferRequest;
use App\Http\Requests\RestaurantAdmin\UpdateOfferRequest;
use App\Http\Resources\OfferResource;
use App\Models\Offer;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $restaurant = $request->get('tenant_restaurant');
        $offers = $restaurant->offers()->get();
        return OfferResource::collection($offers);
    }

    public function store(StoreOfferRequest $request): JsonResponse
    {
        $restaurant = $request->get('tenant_restaurant');

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('offers', 'public');
            $imagePath = asset('storage/' . $imagePath);
        }

        $offer = Offer::create([
            'restaurant_id' => $restaurant->id,
            'title_ar' => $request->title_ar,
            'title_en' => $request->title_en,
            'description_ar' => $request->description_ar,
            'description_en' => $request->description_en,
            'discount_percentage' => $request->discount_percentage,
            'image' => $imagePath,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'is_active' => $request->input('is_active', true),
        ]);

        ActivityLogger::log('create_offer', "Created offer: {$offer->title_en}", $offer->toArray(), $restaurant->id);

        return response()->json(new OfferResource($offer), 201);
    }

    public function update(UpdateOfferRequest $request, Offer $offer): JsonResponse
    {
        $restaurant = $request->get('tenant_restaurant');

        if ($offer->restaurant_id !== $restaurant->id) {
            return response()->json(['error' => 'Unauthorized access to this offer.'], 403);
        }

        $data = $request->only([
            'title_ar', 'title_en', 'description_ar', 'description_en',
            'discount_percentage', 'starts_at', 'ends_at', 'is_active',
        ]);

        if ($request->hasFile('image')) {
            if ($offer->image) {
                $oldPath = str_replace(asset('storage/'), '', $offer->image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('offers', 'public');
            $data['image'] = asset('storage/' . $path);
        }

        $offer->update($data);

        ActivityLogger::log('update_offer', "Updated offer: {$offer->title_en}", $offer->toArray(), $restaurant->id);

        return response()->json(new OfferResource($offer));
    }

    public function destroy(Request $request, Offer $offer): JsonResponse
    {
        $restaurant = $request->get('tenant_restaurant');

        if ($offer->restaurant_id !== $restaurant->id) {
            return response()->json(['error' => 'Unauthorized access to this offer.'], 403);
        }

        if ($offer->image) {
            $oldPath = str_replace(asset('storage/'), '', $offer->image);
            Storage::disk('public')->delete($oldPath);
        }

        ActivityLogger::log('delete_offer', "Deleted offer: {$offer->title_en}", $offer->toArray(), $restaurant->id);

        $offer->delete();

        return response()->json(['message' => 'Offer deleted successfully']);
    }
}
