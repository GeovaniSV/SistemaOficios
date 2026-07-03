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

class UserController extends Controller
{
    public function __construct(
        private UserService $service
    ) {}

    public function index(ViewUserRequest $request): JsonResponse
    {
        return response()->json($this->service->list());
    }

    public function show(ViewUserRequest $request, User $user): JsonResponse
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

    public function destroy(DestroyUserRequest $request, User $user): JsonResponse
    {
        $activate = filter_var($request->input('activate', false), FILTER_VALIDATE_BOOLEAN);

        return response()->json(
            $this->service->toggleActive($user, $activate)
        );
    }
}
