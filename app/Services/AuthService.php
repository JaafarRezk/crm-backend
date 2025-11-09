<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Carbon\Carbon;

class AuthService
{
    protected int $maxAttempts = 5;
    protected int $lockMinutes = 15;

    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'], // mutator will hash
                'phone' => $data['phone'] ?? null,
                'user_type' => $data['user_type'] ?? 'sales_rep', // default system user type
            ]);

            $token = JWTAuth::fromUser($user);

            return [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ];
        });
    }

    public function createByAdmin(array $data, User $admin): User
    {
        if (! $admin->isAdmin()) {
            throw ValidationException::withMessages(['user' => ['Unauthorized']]);
        }

        return DB::transaction(function () use ($data) {
            return User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => $data['phone'] ?? null,
                'user_type' => $data['user_type'],
            ]);
        });
    }

    public function login(array $credentials): array
    {
        // find user by email
        $user = User::where('email', $credentials['email'])->first();

        if ($user) {
            // check if locked
            if ($user->locked_until && Carbon::now()->lessThan($user->locked_until)) {
                throw ValidationException::withMessages([
                    'email' => ["Account locked until {$user->locked_until}"],
                ]);
            }
        }

        if (! $token = JWTAuth::attempt($credentials)) {
            if ($user) {
                $user->increment('failed_login_attempts');
                if ($user->failed_login_attempts >= $this->maxAttempts) {
                    $user->update([
                        'locked_until' => Carbon::now()->addMinutes($this->lockMinutes),
                        'failed_login_attempts' => 0,
                    ]);
                }
            }
            throw ValidationException::withMessages(['email' => ['Invalid credentials provided.']]);
        }

        // success: reset failed attempts and set last_login
        $user = JWTAuth::user();
        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login' => Carbon::now(),
        ]);

        return [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ];
    }

    public function logout(): void
    {
        try {
            $token = JWTAuth::getToken();
            if ($token) {
                JWTAuth::invalidate($token);
            }
        } catch (JWTException $e) {
            throw new \RuntimeException('Failed to logout: ' . $e->getMessage());
        }
    }

    public function currentUser(): ?User
    {
        return JWTAuth::user();
    }

    public function refreshToken(): array
    {
        try {
            $new = JWTAuth::refresh(JWTAuth::getToken());
            return [
                'access_token' => $new,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ];
        } catch (JWTException $e) {
            throw new \RuntimeException('Failed to refresh token: ' . $e->getMessage());
        }
    }
}
