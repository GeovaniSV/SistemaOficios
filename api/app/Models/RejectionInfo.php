<?php

namespace App\Models;

use App\Enums\OficioStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RejectionInfo extends Model
{
    protected $table = 'rejection_infos';

    protected $fillable = [
        'oficio_id',
        'author_id',
        'reason',
        'type',
    ];

    protected $casts = [
        'type' => OficioStatusEnum::class,
    ];

    public function oficio(): BelongsTo
    {
        return $this->belongsTo(Oficio::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
