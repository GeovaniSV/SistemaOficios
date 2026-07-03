<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Autenticação
 */
class AuthController extends Controller
{
    public function __construct(
        private AuthService $service
    ) {}

    /**
     * Login
     *
     * Autentica o usuário e retorna um token Bearer Sanctum.
     *
     * @unauthenticated
     *
     * @response 200 {
     *   "token": "1|AbCdEf...",
     *   "user": {
     *     "id": 1,
     *     "name": "Hugo Barbosa",
     *     "email": "hugo@example.com",
     *     "is_dev": false,
     *     "position": { "id": 1, "name": "Diretor" },
     *     "roles": [{ "id": 1, "name": "admin" }]
     *   }
     * }
     * @response 401 {"message": "Credenciais inválidas."}
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->service->login(
            $request->validated('email'),
            $request->validated('password')
        );

        return response()->json($data);
    }

    /**
     * Logout
     *
     * Revoga o token de acesso atual.
     *
     * @response 200 {"message": "Logout realizado com sucesso."}
     */
    public function logout(Request $request): JsonResponse
    {
        $this->service->logout($request->user());

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }

    /**
     * Logout de todos os dispositivos
     *
     * Revoga todos os tokens Sanctum do usuário autenticado.
     *
     * @response 200 {"message": "Todos os tokens revogados."}
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $this->service->logoutAll($request->user());

        return response()->json(['message' => 'Todos os tokens revogados.']);
    }

    /**
     * Usuário autenticado
     *
     * Retorna os dados do usuário logado, incluindo cargo e papéis.
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Hugo Barbosa",
     *   "email": "hugo@example.com",
     *   "is_active": true,
     *   "is_dev": false,
     *   "position": { "id": 1, "name": "Diretor", "is_active": true },
     *   "roles": [{ "id": 1, "name": "admin", "status": "Ativo" }]
     * }
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json(
            $this->service->me($request->user())
        );
    }
}
