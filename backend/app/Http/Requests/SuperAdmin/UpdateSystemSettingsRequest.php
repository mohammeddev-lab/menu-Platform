<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'platform_name' => 'required|string|max:255',
            'smtp_host' => 'nullable|string',
            'smtp_port' => 'nullable|string',
            'storage_driver' => 'required|in:local,s3',
            'maintenance_mode' => 'required|boolean',
        ];
    }
}
