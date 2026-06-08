<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'subtitle_ar' => $this->subtitle_ar,
            'subtitle_en' => $this->subtitle_en,
            'icon' => $this->icon,
            'order' => $this->order,
            'products_count' => $this->whenCounted('products'),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
