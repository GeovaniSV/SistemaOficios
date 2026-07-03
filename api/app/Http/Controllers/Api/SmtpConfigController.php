<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSmtpConfigRequest;
use App\Services\SmtpConfigService;

/**
 * @group SMTP
 */
class SmtpConfigController extends Controller
{
    public function __construct(
        private SmtpConfigService $service
    ) {}

    /**
     * Exibir configuração SMTP
     *
     * Retorna a configuração SMTP ativa do sistema (senha omitida).
     *
     * @response 200 {
     *   "id": 1,
     *   "host": "smtp-relay.brevo.com",
     *   "port": 587,
     *   "username": "b07e8f001@smtp-brevo.com",
     *   "from_email": "noreply@example.com",
     *   "from_name": "Sistema de Ofícios"
     * }
     * @response 404 {"message": "SMTP não configurado"}
     */
    public function show()
    {
        return response()->json(
            $this->service->show()
        );
    }

    /**
     * Atualizar configuração SMTP
     *
     * Atualiza as credenciais SMTP e notifica o emailWorker em tempo real via RabbitMQ.
     *
     * @response 200 {
     *   "id": 1,
     *   "host": "smtp-relay.brevo.com",
     *   "port": 587,
     *   "username": "b07e8f001@smtp-brevo.com",
     *   "from_email": "noreply@example.com",
     *   "from_name": "Sistema de Ofícios"
     * }
     */
    public function update(UpdateSmtpConfigRequest $request)
    {
        return response()->json(
            $this->service->update($request->validated())
        );
    }

    /**
     * Configuração SMTP para broker
     *
     * Endpoint interno usado pelo emailWorker para obter as credenciais SMTP.
     * Autenticado via chave de API do broker (`X-Broker-Api-Key`), não por Sanctum.
     *
     * @group Broker (Interno)
     * @unauthenticated
     * @header X-Broker-Api-Key {BROKER_API_KEY} required
     *
     * @response 200 {
     *   "host": "smtp-relay.brevo.com",
     *   "port": 587,
     *   "username": "b07e8f001@smtp-brevo.com",
     *   "password": "xsmtpsib-...",
     *   "from_email": "noreply@example.com",
     *   "from_name": "Sistema de Ofícios"
     * }
     * @response 404 {"message": "SMTP não configurado"}
     * @response 401 {"message": "Unauthorized"}
     */
    public function brokerShow()
    {
        $config = $this->service->forBroker();

        if (!$config) {
            return response()->json(['message' => 'SMTP não configurado'], 404);
        }

        return response()->json($config);
    }
}
