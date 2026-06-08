<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
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

    public function view(User $user, User $target): bool
    {
        return $user->hasRole('super-admin') || $user->id === $target->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super-admin');
    }

    public function update(User $user, User $target): bool
    {
        return $user->hasRole('super-admin');
    }

    public function resetPassword(User $user, User $target): bool
    {
        return $user->hasRole('super-admin');
    }

    public function toggleStatus(User $user, User $target): bool
    {
        return $user->hasRole('super-admin');
    }

    public function delete(User $user, User $target): bool
    {
        return $user->hasRole('super-admin') && $user->id !== $target->id;
    }
}
