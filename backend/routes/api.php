<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Customer\MenuController;
use App\Http\Controllers\RestaurantAdmin\AnalyticsController as RestaurantAnalyticsController;
use App\Http\Controllers\RestaurantAdmin\CategoryController as RestaurantCategoryController;
use App\Http\Controllers\RestaurantAdmin\DashboardController as RestaurantDashboardController;
use App\Http\Controllers\RestaurantAdmin\OfferController as RestaurantOfferController;
use App\Http\Controllers\RestaurantAdmin\ProductController as RestaurantProductController;
use App\Http\Controllers\RestaurantAdmin\SettingsController as RestaurantSettingsController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperDashboardController;
use App\Http\Controllers\SuperAdmin\RestaurantController as SuperRestaurantController;
use App\Http\Controllers\SuperAdmin\SubscriptionPlanController as SuperPlanController;
use App\Http\Controllers\SuperAdmin\UserController as SuperUserController;
use App\Http\Controllers\SuperAdmin\SettingController as SuperSettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Customer & Auth Routes
|--------------------------------------------------------------------------
*/
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Public menu lookup
Route::get('/menus/{slug}', [MenuController::class, 'getMenu'])->middleware('check.suspended');
Route::post('/products/{product}/view', [MenuController::class, 'logProductView'])->middleware('check.suspended');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    /*
     * Super Admin Scope
     */
    Route::prefix('super-admin')->middleware(['role:super-admin'])->group(function () {
        Route::get('/dashboard', [SuperDashboardController::class, 'index']);
        
        Route::get('/restaurants', [SuperRestaurantController::class, 'index']);
        Route::post('/restaurants', [SuperRestaurantController::class, 'store']);
        Route::get('/restaurants/{restaurant}', [SuperRestaurantController::class, 'show']);
        Route::post('/restaurants/{restaurant}/status', [SuperRestaurantController::class, 'updateStatus']);
        Route::delete('/restaurants/{restaurant}', [SuperRestaurantController::class, 'destroy']);

        Route::apiResource('/plans', SuperPlanController::class);

        Route::get('/users', [SuperUserController::class, 'index']);
        Route::post('/users/{user}/reset-password', [SuperUserController::class, 'resetPassword']);
        Route::post('/users/{user}/status', [SuperUserController::class, 'toggleStatus']);

        Route::get('/settings', [SuperSettingController::class, 'getSettings']);
        Route::post('/settings', [SuperSettingController::class, 'updateSettings']);
        Route::get('/logs', [SuperSettingController::class, 'getActivityLogs']);
    });

    /*
     * Restaurant Admin Scope (Scoped to Tenant)
     */
    Route::prefix('restaurant-admin')->middleware(['role:restaurant-admin', 'tenant.scope'])->group(function () {
        Route::get('/dashboard', [RestaurantDashboardController::class, 'index']);
        Route::get('/analytics', [RestaurantAnalyticsController::class, 'getAnalytics']);

        // Settings & QR
        Route::get('/settings', [RestaurantSettingsController::class, 'getSettings']);
        Route::post('/settings', [RestaurantSettingsController::class, 'updateSettings']);
        Route::get('/settings/qr', [RestaurantSettingsController::class, 'getQrCode']);
        Route::get('/settings/qr/png', [RestaurantSettingsController::class, 'downloadQrPng']);
        Route::get('/settings/qr/pdf', [RestaurantSettingsController::class, 'downloadQrPdf']);

        // Categories & Reordering
        Route::post('/categories/reorder', [RestaurantCategoryController::class, 'reorder']);
        Route::apiResource('/categories', RestaurantCategoryController::class);

        // Products & Reordering
        Route::post('/products/reorder', [RestaurantProductController::class, 'reorder']);
        Route::apiResource('/products', RestaurantProductController::class);

        // Offers
        Route::apiResource('/offers', RestaurantOfferController::class);
    });
});

