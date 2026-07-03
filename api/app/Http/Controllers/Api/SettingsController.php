<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Http\Requests\ViewSettingsRequest;
use App\Services\SettingsService;

/**
 * @group Configurações
 */
class SettingsController extends Controller
{
    public function __construct(
        private SettingsService $service
    ) {}

    /**
     * Exibir configurações
     *
     * Retorna as configurações globais do sistema (cabeçalho, rodapé, signatários).
     *
     * @response 200 {
     *   "id": 1,
     *   "header": "<p>Cabeçalho institucional...</p>",
     *   "footer": "<p>Rodapé institucional...</p>",
     *   "authorized_signers": [{ "id": 1, "name": "Hugo Barbosa", "position": "Diretor" }]
     * }
     */
    public function show(ViewSettingsRequest $request)
    {
        return response()->json(
            $this->service->get()
        );
    }

    /**
     * Atualizar configurações
     *
     * Atualiza cabeçalho, rodapé e signatários autorizados dos ofícios.
     *
     * @response 200 {
     *   "id": 1,
     *   "header": "<p>Novo cabeçalho...</p>",
     *   "footer": "<p>Novo rodapé...</p>"
     * }
     */
    public function update(UpdateSettingsRequest $request)
    {
        return response()->json(
            $this->service->update(
                $request->validated()
            )
        );
    }
}
