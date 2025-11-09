<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class Communication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'created_by',
        'type', // call|email|meeting
        'date',
        'notes',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    // Relations
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    // Hooks: created/updated/deleted
    protected static function booted()
    {
        static::created(function (Communication $comm) {
            // use a transaction to safely update client + audit
            DB::transaction(function () use ($comm) {
                try {
                    // lock the client row to avoid race-condition on last_communication_at
                    $client = $comm->client()->lockForUpdate()->first();
                    if ($client) {
                        $client->last_communication_at = $comm->date ?? now();
                        $client->save();
                    }

                    // audit log (assumes AuditLog model exists)
                    if (class_exists(\App\Models\AuditLog::class)) {
                        \App\Models\AuditLog::create([
                            'actor_id' => optional($comm->creator)->id ?: optional(auth()->user())->id,
                            'resource_type' => 'Communication',
                            'resource_id' => $comm->id,
                            'action' => 'created',
                            'changes' => json_encode($comm->toArray()),
                        ]);
                    }

                    // dispatch event if exists
                    if (class_exists(\App\Events\CommunicationCreated::class)) {
                        Event::dispatch(new \App\Events\CommunicationCreated($comm));
                    }
                } catch (\Throwable $e) {
                    Log::error('Communication.created hook failed', ['err' => $e->getMessage(), 'comm_id' => $comm->id]);
                }
            });
        });

        static::updated(function (Communication $comm) {
            try {
                if (class_exists(\App\Models\AuditLog::class)) {
                    \App\Models\AuditLog::create([
                        'actor_id' => optional(auth()->user())->id,
                        'resource_type' => 'Communication',
                        'resource_id' => $comm->id,
                        'action' => 'updated',
                        'changes' => json_encode($comm->getChanges()),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Failed to create audit log for communication update', ['err' => $e->getMessage()]);
            }
        });

        static::deleted(function (Communication $comm) {
            try {
                if (class_exists(\App\Models\AuditLog::class)) {
                    \App\Models\AuditLog::create([
                        'actor_id' => optional(auth()->user())->id,
                        'resource_type' => 'Communication',
                        'resource_id' => $comm->id,
                        'action' => 'deleted',
                        'changes' => json_encode($comm->toArray()),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Failed to create audit log for communication deleted', ['err' => $e->getMessage()]);
            }
        });
    }
}
