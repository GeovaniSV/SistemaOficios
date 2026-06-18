<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Models\Position;
use App\Services\PositionService;
use Illuminate\Http\JsonResponse;

class PositionController extends Controller
{
    public function __construct(
        private PositionService $service
    ) {}

    public function index(): JsonResponse
    {
        return response()->json($this->service->list());
    }

    public function show(Position $position): JsonResponse
    {
        return response()->json($this->service->getById($position));
    }

    public function store(StorePositionRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->create($request->validated()),
            201
        );
    }

    public function update(UpdatePositionRequest $request, Position $position): JsonResponse
    {
        return response()->json(
            $this->service->update($position, $request->validated())
        );
    }

    public function destroy(Position $position): JsonResponse
    {
        $this->service->delete($position);

        return response()->json(null, 204);
    }
}
