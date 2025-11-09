<?php

namespace App\Services;

use App\Models\FollowUp;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\FollowUpAssignedNotification;
use App\Notifications\FollowUpCompletedNotification;
use Illuminate\Validation\ValidationException;

class FollowUpService
{
    /**
     * List follow-ups for a user (admin sees all).
     *
     * @param User $user
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function list(User $user, array $filters = [])
    {
        $q = FollowUp::with(['client', 'assignedTo', 'creator']);

        if ($user->isSalesRep()) {
            $q->where('assigned_to', $user->id);
        }

        // search across notes and related client name
        if (!empty($filters['search'])) {
            $term = trim($filters['search']);
            $q->where(function ($sub) use ($term) {
                $sub->where('notes', 'like', "%{$term}%")
                    ->orWhereHas('client', function ($c) use ($term) {
                        $c->where('name', 'like', "%{$term}%");
                    });
            });
        }

        if (!empty($filters['assigned_to'])) {
            $q->where('assigned_to', $filters['assigned_to']);
        }

        if (!empty($filters['due_date'])) {
            $q->whereDate('due_date', $filters['due_date']);
        }

        if (!empty($filters['status'])) {
            $q->where('status', $filters['status']);
        }

        $q->orderBy('due_date', 'asc');

        // pagination support: default per_page 15
        $perPage = (int)($filters['per_page'] ?? 15);
        if (!empty($filters['page'])) {
            return $q->paginate($perPage);
        }

        // if caller didn't request page, still return paginator for consistency
        return $q->paginate($perPage);
    }
    /**
     * Create a follow-up.
     *
     * @param array $data
     * @param User $actor
     * @return FollowUp
     * @throws \Throwable
     */
    public function create(array $data, User $actor): FollowUp
    {
        return DB::transaction(function () use ($data, $actor) {
            // ensure client exists
            $client = Client::findOrFail($data['client_id']);

            $follow = FollowUp::create(array_merge($data, ['created_by' => $actor->id]));

            // Audit log (service-level)
            AuditLog::create([
                'actor_id' => $actor->id,
                'action' => 'create',
                'resource_type' => 'follow_up',
                'resource_id' => $follow->id,
                'changes' => json_encode($follow->toArray()),
            ]);

            // notify assignee if provided
            if (!empty($follow->assigned_to) && class_exists(FollowUpAssignedNotification::class)) {
                $assignee = $follow->assignedTo;
                if ($assignee) {
                    Notification::send($assignee, new FollowUpAssignedNotification($follow));
                }
            }

            return $follow->fresh()->load(['client', 'assignedTo', 'creator']);
        });
    }

    /**
     * Update follow-up with audit logging and notifications.
     *
     * @param FollowUp $follow
     * @param array $data
     * @param User $actor
     * @return FollowUp
     * @throws \Throwable
     */
    public function update(FollowUp $follow, array $data, User $actor): FollowUp
    {
        return DB::transaction(function () use ($follow, $data, $actor) {

            $original = $follow->only(['assigned_to', 'due_date', 'status', 'priority', 'notes']);

            // validate assigned_to if provided
            if (array_key_exists('assigned_to', $data) && $data['assigned_to']) {
                $assignee = User::find($data['assigned_to']);
                if (! $assignee) {
                    throw ValidationException::withMessages(['assigned_to' => 'Assigned user not found.']);
                }
            }

            $follow->update($data);

            // handle completed status transitions
            if (array_key_exists('status', $data) && $data['status'] === FollowUp::STATUS_COMPLETED) {
                $follow->update([
                    'completed_by' => $actor->id,
                    'completed_at' => now(),
                ]);
                // optional: notify creator or manager
                if (class_exists(FollowUpCompletedNotification::class)) {
                    Notification::send($follow->creator, new FollowUpCompletedNotification($follow));
                }
            } elseif (array_key_exists('status', $data) && $data['status'] !== FollowUp::STATUS_COMPLETED) {
                if ($follow->completed_at && $data['status'] !== FollowUp::STATUS_COMPLETED) {
                    $follow->update([
                        'completed_by' => null,
                        'completed_at' => null,
                    ]);
                }
            }

            // detect changes
            $current = $follow->only(array_keys($original));
            $changes = $this->detectChanges($original, $current);

            if (! empty($changes)) {
                AuditLog::create([
                    'actor_id' => $actor->id,
                    'action' => 'update',
                    'resource_type' => 'follow_up',
                    'resource_id' => $follow->id,
                    'changes' => json_encode($changes),
                ]);
            }

            // notify new assignee if changed
            if (array_key_exists('assigned_to', $data) && ($original['assigned_to'] != $follow->assigned_to)) {
                if ($follow->assignedTo && class_exists(FollowUpAssignedNotification::class)) {
                    Notification::send($follow->assignedTo, new FollowUpAssignedNotification($follow->fresh()));
                }
            }

            return $follow->fresh()->load(['client', 'assignedTo', 'creator', 'completedBy']);
        });
    }

