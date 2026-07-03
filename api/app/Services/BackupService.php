<?php

namespace App\Services;

use App\Enums\BackupStorageEnum;
use App\Enums\BackupTypeEnum;
use App\Models\Backup;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class BackupService
{
    /**
     * Gera o arquivo de backup via spatie e retorna o caminho relativo dentro do disco backup_local.
     */
    private function generateBackup(): string
    {
        $filesBefore = Storage::disk('backup_local')->allFiles();

        $exitCode = Artisan::call('backup:run', ['--only-db' => true]);

        $filesAfter = Storage::disk('backup_local')->allFiles();
        $newFiles   = array_values(array_diff($filesAfter, $filesBefore));

        if (empty($newFiles)) {
            throw new RuntimeException('Nenhum arquivo de backup foi gerado (exit code: ' . $exitCode . ')');
        }

        rsort($newFiles);

        return $newFiles[0];
    }

    /**
     * Faz upload de um arquivo do disco backup_local para o r2_backup.
     */
    private function uploadToR2(string $localPath, string $r2Path): void
    {
        $stream = Storage::disk('backup_local')->readStream($localPath);

        if ($stream === false) {
            throw new RuntimeException('Não foi possível ler o arquivo de backup');
        }

        Storage::disk('r2_backup')->writeStream($r2Path, $stream);
    }

    /**
     * Remove arquivo do r2_backup e marca o registro como indisponível.
     */
    private function deleteR2File(Backup $backup): void
    {
        if ($backup->r2_path) {
            try {
                Storage::disk('r2_backup')->delete($backup->r2_path);
            } catch (\Throwable) {}
        }

        $backup->update(['is_available' => false]);
    }

    /**
     * Limpa backups automáticos antigos:
     * - Remove os que têm mais de 5 dias
     * - Mantém no máximo 5 no total
     */
    private function cleanupOldAutomatic(): void
    {
        $old = Backup::where('type', BackupTypeEnum::AUTOMATIC)
            ->where('is_available', true)
            ->where('created_at', '<', now()->subDays(5))
            ->get();

        foreach ($old as $backup) {
            $this->deleteR2File($backup);
        }

        $excess = Backup::where('type', BackupTypeEnum::AUTOMATIC)
            ->where('is_available', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->slice(5);

        foreach ($excess as $backup) {
            $this->deleteR2File($backup);
        }
    }

    /**
     * Backup automático — salva na R2 e executa limpeza.
     */
    public function runAutomatic(): array
    {
        $localPath = $this->generateBackup();
        $filename  = basename($localPath);
        $r2Path    = 'backups/auto/' . $filename;

        try {
            $this->uploadToR2($localPath, $r2Path);
        } finally {
            Storage::disk('backup_local')->delete($localPath);
        }

        Backup::create([
            'type'         => BackupTypeEnum::AUTOMATIC,
            'storage_type' => BackupStorageEnum::R2,
            'user_id'      => null,
            'filename'     => $filename,
            'r2_path'      => $r2Path,
            'is_available' => true,
        ]);

        $this->cleanupOldAutomatic();

        return ['success' => true, 'message' => 'Backup automático realizado com sucesso'];
    }

    /**
     * Backup manual salvo na R2.
     */
    public function runManualR2(User $user): array
    {
        $localPath = $this->generateBackup();
        $filename  = basename($localPath);
        $r2Path    = 'backups/manual/' . $filename;

        try {
            $this->uploadToR2($localPath, $r2Path);
        } finally {
            Storage::disk('backup_local')->delete($localPath);
        }

        Backup::create([
            'type'         => BackupTypeEnum::MANUAL,
            'storage_type' => BackupStorageEnum::R2,
            'user_id'      => $user->id,
            'filename'     => $filename,
            'r2_path'      => $r2Path,
            'is_available' => true,
        ]);

        return ['success' => true, 'message' => 'Backup manual salvo na R2 com sucesso'];
    }

    /**
     * Backup manual para download — retorna o caminho absoluto do arquivo.
     * O chamador é responsável por enviar o arquivo ao cliente.
     * O arquivo local é deletado pelo response()->download(...)->deleteFileAfterSend(true).
     */
    public function runManualDownload(User $user): string
    {
        $localPath    = $this->generateBackup();
        $filename     = basename($localPath);
        $absolutePath = Storage::disk('backup_local')->path($localPath);

        Backup::create([
            'type'         => BackupTypeEnum::MANUAL,
            'storage_type' => BackupStorageEnum::DOWNLOAD,
            'user_id'      => $user->id,
            'filename'     => $filename,
            'r2_path'      => null,
            'is_available' => false,
        ]);

        return $absolutePath;
    }

    /**
     * Lista todos os backups, verificando disponibilidade dos manuais armazenados na R2.
     */
    public function list(): LengthAwarePaginator
    {
        $manualR2Available = Backup::where('type', BackupTypeEnum::MANUAL)
            ->where('storage_type', BackupStorageEnum::R2)
            ->where('is_available', true)
            ->get();

        foreach ($manualR2Available as $backup) {
            if ($backup->r2_path && !Storage::disk('r2_backup')->exists($backup->r2_path)) {
                $backup->update(['is_available' => false]);
            }
        }

        return Backup::with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }
}
