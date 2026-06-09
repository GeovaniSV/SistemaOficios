<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\OficioStatusEnum;
class RejectionInfo extends Model
{
    protected $table = 'rejection_infos';

    protected $fillable = [
        'reason',
        'author_id',
        'type',
    ];

    protected $casts = [
        'type' => OficioStatusEnum::class,
    ];

    public function oficio(): HasOne
    {
        return $this->hasOne(Oficio::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
