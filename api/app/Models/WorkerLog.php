<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerLog extends Model
{
    protected $table = 'worker_logs';

    protected $fillable = [
        'correlation_id',
        'code',
        'message',
        'status',
        'queue_name',
        'event_type',
        'metadata',
        'user_id',
    ];

    protected $casts = [
        'metadata' => 'array',
        'status'   => 'integer',
    ];
}
