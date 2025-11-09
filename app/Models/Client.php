<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'status', // New|Active|Hot|Inactive
        'assigned_to',
        'last_communication_at',
    ];

    protected $casts = [
        'last_communication_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relations
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to', 'id');
    }

    public function communications(): HasMany
    {
        return $this->hasMany(Communication::class, 'client_id', 'id');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'client_id', 'id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'resource_id', 'id')
                    ->where('resource_type', 'Client');
    }

    // Scopes
    public function scopeVisibleTo($query, ?User $user)
    {
        if (!$user) {
            return $query->whereRaw('0 = 1');
        }

        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isManager()) {
            // Manager sees all for now — implement team logic if needed
            return $query;
        }

        if ($user->isSalesRep()) {
            return $query->where('assigned_to', $user->id);
        }

        return $query->whereRaw('0 = 1');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) return $query;
        $term = '%' . trim($term) . '%';
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', $term)
              ->orWhere('email', 'like', $term)
              ->orWhere('phone', 'like', $term);
        });
    }

    public function scopeNeedsFollowUpToday($query)
    {
        $today = now()->toDateString();

        return $query->where(function ($q) use ($today) {
            $q->whereHas('followUps', function ($q2) use ($today) {
                $q2->whereDate('due_date', $today)->where('status', 'pending');
            })
            ->orWhere(function ($q3) use ($today) {
                $q3->whereNull('last_communication_at')
                   ->orWhere('last_communication_at', '<', now()->subDays(30));
            });
        });
    }

    // Audit hooks (lightweight)
    protected static function booted()
    {
        static::created(function (Client $client) {
            try {
                AuditLog::create([
                    'actor_id' => optional(Auth::user())->id,
                    'resource_type' => 'Client',
                    'resource_id' => $client->id,
                    'action' => 'created',
                    'changes' => json_encode($client->toArray()),
                ]);
            } catch (\Throwable $e) {
                Log::error('Client created audit failed', ['err' => $e->getMessage()]);
            }
        });

        static::updated(function (Client $client) {
            try {
                AuditLog::create([
                    'actor_id' => optional(Auth::user())->id,
                    'resource_type' => 'Client',
                    'resource_id' => $client->id,
                    'action' => 'updated',
                    'changes' => json_encode($client->getChanges()),
                ]);
            } catch (\Throwable $e) {
                Log::error('Client updated audit failed', ['err' => $e->getMessage()]);
            }
        });

        static::deleted(function (Client $client) {
            try {
                AuditLog::create([
                    'actor_id' => optional(Auth::user())->id,
                    'resource_type' => 'Client',
                    'resource_id' => $client->id,
                    'action' => 'deleted',
                    'changes' => json_encode($client->toArray()),
                ]);
            } catch (\Throwable $e) {
                Log::error('Client deleted audit failed', ['err' => $e->getMessage()]);
            }
        });
    }
}
