<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'logo' => $this->logo,
            'cover_image' => $this->cover_image,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'address_ar' => $this->address_ar,
            'address_en' => $this->address_en,
            'working_hours_ar' => $this->working_hours_ar,
            'working_hours_en' => $this->working_hours_en,
            'social_links' => $this->social_links,
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'accent_color' => $this->accent_color,
            'button_style' => $this->button_style,
            'typography_selection' => $this->typography_selection,
        ];
    }
}
