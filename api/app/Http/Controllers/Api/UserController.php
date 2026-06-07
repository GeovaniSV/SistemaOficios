<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private UserService $service
    ) {}

    public function index(): JsonResponse
    {
        return response()->json($this->service->list());
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($this->service->getById($user));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->create($request->validated()),
            201
        );
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        return response()->json(
            $this->service->update($user, $request->validated())
        );
    }

    public function destroy(User $user): JsonResponse
    {
        return response()->json(
            $this->service->softDelete($user)
        );
    }

    public function restore(User $user): JsonResponse
    {
        return response()->json(
            $this->service->restore($user)
        );
    }
}
