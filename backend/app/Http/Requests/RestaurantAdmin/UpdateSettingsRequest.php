<?php

namespace App\Http\Requests\RestaurantAdmin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,webp|max:4096',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
            'address_ar' => 'nullable|string',
            'address_en' => 'nullable|string',
            'working_hours_ar' => 'nullable|array',
            'working_hours_en' => 'nullable|array',
            'social_links' => 'nullable|array',
            'primary_color' => 'required|string|max:20',
            'secondary_color' => 'required|string|max:20',
            'accent_color' => 'required|string|max:20',
            'button_style' => 'required|string|max:50',
            'typography_selection' => 'required|string|max:50',
        ];
    }
}
