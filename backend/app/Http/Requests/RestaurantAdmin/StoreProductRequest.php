<?php

namespace App\Http\Requests\RestaurantAdmin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'image' => 'nullable|image|max:5120',
            'is_available' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'is_recommended' => 'nullable|boolean',
            'tags' => 'nullable|array',
        ];
    }
}
