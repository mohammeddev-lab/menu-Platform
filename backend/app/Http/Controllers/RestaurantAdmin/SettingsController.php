<?php

namespace App\Http\Controllers\RestaurantAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantAdmin\UpdateSettingsRequest;
use App\Http\Resources\RestaurantSettingResource;
use App\Models\RestaurantSetting;
use App\Services\ActivityLogger;
use App\Services\QRCodeGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function getSettings(Request $request)
    {
        $restaurant = $request->get('tenant_restaurant');
        return new RestaurantSettingResource($restaurant->settings);
    }

    public function updateSettings(UpdateSettingsRequest $request): JsonResponse
    {
        $restaurant = $request->get('tenant_restaurant');
        $settings = $restaurant->settings;

        $data = $request->only([
            'name_ar', 'name_en', 'contact_email', 'contact_phone',
            'address_ar', 'address_en', 'working_hours_ar', 'working_hours_en',
            'social_links', 'primary_color', 'secondary_color', 'accent_color',
            'button_style', 'typography_selection',
        ]);

        if ($request->hasFile('logo')) {
            if ($settings->logo) {
                $oldPath = str_replace(asset('storage/'), '', $settings->logo);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('logo')->store('brand', 'public');
            $data['logo'] = asset('storage/' . $path);
        }

        if ($request->hasFile('cover_image')) {
            if ($settings->cover_image) {
                $oldPath = str_replace(asset('storage/'), '', $settings->cover_image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('cover_image')->store('brand', 'public');
            $data['cover_image'] = asset('storage/' . $path);
        }

        $settings->update($data);

        ActivityLogger::log('update_settings', 'Updated restaurant settings and branding theme.', $settings->toArray(), $restaurant->id);

        return response()->json([
            'message' => 'Settings updated successfully',
            'settings' => new RestaurantSettingResource($settings->fresh()),
        ]);
    }

    public function getQrCode(Request $request): JsonResponse
    {
        $restaurant = $request->get('tenant_restaurant');
        $menuUrl = config('app.frontend_url', 'http://localhost:3000') . '/' . $restaurant->slug;

        $base64 = QRCodeGenerator::generatePngBase64($menuUrl);

        return response()->json([
            'url' => $menuUrl,
            'qr_code_base64' => $base64,
        ]);
    }

    public function downloadQrPng(Request $request)
    {
        $restaurant = $request->get('tenant_restaurant');
        $menuUrl = config('app.frontend_url', 'http://localhost:3000') . '/' . $restaurant->slug;

        $base64 = QRCodeGenerator::generatePngBase64($menuUrl);
        $data = substr($base64, strpos($base64, ',') + 1);
        $decodedData = base64_decode($data);

        return response($decodedData)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $restaurant->slug . '-menu-qr.png"');
    }

    public function downloadQrPdf(Request $request)
    {
        $restaurant = $request->get('tenant_restaurant');
        $menuUrl = config('app.frontend_url', 'http://localhost:3000') . '/' . $restaurant->slug;
        $name = $restaurant->settings->name_en;

        $pdfOutput = QRCodeGenerator::generatePdf($name, $menuUrl);

        return response($pdfOutput)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $restaurant->slug . '-menu-qr.pdf"');
    }
}
