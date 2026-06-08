<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'restaurant_name_ar' => 'required|string|max:255',
            'restaurant_name_en' => 'required|string|max:255',
            'restaurant_slug' => 'required|string|alpha_dash|max:50|unique:restaurants,slug',
        ];
    }
}
