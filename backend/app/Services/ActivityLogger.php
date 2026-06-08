<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log(string $action, string $description, ?array $payload = null, ?int $restaurantId = null, ?int $userId = null): void
    {
        ActivityLog::create([
            'user_id' => $userId ?? auth()->id(),
            'restaurant_id' => $restaurantId ?? (auth()->check() && auth()->user()->restaurant ? auth()->user()->restaurant->id : null),
            'action' => $action,
            'description' => $description,
            'ip_address' => Request::ip(),
            'payload' => $payload,
        ]);
    }
}
