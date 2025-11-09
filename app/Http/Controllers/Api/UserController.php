<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminCreateUserRequest;
use App\Http\Requests\Admin\AdminUpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use App\Http\Resources\ApiResponseTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Throwable;

class UserController extends Controller
{
    use ApiResponseTrait, AuthorizesRequests;

    public function __construct(private UserService $service) {}

    public function index()
    {
        try {
            $users = $this->service->list();
            return $this->success(UserResource::collection($users), 'Users fetched');
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function show($id)
    {
        try {
            $user = $this->service->find($id);
            if (!$user) return $this->notFound('User not found');
            return $this->success(new UserResource($user));
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function update(AdminUpdateUserRequest $request, $id)
    {
        try {
            $user = $this->service->find($id);
            if (!$user) return $this->notFound('User not found');

            $this->authorize('update', $user);
            \Log::info('Update user payload', $request->validated());
            $user = $this->service->update($id, $request->validated());
            return $this->success(new UserResource($user), 'User updated');
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }


    public function destroy($id)
    {
        try {
            $user = $this->service->find($id);
            if (!$user) return $this->notFound('User not found');

            $this->authorize('delete', $user);

            $this->service->delete($id);
            return $this->success(null, 'User deleted');
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function assignRole($id)
    {
        try {
            $user = $this->service->find($id);
            if (!$user) return $this->notFound('User not found');

            $this->authorize('assignRole', $user);

            $role = request('role');
            $this->service->assignRole($id, $role);
            return $this->success(null, 'Role assigned');
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }
}
