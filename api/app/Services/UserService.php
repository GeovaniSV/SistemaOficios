<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function list(): LengthAwarePaginator
    {
        return User::with('position')->where('is_dev', false)->paginate(20);
    }

    public function getById(User $user): User
    {
        if ($user->is_dev) {
            throw new ModelNotFoundException();
        }

        return $user;
    }

    public function create(array $data): User
    {
        $role = $data['role'] ?? null;
        unset($data['role'], $data['is_dev'], $data['is_active']);

        $user = User::create([...$data, 'is_dev' => false, 'is_active' => true]);

        if ($role) {
            $user->assignRole($role);
        }
        return $user;
    }

    public function update(User $user, array $data): User
    {
        if ($user->is_dev) {
            throw new ModelNotFoundException();
        }

        $role = $data['role'] ?? null;
        unset($data['role'], $data['is_dev'], $data['is_active']);

        $user->update($data);

        if ($role) {
            $user->syncRoles([$role]);
        }

        return $user;
    }

    public function softDelete(User $user): User
    {
        if ($user->is_dev) {
            throw new ModelNotFoundException();
        }

        $user->tokens()->delete();
        $user->update(['is_active' => false]);

        return $user;
    }

    public function restore(User $user): User
    {
        if ($user->is_dev) {
            throw new ModelNotFoundException();
        }

        $user->update(['is_active' => true]);

        return $user->fresh();
    }
}
