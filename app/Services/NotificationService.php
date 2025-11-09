<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;

class NotificationService
{
    public function forUser($user, array $params = []): LengthAwarePaginator
    {
        $perPage = (int) ($params['per_page'] ?? 15);
        $q = $user->notifications()->orderBy('created_at', 'desc');
        // optional filters: unread only, type, etc.
        if (!empty($params['unread'])) {
            $q->whereNull('read_at');
        }
        if (!empty($params['type'])) {
            $q->where('type', $params['type']);
        }
        return $q->paginate($perPage);
    }

    public function markRead($user, array $ids = []): void
    {
        if (empty($ids)) return;
        $user->unreadNotifications()->whereIn('id', $ids)->get()->each->markAsRead();
    }

    public function markAllRead($user): void
    {
        $user->unreadNotifications->each->markAsRead();
    }
}
