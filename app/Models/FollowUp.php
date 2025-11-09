<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class FollowUp extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'client_id',
        'assigned_to',
        'created_by',
        'due_date',
        'status', // pending|completed|cancelled
        'notes',
        'priority',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    // Relations
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    // Scopes
    public function scopeDueOn($query, $date)
    {
        return $query->whereDate('due_date', $date);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // Audit hooks (lightweight - supplement service-level audit)
    protected static function booted()
    {
        static::created(function (FollowUp $f) {
            try {
                AuditLog::create([
                    'actor_id' => optional(auth()->user())->id,
                    'resource_type' => 'follow_up',
                    'resource_id' => $f->id,
                    'action' => 'created',
                    'changes' => json_encode($f->toArray()),
                ]);
            } catch (\Throwable $e) {
                Log::error('FollowUp created audit failed', ['err' => $e->getMessage()]);
            }
        });

        static::deleted(function (FollowUp $f) {
            try {
                AuditLog::create([
                    'actor_id' => optional(auth()->user())->id,
                    'resource_type' => 'follow_up',
                    'resource_id' => $f->id,
                    'action' => 'deleted',
                    'changes' => json_encode($f->toArray()),
                ]);
            } catch (\Throwable $e) {
                Log::error('FollowUp deleted audit failed', ['err' => $e->getMessage()]);
            }
        });
    }
}
