<?php

namespace App\Models;

use App\Enums\BackupStorageEnum;
use App\Enums\BackupTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Backup extends Model
{
    protected $fillable = [
        'type',
        'storage_type',
        'user_id',
        'filename',
        'r2_path',
        'is_available',
    ];

    protected $casts = [
        'type'         => BackupTypeEnum::class,
        'storage_type' => BackupStorageEnum::class,
        'is_available' => 'boolean',
    ];

    protected $appends = ['download_url'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDownloadUrlAttribute(): ?string
    {
        if (
            !$this->is_available
            || $this->storage_type !== BackupStorageEnum::R2
            || !$this->r2_path
        ) {
            return null;
        }

        try {
            return Storage::disk('r2_backup')->temporaryUrl($this->r2_path, now()->addHour());
        } catch (\Throwable) {
            return null;
        }
    }
}
