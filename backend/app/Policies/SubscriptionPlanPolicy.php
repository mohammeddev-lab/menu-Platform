<?php

namespace App\Policies;

use App\Models\SubscriptionPlan;
use App\Models\User;

class SubscriptionPlanPolicy
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

    public function view(User $user, SubscriptionPlan $plan): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super-admin');
    }

    public function update(User $user, SubscriptionPlan $plan): bool
    {
        return $user->hasRole('super-admin');
    }

    public function delete(User $user, SubscriptionPlan $plan): bool
    {
        return $user->hasRole('super-admin');
    }
}
