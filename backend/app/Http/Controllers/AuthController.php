<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Restaurant;
use App\Models\RestaurantSetting;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole('restaurant-admin');

            $restaurant = Restaurant::create([
                'user_id' => $user->id,
                'slug' => Str::slug($request->restaurant_slug),
                'status' => 'active',
            ]);

            RestaurantSetting::create([
                'restaurant_id' => $restaurant->id,
                'name_ar' => $request->restaurant_name_ar,
                'name_en' => $request->restaurant_name_en,
                'primary_color' => '#1a3c2f',
                'secondary_color' => '#c8a97e',
                'accent_color' => '#f5f0e8',
                'button_style' => 'rounded-full',
                'typography_selection' => 'Cairo',
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            ActivityLogger::log('register', 'New tenant registered: ' . $request->restaurant_name_en, null, $restaurant->id, $user->id);

            return response()->json([
                'user' => new UserResource($user),
                'token' => $token,
                'restaurant_slug' => $restaurant->slug,
            ], 201);
        });
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 422);
        }

        $restaurant = $user->restaurant;

        if ($user->hasRole('restaurant-admin') && $restaurant && $restaurant->status === 'suspended') {
            return response()->json(['error' => 'Your restaurant has been suspended. Please contact support.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        ActivityLogger::log('login', 'User logged in successfully.', null, $restaurant?->id, $user->id);

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
            'restaurant_slug' => $restaurant?->slug,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        ActivityLogger::log('logout', 'User logged out.');

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => new UserResource($request->user()),
            'restaurant_slug' => $request->user()->restaurant?->slug,
        ]);
    }
}
