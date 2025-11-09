<?php

namespace App\Services;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ClientService
{
    public function list(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $q = Client::query()->with('assignedTo');

        $q->visibleTo($user);

        if (!empty($filters['status'])) $q->where('status', $filters['status']);
        if (!empty($filters['assigned_to'])) $q->where('assigned_to', $filters['assigned_to']);
        if (!empty($filters['search'])) $q->search($filters['search']);

        $q->select(['id','name','email','phone','status','assigned_to','last_communication_at']);

        return $q->orderBy('name')->paginate($perPage);
    }

    public function create(array $data, User $actor): Client
    {
        return DB::transaction(function () use ($data, $actor) {
            if (empty($data['assigned_to']) && $actor->isSalesRep()) {
                $data['assigned_to'] = $actor->id;
            }
            $client = Client::create($data);
            return $client;
        });
    }

    public function update(Client $client, array $data, User $actor): Client
    {
        return DB::transaction(function () use ($client, $data, $actor) {
            $original = $client->only(['name','email','phone','status','assigned_to']);
            $client->update($data);

            // AuditLog manually if needed
            // AuditLog::create([...]);

            return $client->fresh();
        });
    }

    public function delete(Client $client): void
    {
        $client->delete();
    }

    public function restore(Client $client): void
    {
        $client->restore();
    }

    /**
     * Bulk assign clients to a user.
     *
     * @param array $clientIds
     * @param int $assignedTo
     * @param User $actor
     * @return Collection
     */
    public function assignBulk(array $clientIds, int $assignedTo, User $actor): Collection
    {
        return DB::transaction(function () use ($clientIds, $assignedTo, $actor) {
            $assignee = User::find($assignedTo);
            if (! $assignee) {
                throw ValidationException::withMessages(['assigned_to' => 'Assigned user not found.']);
            }

            $clients = Client::whereIn('id', $clientIds)->get();
            foreach ($clients as $client) {
                $client->update(['assigned_to' => $assignedTo]);
                // optional: create AuditLog per client
            }
            return $clients;
        });
    }

    /**
     * Bulk restore soft-deleted clients.
     *
     * @param array $clientIds
     * @param User|null $actor
     * @return Collection
     */
    public function restoreBulk(array $clientIds, ?User $actor = null): Collection
    {
        return DB::transaction(function () use ($clientIds, $actor) {
            $clients = Client::withTrashed()->whereIn('id', $clientIds)->get();
            foreach ($clients as $client) {
                if ($client->trashed()) $client->restore();
                // optional audit
            }
            return $clients;
        });
    }
}
