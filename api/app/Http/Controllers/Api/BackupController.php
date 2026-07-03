<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AutoBackupRequest;
use App\Http\Requests\ListBackupRequest;
use App\Http\Requests\ManualBackupRequest;
use App\Services\BackupService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @group Backups
 */
class BackupController extends Controller
{
    public function __construct(
        private BackupService $service
    ) {}

    /**
     * Listar backups
     *
     * Retorna todos os backups registrados, ordenados do mais recente para o mais antigo.
     * Durante a listagem, a disponibilidade dos backups manuais armazenados na R2 é verificada
     * e atualizada automaticamente.
     * O campo `download_url` contém uma URL presignada válida por 1 hora (apenas para backups disponíveis na R2).
     *
     * @response 200 {
     *   "current_page": 1,
     *   "data": [
     *     {
     *       "id": 2,
     *       "type": "manual",
     *       "storage_type": "r2",
     *       "user_id": 1,
     *       "filename": "backup-2026-07-03_15-30-00.zip",
     *       "r2_path": "backups/manual/backup-2026-07-03_15-30-00.zip",
     *       "is_available": true,
     *       "created_at": "2026-07-03T15:30:00.000000Z",
     *       "user": { "id": 1, "name": "Hugo Barbosa" },
     *       "download_url": "https://r2.cloudflarestorage.com/..."
     *     },
     *     {
     *       "id": 1,
     *       "type": "automatic",
     *       "storage_type": "r2",
     *       "user_id": null,
     *       "filename": "backup-2026-07-03_00-00-00.zip",
     *       "r2_path": "backups/auto/backup-2026-07-03_00-00-00.zip",
     *       "is_available": true,
     *       "created_at": "2026-07-03T00:00:00.000000Z",
     *       "user": null,
     *       "download_url": "https://r2.cloudflarestorage.com/..."
     *     }
     *   ],
     *   "per_page": 20,
     *   "total": 2
     * }
     */
    public function index(ListBackupRequest $request): JsonResponse
    {
        return response()->json($this->service->list());
    }

    /**
     * Backup automático
     *
     * Gera um backup do banco de dados e armazena no Cloudflare R2 (bucket de backup).
     * Executa automaticamente a limpeza: remove backups automáticos com mais de 5 dias
     * e garante que no máximo 5 backups automáticos permaneçam disponíveis.
     *
     * Autenticado via chave de API do broker (`X-Broker-Api-Key`), não por Sanctum.
     * Deve ser chamado pelo cron do servidor.
     *
     * **Exemplo de chamada via cron:**
     * ```
     * curl -X POST https://seu-dominio.com/api/backups/auto \
     *   -H "X-Broker-Api-Key: SUA_CHAVE_AQUI"
     * ```
     *
     * @group Broker (Interno)
     * @unauthenticated
     * @header X-Broker-Api-Key {BROKER_API_KEY} required
     *
     * @response 200 {"success": true, "message": "Backup automático realizado com sucesso"}
     * @response 401 {"message": "Unauthorized"}
     * @response 500 {"message": "Nenhum arquivo de backup foi gerado (exit code: 1)"}
     */
    public function auto(AutoBackupRequest $request): JsonResponse
    {
        return response()->json($this->service->runAutomatic());
    }

    /**
     * Backup manual
     *
     * Gera um backup manual do banco de dados. O parâmetro `storage` define o destino:
     * - `r2`: armazena no Cloudflare R2 e registra como disponível para download posterior.
     * - `download`: retorna o arquivo ZIP diretamente como download. Registrado como indisponível (sem cópia armazenada).
     *
     * Requer permissão `configuracoes.acessar` ou usuário marcado como `is_dev`.
     *
     * @bodyParam storage string required Destino do backup: `r2` para armazenar na nuvem, `download` para baixar imediatamente. Example: r2
     *
     * @response 200 scenario="Armazenado na R2" {"success": true, "message": "Backup manual salvo na R2 com sucesso"}
     * @response 200 scenario="Download" {"description": "Arquivo ZIP retornado como download binário"}
     * @response 403 {"message": "This action is unauthorized."}
     * @response 422 {"message": "The storage field is required.", "errors": { "storage": ["The storage field is required."] }}
     */
    public function manual(ManualBackupRequest $request): JsonResponse|BinaryFileResponse
    {
        if ($request->input('storage') === 'download') {
            $filepath = $this->service->runManualDownload($request->user());

            return response()->download($filepath, basename($filepath))->deleteFileAfterSend(true);
        }

        return response()->json($this->service->runManualR2($request->user()));
    }
}
