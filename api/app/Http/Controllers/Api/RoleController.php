<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Requests\ViewRoleRequest;
use App\Services\RoleService;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(
        private RoleService $service
    ) {}

    public function index(ViewRoleRequest $request)
    {
        return response()->json($this->service->list());
    }

    public function show(ViewRoleRequest $request, Role $role)
    {
        return response()->json($this->service->getById($role));
    }

    public function store(StoreRoleRequest $request)
    {
        return response()->json(
            $this->service->create($request->validated()),
            201
        );
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        return response()->json(
            $this->service->update($role, $request->validated())
        );
    }
}
