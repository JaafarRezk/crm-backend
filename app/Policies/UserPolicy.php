<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function update(User $currentUser, User $user)
    {
        return $currentUser->isAdmin();
    }

    public function delete(User $currentUser, User $user)
    {
        return $currentUser->isAdmin();
    }

    public function assignRole(User $currentUser, User $user)
    {
        return $currentUser->isAdmin();
    }
}


