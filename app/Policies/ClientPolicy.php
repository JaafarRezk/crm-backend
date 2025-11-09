<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Client;

class ClientPolicy
{
    public function view(User $user, Client $client): bool
    {
        return $user->isAdmin() || $user->isManager() || $user->id === $client->assigned_to;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager() || $user->isSalesRep();
    }

    public function update(User $user, Client $client): bool
    {
        if ($user->isAdmin() || $user->isManager()) return true;
        return $user->isSalesRep() && $user->id === $client->assigned_to;
    }

    public function delete(User $user, Client $client): bool
    {
        // Admins can delete, managers if they own the client (team logic omitted)
        return $user->isAdmin() || ($user->isManager() && $user->id === $client->assigned_to);
    }

    public function restore(User $user, Client $client): bool
    {
        return $user->isAdmin();
    }

    public function assignBulk(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function restoreBulk(User $user): bool
    {
        return $user->isAdmin();
    }
}
