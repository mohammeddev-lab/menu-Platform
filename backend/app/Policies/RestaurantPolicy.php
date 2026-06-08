<?php

namespace App\Policies;

use App\Models\Restaurant;
use App\Models\User;

class RestaurantPolicy
{
    public function before(User $user): ?bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole('super-admin');
    }

    public function view(User $user, Restaurant $restaurant): bool
    {
        return $user->hasRole('super-admin') || ($user->restaurant && $user->restaurant->id === $restaurant->id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super-admin');
    }

    public function update(User $user, Restaurant $restaurant): bool
    {
        return $user->hasRole('super-admin');
    }

    public function updateStatus(User $user, Restaurant $restaurant): bool
    {
        return $user->hasRole('super-admin');
    }

    public function delete(User $user, Restaurant $restaurant): bool
    {
        return $user->hasRole('super-admin');
    }
}