    /**
     * Delete (soft) a follow-up.
     *
     * @param int|FollowUp $idOrModel
     * @param User|null $actor
     * @return void
     */
    public function delete($idOrModel, ?User $actor = null): void
    {
        $follow = $idOrModel instanceof FollowUp ? $idOrModel : FollowUp::findOrFail($idOrModel);
        $follow->delete();

        AuditLog::create([
            'actor_id' => $actor?->id,
            'action' => 'delete',
            'resource_type' => 'follow_up',
            'resource_id' => $follow->id,
            'changes' => json_encode($follow->toArray()),
        ]);
    }

    /**
     * Restore a soft-deleted follow-up.
     *
     * @param int $id
     * @param User|null $actor
     * @return FollowUp
     */
    public function restore(int $id, ?User $actor = null): FollowUp
    {
        $follow = FollowUp::withTrashed()->findOrFail($id);
        $follow->restore();

        AuditLog::create([
            'actor_id' => $actor?->id,
            'action' => 'restore',
            'resource_type' => 'follow_up',
            'resource_id' => $follow->id,
            'changes' => null,
        ]);

        return $follow->fresh()->load(['client', 'assignedTo', 'creator']);
    }

    /**
     * Mark a follow-up complete.
     *
     * @param int $id
     * @param User $actor
     * @return FollowUp
     */
    public function markComplete(int $id, User $actor): FollowUp
    {
        $follow = FollowUp::findOrFail($id);
        $oldStatus = $follow->status;

        $follow->update([
            'status' => FollowUp::STATUS_COMPLETED,
            'completed_by' => $actor->id,
            'completed_at' => now(),
        ]);

        AuditLog::create([
            'actor_id' => $actor->id,
            'action' => 'complete',
            'resource_type' => 'follow_up',
            'resource_id' => $follow->id,
            'changes' => json_encode(['status' => ['old' => $oldStatus, 'new' => FollowUp::STATUS_COMPLETED]]),
        ]);

        // notify creator
        if (class_exists(FollowUpCompletedNotification::class)) {
            Notification::send($follow->creator, new FollowUpCompletedNotification($follow));
        }

        return $follow->fresh()->load(['client', 'assignedTo', 'creator', 'completedBy']);
    }

    /**
     * Mark a follow-up cancelled.
     *
     * @param int $id
     * @param User $actor
     * @return FollowUp
     */
    public function markCancelled(int $id, User $actor): FollowUp
    {
        $follow = FollowUp::findOrFail($id);
        $oldStatus = $follow->status;

        $follow->update(['status' => FollowUp::STATUS_CANCELLED]);

        AuditLog::create([
            'actor_id' => $actor->id,
            'action' => 'cancel',
            'resource_type' => 'follow_up',
            'resource_id' => $follow->id,
            'changes' => json_encode(['status' => ['old' => $oldStatus, 'new' => FollowUp::STATUS_CANCELLED]]),
        ]);

        return $follow->fresh()->load(['client', 'assignedTo', 'creator']);
    }

    /**
     * Follow-ups assigned to a user (for "my tasks").
     *
     * @param User $user
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function forUser(User $user, array $filters = [])
    {
        $q = FollowUp::with(['client', 'assignedTo', 'creator', 'completedBy'])
            ->where('assigned_to', $user->id);

        if (!empty($filters['status'])) {
            $q->where('status', $filters['status']);
        }
        if (!empty($filters['due_date'])) {
            $q->whereDate('due_date', $filters['due_date']);
        }


        return $q->orderBy('due_date')->paginate(15); // pagination recommended
    }


    protected function detectChanges(array $original, array $current): array
    {
        $diff = [];
        foreach ($current as $k => $v) {
            $old = $original[$k] ?? null;
            $new = $v ?? null;
            if ((string)($old ?? '') !== (string)($new ?? '')) {
                $diff[$k] = ['old' => $old, 'new' => $new];
            }
        }
        return $diff;
    }
}
