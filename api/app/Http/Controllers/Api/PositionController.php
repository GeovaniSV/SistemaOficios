<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyPositionRequest;
use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Http\Requests\ViewPositionRequest;
use App\Models\Position;
use App\Services\PositionService;
use Illuminate\Http\JsonResponse;

/**
 * @group Cargos
 */
class PositionController extends Controller
{
    public function __construct(
        private PositionService $service
    ) {}

    /**
     * Listar cargos
     *
     * Retorna lista paginada de cargos.
     *
     * @queryParam filter[name] string Filtro de pesquisa parcial pelo nome. Example: Diretor
     * @queryParam filter[is_active] boolean Filtra por status ativo/inativo. Example: true
     * @queryParam sort string Campo de ordenação. Use "-" para decrescente. Valores permitidos: name, created_at. Example: -created_at
     *
     * @response 200 {
     *   "current_page": 1,
     *   "data": [{ "id": 1, "name": "Diretor", "is_active": true }],
     *   "per_page": 20,
     *   "total": 1
     * }
     */
    public function index(ViewPositionRequest $request): JsonResponse
    {
        return response()->json($this->service->list());
    }

    /**
     * Exibir cargo
     *
     * @response 200 { "id": 1, "name": "Diretor", "is_active": true }
     * @response 404 {"message": "No query results for model [App\\Models\\Position]"}
     */
    public function show(Position $position): JsonResponse
    {
        return response()->json($this->service->getById($position));
    }

    /**
     * Criar cargo
     *
     * @response 201 { "id": 2, "name": "Secretário", "is_active": true }
     */
    public function store(StorePositionRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->create($request->validated()),
            201
        );
    }

    /**
     * Atualizar cargo
     *
     * @response 200 { "id": 1, "name": "Diretor Geral", "is_active": true }
     */
    public function update(UpdatePositionRequest $request, Position $position): JsonResponse
    {
        return response()->json(
            $this->service->update($position, $request->validated())
        );
    }

    /**
     * Inativar / Reativar cargo
     *
     * Quando `activate=false` (padrão), inativa o cargo. Quando `activate=true`, reativa.
     *
     * @queryParam activate boolean Passa `true` para reativar, `false` (padrão) para inativar. Example: false
     *
     * @response 200 { "id": 1, "name": "Diretor", "is_active": false }
     */
    public function destroy(DestroyPositionRequest $request, Position $position): JsonResponse
    {
        $activate = filter_var($request->input('activate', false), FILTER_VALIDATE_BOOLEAN);

        return response()->json(
            $this->service->toggleActive($position, $activate)
        );
    }
}
