<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRestaurantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'owner_id' => 'required|exists:users,id',
            'slug' => 'required|string|alpha_dash|max:50|unique:restaurants,slug',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'plan_id' => 'required|exists:subscription_plans,id',
        ];
    }
}
