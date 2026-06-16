<?php

namespace App\Models;

use App\Enums\MessageStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $table = 'messages';

    protected $fillable = [
        'status',
        'oficio_id',
        'responsible_id',
        'sent_at',
    ];

    protected $casts = [
        'status' => MessageStatusEnum::class,
        'sent_at' => 'datetime',
    ];

    public function oficio(): BelongsTo
    {
        return $this->belongsTo(
            Oficio::class
        );
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(
            Responsible::class
        );
    }
}
