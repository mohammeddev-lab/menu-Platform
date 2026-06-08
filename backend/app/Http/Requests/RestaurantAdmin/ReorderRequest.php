<?php

namespace App\Http\Requests\RestaurantAdmin;

use Illuminate\Foundation\Http\FormRequest;

class ReorderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order' => 'required|array',
            'order.*.id' => 'required|integer',
            'order.*.order' => 'required|integer',
        ];
    }
}
