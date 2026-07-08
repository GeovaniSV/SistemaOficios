<?php

namespace App\Services;

use App\Models\WorkerLog;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

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
            'worker'         => $data['worker'] ?? null,
            'event_type'     => $data['eventType'] ?? null,
            'metadata'       => $data['metadata'] ?? null,
            'user_id'        => $data['userId'] ?? null,
        ]);
    }

    public function list()
    {
        return QueryBuilder::for(WorkerLog::class)
            ->allowedFilters(...[
                AllowedFilter::exact('worker'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('code'),
                AllowedFilter::exact('queue_name'),
                AllowedFilter::exact('correlation_id'),
                AllowedFilter::partial('message'),
            ])
            ->allowedSorts(...['created_at'])
            ->defaultSort('-created_at')
            ->paginate(20);
    }
}
