<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => (float) $this->price,
            'duration_days' => $this->duration_days,
            'features' => $this->features,
            'limit_categories' => $this->limit_categories,
            'limit_products' => $this->limit_products,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
