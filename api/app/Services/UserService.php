<?php

namespace App\Services;

use App\Filters\BooleanFilter;
use App\Filters\RoleNameFilter;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserService
{
    public function list(): LengthAwarePaginator
    {
        return QueryBuilder::for(User::class)
            ->with('position', 'roles')
            ->where('is_dev', false)
            ->allowedFilters(...[
                AllowedFilter::partial('name'),
                AllowedFilter::partial('email'),
                AllowedFilter::exact('position_id'),
                AllowedFilter::custom('is_active', new BooleanFilter()),
                AllowedFilter::custom('roles', new RoleNameFilter()),
            ])
            ->allowedSorts(...['name', 'email', 'created_at', 'last_login'])
            ->paginate(20);
    }

    public function getById(User $user): User
    {
        if ($user->is_dev) {
            throw new ModelNotFoundException();
        }

        return $user->load('position', 'roles');
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

    public function toggleActive(User $user, bool $activate): User
    {
        if ($user->is_dev) {
            throw new ModelNotFoundException();
        }

        if (!$activate) {
            $user->tokens()->delete();
        }

        $user->update(['is_active' => $activate]);

        return $user->fresh()->load('position', 'roles');
    }
}
