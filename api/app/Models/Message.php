<?php

namespace App\Models;

use App\Enums\MessageStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Message extends Model
{
    protected $table = 'messages';

    protected $fillable = [
        'status',
        'oficio_id',
        'responsible_id',
        'sent_at',
    ];

    protected $hidden = [
        'pdf_hash',
    ];

    protected $casts = [
        'status' => MessageStatusEnum::class,
        'sent_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Message $message) {
            $message->pdf_hash ??= (string) Str::uuid();
        });
    }

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
