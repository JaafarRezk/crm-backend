<?php

namespace App\Services;

use App\Models\Communication;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CommunicationService
{
    public function list(Client $client, array $filters = [])
    {
        $q = $client->communications()->with('creator')->orderByDesc('date');

        if (!empty($filters['type'])) {
            $q->where('type', $filters['type']);
        }
        if (!empty($filters['from'])) {
            $q->whereDate('date', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $q->whereDate('date', '<=', $filters['to']);
        }

        return $q->get();
    }

    public function create(Client $client, User $actor, array $data): Communication
    {
        return DB::transaction(function () use ($client, $actor, $data) {
            $comm = $client->communications()->create([
                'created_by' => $actor->id,
                'type' => $data['type'],
                'date' => $data['date'] ?? now(),
                'notes' => $data['notes'] ?? null,
            ]);

            return $comm->load('creator');
        });
    }

    public function show(Client $client, int $commId): Communication
    {
        return $client->communications()->withTrashed()->with('creator')->findOrFail($commId);
    }

    public function update(Client $client, int $commId, array $data): Communication
    {
        return DB::transaction(function () use ($client, $commId, $data) {
            $comm = $client->communications()->withTrashed()->findOrFail($commId);
            $comm->update($data);
            return $comm->fresh('creator');
        });
    }

    public function delete(Client $client, int $commId): void
    {
        $comm = $client->communications()->findOrFail($commId);
        $comm->delete(); // will soft delete
    }

    public function restore(Client $client, int $commId): Communication
    {
        $comm = $client->communications()->withTrashed()->findOrFail($commId);
        if (method_exists($comm, 'restore')) {
            $comm->restore();
        }
        return $comm->fresh('creator');
    }
}


