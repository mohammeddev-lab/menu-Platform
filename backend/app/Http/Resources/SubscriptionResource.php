<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'subscription_plan_id' => $this->subscription_plan_id,
            'status' => $this->status,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'plan' => new SubscriptionPlanResource($this->whenLoaded('plan')),
            'is_valid' => $this->isValid(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
