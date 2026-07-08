<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewOficioRequest;
use App\Http\Requests\SendOficioRequest;
use App\Http\Requests\StoreOficioRequest;
use App\Http\Requests\UpdateOficioRequest;
use App\Http\Requests\ViewOficioRequest;
use App\Models\Oficio;
use App\Services\OficioService;

/**
 * @group Ofícios
 */
class OficioController extends Controller
{
    public function __construct(
        private OficioService $service
    ) {}

    /**
     * Listar ofícios
     *
     * Retorna lista paginada de ofícios com remetente e destinatários.
     *
     * @queryParam filter[subject] string Filtro de pesquisa parcial pelo assunto. Example: Solicitação
     * @queryParam filter[number] string Filtro de pesquisa parcial pelo número. Example: 001
     * @queryParam filter[department] string Filtro de pesquisa parcial pelo departamento. Example: Financeiro
     * @queryParam filter[status] string Filtro exato pelo status. Example: DRAFT
     * @queryParam filter[priority] string Filtro exato pela prioridade. Example: MEDIUM
     * @queryParam filter[destination_contact_id] integer Filtro exato pelo contato de destino. Example: 1
     * @queryParam filter[author_id] integer Filtro exato pelo autor. Example: 1
     * @queryParam sort string Campo de ordenação. Use "-" para decrescente. Valores permitidos: number, subject, priority, status, created_at. Padrão: -created_at. Example: -created_at
     *
     * @response 200 {
     *   "current_page": 1,
     *   "data": [{
     *     "id": 1,
     *     "number": "001/2026",
     *     "subject": "Solicitação de Informações",
     *     "status": "DRAFT",
     *     "priority": "MEDIUM",
     *     "created_at": "2026-07-03T00:00:00.000000Z"
     *   }],
     *   "per_page": 20,
     *   "total": 1
     * }
     */
    public function index(ViewOficioRequest $request)
    {
        return response()->json($this->service->list());
    }

    /**
     * Exibir ofício
     *
     * @response 200 {
     *   "id": 1,
     *   "number": "001/2026",
     *   "subject": "Solicitação de Informações",
     *   "content": "<p>Conteúdo do ofício...</p>",
     *   "status": "DRAFT",
     *   "priority": "MEDIUM",
     *   "author": { "id": 1, "name": "Hugo Barbosa" },
     *   "messages": []
     * }
     * @response 404 {"message": "No query results for model [App\\Models\\Oficio]"}
     */
    public function show(ViewOficioRequest $request, Oficio $oficio)
    {
        return response()->json($this->service->getById($oficio));
    }

    /**
     * Criar ofício
     *
     * Cria um novo ofício com status `DRAFT`. O número é gerado automaticamente.
     *
     * @response 201 {
     *   "id": 2,
     *   "number": "002/2026",
     *   "subject": "Novo Ofício",
     *   "status": "DRAFT",
     *   "priority": "LOW"
     * }
     * @response 422 {"message": "The subject field is required.", "errors": { "subject": ["The subject field is required."] }}
     */
    public function store(StoreOficioRequest $request)
    {
        return response()->json(
            $this->service->create($request->validated()),
            201
        );
    }

    /**
     * Atualizar ofício
     *
     * Apenas ofícios em status `DRAFT` ou `RETURNED` podem ser editados.
     *
     * @response 200 {
     *   "id": 1,
     *   "number": "001/2026",
     *   "subject": "Assunto Atualizado",
     *   "status": "DRAFT"
     * }
     */
    public function update(UpdateOficioRequest $request, Oficio $oficio)
    {
        return response()->json(
            $this->service->update($oficio, $request->validated())
        );
    }

    /**
     * Revisar ofício
     *
     * Aprova (`APPROVED`) ou rejeita/devolve (`REJECTED`/`RETURNED`) o ofício.
     *
     * @response 200 {
     *   "id": 1,
     *   "number": "001/2026",
     *   "status": "APPROVED"
     * }
     * @response 403 {"message": "This action is unauthorized."}
     */
    public function review(ReviewOficioRequest $request, Oficio $oficio)
    {
        return response()->json(
            $this->service->review($oficio, $request->validated())
        );
    }

    /**
     * Enviar ofício
     *
     * Enfileira o ofício para geração de PDF e envio de e-mail. O status muda para `SENT`.
     * Requer que o ofício esteja `APPROVED`.
     *
     * @response 200 {"success": true, "message": "Ofício enviado com sucesso."}
     * @response 422 {"message": "Ofício não está aprovado para envio."}
     */
    public function send(SendOficioRequest $request, Oficio $oficio)
    {
        return response()->json(
            $this->service->send($oficio)
        );
    }
}
