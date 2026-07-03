<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\ViewUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

/**
 * @group Usuários
 */
class UserController extends Controller
{
    public function __construct(
        private UserService $service
    ) {}

    /**
     * Listar usuários
     *
     * Retorna lista paginada de usuários com cargo e papéis.
     *
     * @response 200 {
     *   "current_page": 1,
     *   "data": [{
     *     "id": 1,
     *     "name": "Hugo Barbosa",
     *     "email": "hugo@example.com",
     *     "is_active": true,
     *     "is_dev": false,
     *     "position": { "id": 1, "name": "Diretor" },
     *     "roles": [{ "id": 1, "name": "admin" }]
     *   }],
     *   "per_page": 20,
     *   "total": 1
     * }
     */
    public function index(ViewUserRequest $request): JsonResponse
    {
        return response()->json($this->service->list());
    }

    /**
     * Exibir usuário
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Hugo Barbosa",
     *   "email": "hugo@example.com",
     *   "is_active": true,
     *   "is_dev": false,
     *   "position": { "id": 1, "name": "Diretor" },
     *   "roles": [{ "id": 1, "name": "admin" }]
     * }
     * @response 404 {"message": "No query results for model [App\\Models\\User]"}
     */
    public function show(ViewUserRequest $request, User $user): JsonResponse
    {
        return response()->json($this->service->getById($user));
    }

    /**
     * Criar usuário
     *
     * @response 201 {
     *   "id": 2,
     *   "name": "Maria Silva",
     *   "email": "maria@example.com",
     *   "is_active": true,
     *   "position": null,
     *   "roles": []
     * }
     * @response 422 {"message": "The email has already been taken.", "errors": { "email": ["The email has already been taken."] }}
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->create($request->validated()),
            201
        );
    }

    /**
     * Atualizar usuário
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Hugo Barbosa Atualizado",
     *   "email": "hugo@example.com",
     *   "is_active": true,
     *   "position": { "id": 1, "name": "Diretor" },
     *   "roles": [{ "id": 1, "name": "admin" }]
     * }
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        return response()->json(
            $this->service->update($user, $request->validated())
        );
    }

    /**
     * Inativar / Reativar usuário
     *
     * Quando `activate=false` (padrão), inativa o usuário e revoga todos os seus tokens.
     * Quando `activate=true`, reativa o usuário.
     * Usuários marcados como `is_dev` não podem ser inativados.
     *
     * @queryParam activate boolean Passa `true` para reativar, `false` (padrão) para inativar. Example: false
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Hugo Barbosa",
     *   "is_active": false,
     *   "position": { "id": 1, "name": "Diretor" },
     *   "roles": []
     * }
     * @response 404 {"message": "No query results for model [App\\Models\\User]"}
     */
    public function destroy(DestroyUserRequest $request, User $user): JsonResponse
    {
        $activate = filter_var($request->input('activate', false), FILTER_VALIDATE_BOOLEAN);

        return response()->json(
            $this->service->toggleActive($user, $activate)
        );
    }
}
