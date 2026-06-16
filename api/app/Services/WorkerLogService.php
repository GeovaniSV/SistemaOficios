<?php

namespace App\Services;

use App\Models\WorkerLog;

class WorkerLogService
{
    public function store(array $data): WorkerLog
    {
        return WorkerLog::create([
            'correlation_id' => $data['correlationId'] ?? null,
            'code'           => $data['code'] ?? null,
            'message'        => $data['message'] ?? null,
            'status'         => $data['status'] ?? null,
            'queue_name'     => $data['queueName'] ?? null,
            'event_type'     => $data['eventType'] ?? null,
            'metadata'       => $data['metadata'] ?? null,
            'user_id'        => $data['userId'] ?? null,
        ]);
    }

    public function list()
    {
        return WorkerLog::latest()->paginate(20);
    }
}
