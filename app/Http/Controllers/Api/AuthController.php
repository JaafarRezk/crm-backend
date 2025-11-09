<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminCreateUserRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;
use App\Services\AuthService;
use Throwable;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private AuthService $authService)
    {
    }

    public function register(RegisterRequest $request)
    {
        try {
            $result = $this->authService->register($request->validated());
            return $this->success($result, 'Registration successful', 201);
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->login($request->validated());
            return $this->success($result, 'Login successful');
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function logout()
    {
        try {
            $this->authService->logout();
            return $this->success(null, 'Logged out successfully');
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function user()
    {
        try {
            $user = $this->authService->currentUser();
            return $this->success($user, 'Current user data');
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function refresh()
    {
        try {
            $token = $this->authService->refreshToken();
            return $this->success($token, 'Token refreshed');
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function store(AdminCreateUserRequest $request)
    {
        try {
            $admin = Auth::user();
            if (! $admin || ! $admin->isAdmin()) {
                return $this->forbidden('You are not authorized to create users.');
            }

            $user = $this->authService->createByAdmin($request->validated(), $admin);

            return $this->success($user, 'User created successfully', 201);
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }
}
