<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function list(): LengthAwarePaginator
    {
        return Role::with('permissions')->paginate(20);
    }

    public function getById(Role $role): Role
    {
        return $role->load('permissions');
    }

    public function create(array $data): Role
    {
        $role = Role::create([
            'name'        => $data['name'],
            'description' => $data['description'],
            'status'      => $data['status'],
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        return $role->load('permissions');
    }

    public function update(Role $role, array $data): Role
    {
        $role->update([
            'name'        => $data['name'],
            'description' => $data['description'],
            'status'      => $data['status'],
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        return $role->load('permissions');
    }
}
