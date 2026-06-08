<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'restaurant_id' => $this->restaurant_id,
            'action' => $this->action,
            'description' => $this->description,
            'ip_address' => $this->ip_address,
            'payload' => $this->payload,
            'user' => new UserResource($this->whenLoaded('user')),
            'restaurant' => $this->whenLoaded('restaurant', function () {
                return [
                    'slug' => $this->restaurant->slug,
                    'name_en' => $this->restaurant->settings?->name_en,
                ];
            }),
            'created_at' => $this->created_at,
        ];
    }
}
