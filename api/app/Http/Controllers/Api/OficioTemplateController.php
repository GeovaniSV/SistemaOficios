<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyOficioTemplateRequest;
use App\Http\Requests\StoreOficioTemplateRequest;
use App\Http\Requests\UpdateOficioTemplateRequest;
use App\Http\Requests\ViewOficioTemplateRequest;
use App\Models\OficioTemplate;
use App\Services\OficioTemplateService;

/**
 * @group Templates de Ofício
 */
class OficioTemplateController extends Controller
{
    public function __construct(
        private OficioTemplateService $service
    ) {}

    /**
     * Listar templates
     *
     * @response 200 {
     *   "current_page": 1,
     *   "data": [{
     *     "id": 1,
     *     "name": "Solicitação Padrão",
     *     "content": "<p>Modelo de ofício...</p>",
     *     "is_active": true
     *   }],
     *   "per_page": 20,
     *   "total": 1
     * }
     */
    public function index(ViewOficioTemplateRequest $request)
    {
        return response()->json(
            $this->service->list()
        );
    }

    /**
     * Exibir template
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Solicitação Padrão",
     *   "content": "<p>Modelo de ofício...</p>",
     *   "is_active": true
     * }
     * @response 404 {"message": "No query results for model [App\\Models\\OficioTemplate]"}
     */
    public function show(ViewOficioTemplateRequest $request, OficioTemplate $oficioTemplate)
    {
        return response()->json(
            $this->service->getById($oficioTemplate)
        );
    }

    /**
     * Criar template
     *
     * @response 201 {
     *   "id": 2,
     *   "name": "Convocação",
     *   "content": "<p>...</p>",
     *   "is_active": true
     * }
     */
    public function store(StoreOficioTemplateRequest $request)
    {
        return response()->json(
            $this->service->create($request->validated()),
            201
        );
    }

    /**
     * Atualizar template
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Solicitação Atualizada",
     *   "content": "<p>Modelo atualizado...</p>",
     *   "is_active": true
     * }
     */
    public function update(UpdateOficioTemplateRequest $request, OficioTemplate $oficioTemplate)
    {
        return response()->json(
            $this->service->update($oficioTemplate, $request->validated())
        );
    }

    /**
     * Inativar / Reativar template
     *
     * Quando `activate=false` (padrão), inativa o template. Quando `activate=true`, reativa.
     *
     * @queryParam activate boolean Passa `true` para reativar, `false` (padrão) para inativar. Example: false
     *
     * @response 200 { "id": 1, "name": "Solicitação Padrão", "is_active": false }
     */
    public function destroy(DestroyOficioTemplateRequest $request, OficioTemplate $oficioTemplate)
    {
        $activate = filter_var($request->input('activate', false), FILTER_VALIDATE_BOOLEAN);

        return response()->json(
            $this->service->toggleActive($oficioTemplate, $activate)
        );
    }
}
