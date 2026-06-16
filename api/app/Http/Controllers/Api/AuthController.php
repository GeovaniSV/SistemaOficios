<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $service
    ) {}

    /**
     * @unauthenticated
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->service->login(
            $request->validated('email'),
            $request->validated('password')
        );

        return response()->json($data);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->service->logout($request->user());

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $this->service->logoutAll($request->user());

        return response()->json(['message' => 'Todos os tokens revogados.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(
            $this->service->me($request->user())
        );
    }
}
