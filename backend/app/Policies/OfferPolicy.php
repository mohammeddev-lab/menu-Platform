<?php

namespace App\Policies;

use App\Models\Offer;
use App\Models\User;

class OfferPolicy
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
        return true;
    }

    public function view(User $user, Offer $offer): bool
    {
        return $user->hasRole('super-admin') || ($user->restaurant && $user->restaurant->id === $offer->restaurant_id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('restaurant-admin');
    }

    public function update(User $user, Offer $offer): bool
    {
        return $user->hasRole('restaurant-admin') && $user->restaurant && $user->restaurant->id === $offer->restaurant_id;
    }

    public function delete(User $user, Offer $offer): bool
    {
        return $user->hasRole('restaurant-admin') && $user->restaurant && $user->restaurant->id === $offer->restaurant_id;
    }
}
