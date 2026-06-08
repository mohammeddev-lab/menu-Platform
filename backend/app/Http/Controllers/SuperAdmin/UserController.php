<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\ResetUserPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $owners = User::role('restaurant-admin')->with('restaurant')->get();
        return UserResource::collection($owners);
    }

    public function resetPassword(ResetUserPasswordRequest $request, User $user): JsonResponse
    {
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        ActivityLogger::log('reset_password', "Super admin reset password for user: {$user->email}", null, null, $user->id);

        return response()->json(['message' => 'Password reset successfully']);
    }

    public function toggleStatus(User $user): JsonResponse
    {
        $restaurant = $user->restaurant;
        if ($restaurant) {
            $newStatus = $restaurant->status === 'suspended' ? 'active' : 'suspended';
            $restaurant->update(['status' => $newStatus]);

            ActivityLogger::log('toggle_user_status', "Super admin toggled status of owner: {$user->email} (Restaurant slug: {$restaurant->slug}) to {$newStatus}.");

            return response()->json(['message' => "Account associated restaurant status toggled to {$newStatus}.", 'status' => $newStatus]);
        }

        return response()->json(['error' => 'No restaurant associated with this user.'], 422);
    }
}
