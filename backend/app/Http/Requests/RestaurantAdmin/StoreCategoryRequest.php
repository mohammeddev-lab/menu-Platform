<?php

namespace App\Http\Requests\RestaurantAdmin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'icon_type' => 'nullable|string|in:lucide,emoji,image',
            'icon_value' => 'nullable|string|max:500',
            'icon_image' => 'nullable|mimes:jpg,jpeg,png,webp,svg|max:5120',
        ];
    }
}
