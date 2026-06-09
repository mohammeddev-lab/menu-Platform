<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\UpdateSystemSettingsRequest;
use App\Http\Resources\ActivityLogResource;
use App\Models\ActivityLog;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function getSettings()
    {
        $settings = Cache::get('system_settings', [
            'platform_name' => 'Menu Platform',
            'platform_logo' => '',
            'smtp_host' => 'smtp.mailtrap.io',
            'smtp_port' => '2525',
            'smtp_username' => '',
            'storage_driver' => 'local',
            'maintenance_mode' => false,
        ]);

        return response()->json($settings);
    }

    public function updateSettings(UpdateSystemSettingsRequest $request): JsonResponse
    {
        $settings = $request->only([
            'platform_name', 'platform_logo', 'smtp_host', 'smtp_port',
            'smtp_username', 'smtp_password', 'storage_driver', 'maintenance_mode',
        ]);

        Cache::forever('system_settings', $settings);

        ActivityLogger::log('update_system_settings', 'Super admin updated global system settings.', $settings);

        return response()->json([
            'message' => 'Settings updated successfully',
            'settings' => $settings,
        ]);
    }

    public function getActivityLogs()
    {
        $logs = ActivityLog::with(['user.roles', 'restaurant.settings'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return ActivityLogResource::collection($logs);
    }
}
