<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkerLogRequest;
use App\Http\Requests\ViewWorkerLogRequest;
use App\Services\WorkerLogService;

class WorkerLogController extends Controller
{
    public function __construct(
        private WorkerLogService $service
    ) {}

    public function index(ViewWorkerLogRequest $request)
    {
        return response()->json(
            $this->service->list()
        );
    }

    public function store(StoreWorkerLogRequest $request)
    {
        return response()->json(
            $this->service->store($request->validated()),
            201
        );
    }
}
