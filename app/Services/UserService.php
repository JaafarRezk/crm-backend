<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function list($params = [])
    {
        $q = User::query()->orderBy('name');
        if (!empty($params['search'])) {
            $term = '%' . trim($params['search']) . '%';
            $q->where(function ($s) use ($term) {
                $s->where('name', 'like', $term)->orWhere('email', 'like', $term);
            });
        }
        return $q->paginate($params['per_page'] ?? 15);
    }

    public function find($id)
    {
        return User::find($id);
    }

    public function create(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }
        return $user;
    }

    public function update($id, array $data): User
    {
        $user = $this->find($id);
        if (!$user) throw new \Illuminate\Database\Eloquent\ModelNotFoundException();

        if (!empty($data['name'])) $user->name = $data['name'];
        if (!empty($data['email'])) $user->email = $data['email'];
        if (!empty($data['password'])) $user->password = Hash::make($data['password']);
        if (!empty($data['user_type'])) {
            $user->user_type = $data['user_type'];
        }
        $user->save();

        if (array_key_exists('roles', $data) && !empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return $user->fresh();
    }

    public function delete($id): void
    {
        $user = $this->find($id);
        if ($user) $user->delete();
    }

    public function assignRole($id, $role): void
    {
        $user = $this->find($id);
        if (!$user) throw new \Exception('User not found');
        $user->assignRole($role);
    }
}
