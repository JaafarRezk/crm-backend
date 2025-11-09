<?php

namespace App\Policies;

use App\Models\User;
use App\Models\FollowUp;

class FollowUpPolicy
{
    public function view(User $user, FollowUp $followUp): bool
    {
        return $user->isAdmin() ||
               $user->id === $followUp->assigned_to ||
               $user->id === $followUp->created_by;
    }

    public function update(User $user, FollowUp $followUp): bool
    {
        return $user->isAdmin() ||
               $user->id === $followUp->assigned_to ||
               $user->id === $followUp->created_by;
    }

    public function delete(User $user, FollowUp $followUp): bool
    {
        return $user->isAdmin() || $user->id === $followUp->created_by;
    }

    public function restore(User $user, FollowUp $followUp): bool
    {
        return $user->isAdmin();
    }

    public function markComplete(User $user, FollowUp $followUp): bool
    {
        return $user->isAdmin() || $user->id === $followUp->assigned_to;
    }
}
