<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes,HasRoles;


    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'user_type', // 'admin'|'manager'|'sales_rep'
        'last_login',
        'failed_login_attempts',
        'locked_until',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'last_login' => 'datetime',
        'locked_until' => 'datetime',
    ];

    public function assignedClients(): HasMany
    {
        return $this->hasMany(Client::class, 'assigned_to', 'id');
    }

    public function communicationsCreated(): HasMany
    {
        return $this->hasMany(Communication::class, 'created_by', 'id');
    }

    public function followUpsAssigned(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'assigned_to', 'id');
    }

    public function followUpsCreated(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'created_by', 'id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'actor_id', 'id');
    }

    // -------------------------
    // Helpers / role checks
    // -------------------------

    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    public function isManager(): bool
    {
        return $this->user_type === 'manager';
    }

    public function isSalesRep(): bool
    {
        return $this->user_type === 'sales_rep';
    }

    // -------------------------
    // mutators
    // -------------------------

    /**
     * Hash password automatically when set (unless already hashed)
     */
    public function setPasswordAttribute($value): void
    {
        if (empty($value)) {
            return;
        }

        if (Hash::needsRehash($value)) {
            $this->attributes['password'] = Hash::make($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }

    // -------------------------
    // JWTSubject implementation
    // -------------------------

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
