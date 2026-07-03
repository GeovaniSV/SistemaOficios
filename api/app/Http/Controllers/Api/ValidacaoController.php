<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidacaoRequest;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;

/**
 * @group Validação de Documentos
 */
class ValidacaoController extends Controller
{
    public function __construct(
        private MessageService $service
    ) {}

    /**
     * Verificar autenticidade de ofício
     *
     * Rota pública. Verifica se existe um PDF válido para o código de validação impresso no ofício.
     * O `codigo` é um UUID diferente do hash do arquivo — é o `validation_hash` gerado no momento do envio.
     * Em caso de sucesso, retorna uma URL temporária (60 minutos) para download do PDF no Cloudflare R2.
     *
     * @unauthenticated
     *
     * @queryParam codigo string required UUID de validação do ofício (impresso no documento). Example: 550e8400-e29b-41d4-a716-446655440000
     *
     * @response 200 scenario="Documento válido" {
     *   "success": true,
     *   "message": "a PDF exists for this hash",
     *   "path": "https://r2.cloudflarestorage.com/oficios/abc123.pdf?X-Amz-Signature=..."
     * }
     * @response 200 scenario="Documento não encontrado" {
     *   "success": false,
     *   "message": "don't exists a PDF for this hash"
     * }
     * @response 422 {"message": "The codigo field must be a valid UUID.", "errors": { "codigo": ["The codigo field must be a valid UUID."] }}
     */
    public function validate(ValidacaoRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->validateByHash($request->input('codigo'))
        );
    }
}
