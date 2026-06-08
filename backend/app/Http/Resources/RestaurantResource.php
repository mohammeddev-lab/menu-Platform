<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'slug' => $this->slug,
            'status' => $this->status,
            'owner' => new UserResource($this->whenLoaded('owner')),
            'settings' => new RestaurantSettingResource($this->whenLoaded('settings')),
            'active_subscription' => new SubscriptionResource($this->whenLoaded('activeSubscription')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
