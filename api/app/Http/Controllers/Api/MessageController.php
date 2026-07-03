<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Services\MessageService;

/**
 * @group Mensagens
 */
class MessageController extends Controller
{
    public function __construct(
        private MessageService $service
    ) {}

    /**
     * Listar mensagens
     *
     * Retorna lista paginada de mensagens (envios de ofício) com status e destinatário.
     *
     * @response 200 {
     *   "current_page": 1,
     *   "data": [{
     *     "id": 1,
     *     "status": "SENT",
     *     "sent_at": "2026-07-03T15:00:00.000000Z",
     *     "oficio": { "id": 1, "number": "001/2026", "subject": "Solicitação de Informações" },
     *     "responsible": { "id": 1, "name": "João da Silva", "email": "joao@oab.org.br" }
     *   }],
     *   "per_page": 20,
     *   "total": 1
     * }
     */
    public function index()
    {
        return response()->json(
            $this->service->list()
        );
    }

    /**
     * Exibir mensagem
     *
     * @response 200 {
     *   "id": 1,
     *   "status": "SENT",
     *   "sent_at": "2026-07-03T15:00:00.000000Z",
     *   "oficio": { "id": 1, "number": "001/2026" },
     *   "responsible": { "id": 1, "name": "João da Silva", "email": "joao@oab.org.br" }
     * }
     * @response 404 {"message": "No query results for model [App\\Models\\Message]"}
     */
    public function show(Message $message)
    {
        return response()->json(
            $this->service->getById($message)
        );
    }

    /**
     * Download do PDF
     *
     * Baixa o PDF gerado para a mensagem diretamente do Cloudflare R2.
     * Disponível apenas para mensagens com status `SENT` que já tiveram o PDF gerado.
     *
     * @response 200 scenario="PDF disponível" {"description": "Arquivo PDF retornado como download binário"}
     * @response 404 {"message": "PDF não disponível para esta mensagem"}
     * @response 404 {"message": "PDF ainda não foi gerado"}
     */
    public function downloadPdf(Message $message)
    {
        return $this->service->downloadPdf($message);
    }
}
