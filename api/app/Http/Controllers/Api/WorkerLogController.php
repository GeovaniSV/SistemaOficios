<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkerLogRequest;
use App\Http\Requests\ViewWorkerLogRequest;
use App\Services\WorkerLogService;

/**
 * @group Logs de Workers
 */
class WorkerLogController extends Controller
{
    public function __construct(
        private WorkerLogService $service
    ) {}

    /**
     * Listar logs
     *
     * Retorna lista paginada de logs gerados pelos workers (pdfWorker e emailWorker),
     * ordenados do mais recente para o mais antigo.
     *
     * @queryParam filter[worker] string Filtro exato pelo worker. Example: pdfWorker
     * @queryParam filter[status] string Filtro exato pelo status. Example: success
     * @queryParam filter[code] string Filtro exato pelo código do evento. Example: PDF_GENERATED
     * @queryParam filter[queue_name] string Filtro exato pela fila. Example: oficios_queue
     * @queryParam filter[correlation_id] string Filtro exato pelo ID de correlação. Example: abc123
     * @queryParam filter[message] string Filtro de pesquisa parcial pela mensagem. Example: PDF
     * @queryParam sort string Campo de ordenação. Use "-" para decrescente. Valor permitido: created_at. Padrão: -created_at. Example: -created_at
     *
     * @response 200 {
     *   "current_page": 1,
     *   "data": [{
     *     "id": 1,
     *     "worker": "pdfWorker",
     *     "queue": "oficios_queue",
     *     "code": "PDF_GENERATED",
     *     "status": "success",
     *     "message": "PDF gerado com sucesso",
     *     "userId": 1,
     *     "correlationId": "abc123",
     *     "metadata": null,
     *     "created_at": "2026-07-03T15:00:00.000000Z"
     *   }],
     *   "per_page": 50,
     *   "total": 1
     * }
     */
    public function index(ViewWorkerLogRequest $request)
    {
        return response()->json(
            $this->service->list()
        );
    }

    /**
     * Registrar log de worker
     *
     * Endpoint interno usado pelos workers para registrar logs de execução.
     * Autenticado via chave de API do broker (`X-Broker-Api-Key`), não por Sanctum.
     *
     * @group Broker (Interno)
     * @unauthenticated
     * @header X-Broker-Api-Key {BROKER_API_KEY} required
     *
     * @response 201 {
     *   "id": 2,
     *   "worker": "emailWorker",
     *   "queue": "email_queue",
     *   "code": "EMAIL_SENT",
     *   "status": "success",
     *   "message": "Email enviado com sucesso para joao@oab.org.br",
     *   "created_at": "2026-07-03T15:05:00.000000Z"
     * }
     * @response 401 {"message": "Unauthorized"}
     */
    public function store(StoreWorkerLogRequest $request)
    {
        return response()->json(
            $this->service->store($request->validated()),
            201
        );
    }
}
