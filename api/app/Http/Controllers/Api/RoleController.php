<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyRoleRequest;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Requests\ViewRoleRequest;
use App\Services\RoleService;
use Spatie\Permission\Models\Role;

/**
 * @group Papéis
 */
class RoleController extends Controller
{
    public function __construct(
        private RoleService $service
    ) {}

    /**
     * Listar papéis
     *
     * Retorna lista paginada de papéis (roles) com suas permissões.
     *
     * @response 200 {
     *   "current_page": 1,
     *   "data": [{
     *     "id": 1,
     *     "name": "admin",
     *     "status": "Ativo",
     *     "permissions": [{ "id": 1, "name": "configuracoes.acessar" }]
     *   }],
     *   "per_page": 20,
     *   "total": 1
     * }
     */
    public function index(ViewRoleRequest $request)
    {
        return response()->json($this->service->list());
    }

    /**
     * Exibir papel
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "admin",
     *   "status": "Ativo",
     *   "permissions": [{ "id": 1, "name": "configuracoes.acessar" }]
     * }
     * @response 404 {"message": "No query results for model [Role]"}
     */
    public function show(ViewRoleRequest $request, Role $role)
    {
        return response()->json($this->service->getById($role));
    }

    /**
     * Criar papel
     *
     * @response 201 {
     *   "id": 2,
     *   "name": "editor",
     *   "status": "Ativo",
     *   "permissions": []
     * }
     */
    public function store(StoreRoleRequest $request)
    {
        return response()->json(
            $this->service->create($request->validated()),
            201
        );
    }

    /**
     * Atualizar papel
     *
     * Atualiza nome, status e permissões do papel.
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "admin",
     *   "status": "Ativo",
     *   "permissions": [{ "id": 1, "name": "configuracoes.acessar" }]
     * }
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        return response()->json(
            $this->service->update($role, $request->validated())
        );
    }

    /**
     * Inativar / Reativar papel
     *
     * Quando `activate=false` (padrão), muda o status para `Inativo`. Quando `activate=true`, muda para `Ativo`.
     *
     * @queryParam activate boolean Passa `true` para reativar, `false` (padrão) para inativar. Example: false
     *
     * @response 200 { "id": 1, "name": "admin", "status": "Inativo" }
     */
    public function destroy(DestroyRoleRequest $request, Role $role)
    {
        $activate = filter_var($request->input('activate', false), FILTER_VALIDATE_BOOLEAN);

        return response()->json(
            $this->service->toggleActive($role, $activate)
        );
    }
}
