<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyContactRequest;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Http\Requests\ViewContactRequest;
use App\Services\ContactService;
use Illuminate\Http\JsonResponse;

/**
 * @group Contatos
 */
class ContactController extends Controller
{
    public function __construct(
        private ContactService $service
    ) {}

    /**
     * Listar contatos
     *
     * Retorna lista paginada de contatos. Filtre pelo status com o parâmetro `is_active`.
     *
     * @queryParam is_active boolean Filtra por status ativo/inativo. Omita para retornar todos. Example: true
     *
     * @response 200 {
     *   "current_page": 1,
     *   "data": [{
     *     "id": 1,
     *     "name": "OAB Nacional",
     *     "tipo": "PJ",
     *     "email": "oab@oab.org.br",
     *     "is_active": true,
     *     "address": { "street": "SHS Q. 6", "city": "Brasília", "state": "DF" }
     *   }],
     *   "per_page": 20,
     *   "total": 1
     * }
     */
    public function index(ViewContactRequest $request): JsonResponse
    {
        $isActive = null;

        if ($request->has('is_active')) {
            $isActive = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        return response()->json(
            $this->service->list($isActive)
        );
    }

    /**
     * Responsáveis do contato
     *
     * Retorna a lista de responsáveis (pessoas físicas) vinculados ao contato.
     *
     * @urlParam id integer required ID do contato. Example: 1
     *
     * @response 200 [{
     *   "id": 1,
     *   "name": "João da Silva",
     *   "email": "joao@oab.org.br",
     *   "treatment": "Dr.",
     *   "position": "Presidente"
     * }]
     */
    public function responsibles(ViewContactRequest $request, int $id): JsonResponse
    {
        return response()->json(
            $this->service->getResponsibles($id)
        );
    }

    /**
     * Exibir contato
     *
     * @urlParam id integer required ID do contato. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "OAB Nacional",
     *   "tipo": "PJ",
     *   "email": "oab@oab.org.br",
     *   "is_active": true,
     *   "address": { "street": "SHS Q. 6", "city": "Brasília", "state": "DF" }
     * }
     * @response 404 {"message": "Contato não encontrado."}
     */
    public function show(ViewContactRequest $request, int $id): JsonResponse
    {
        return response()->json(
            $this->service->getById($id)
        );
    }

    /**
     * Criar contato
     *
     * @response 201 {
     *   "id": 2,
     *   "name": "TRT 10ª Região",
     *   "tipo": "PJ",
     *   "email": "trt10@trt10.jus.br",
     *   "is_active": true
     * }
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        $contact = $this->service->create(
            $request->validated()
        );

        return response()->json($contact, 201);
    }

    /**
     * Atualizar contato
     *
     * @urlParam id integer required ID do contato. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "OAB Nacional Atualizado",
     *   "tipo": "PJ",
     *   "is_active": true
     * }
     */
    public function update(UpdateContactRequest $request, int $id): JsonResponse
    {
        $contact = $this->service->update($id, $request->validated());

        return response()->json($contact);
    }

    /**
     * Inativar / Reativar contato
     *
     * Quando `activate=false` (padrão), inativa o contato. Quando `activate=true`, reativa.
     *
     * @urlParam id integer required ID do contato. Example: 1
     * @queryParam activate boolean Passa `true` para reativar, `false` (padrão) para inativar. Example: false
     *
     * @response 200 { "id": 1, "name": "OAB Nacional", "is_active": false }
     */
    public function destroy(DestroyContactRequest $request, int $id): JsonResponse
    {
        $activate = filter_var($request->input('activate', false), FILTER_VALIDATE_BOOLEAN);

        $contact = $this->service->toggleActive($id, $activate);

        return response()->json($contact);
    }
}
