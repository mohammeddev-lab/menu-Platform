<?php

namespace App\Http\Requests\RestaurantAdmin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', function ($attribute, $value, $fail) {
                $restaurant = request()->get('tenant_restaurant');
                if (!$restaurant || !$restaurant->categories()->where('id', $value)->exists()) {
                    $fail('The selected category does not belong to your restaurant.');
                }
            }],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_available' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'is_recommended' => 'nullable|boolean',
            'tags' => 'nullable|array',
        ];
    }
}
