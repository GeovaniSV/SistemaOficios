<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSmtpConfigRequest;
use App\Services\SmtpConfigService;

class SmtpConfigController extends Controller
{
    public function __construct(
        private SmtpConfigService $service
    ) {}

    public function show()
    {
        return response()->json(
            $this->service->show()
        );
    }

    public function update(UpdateSmtpConfigRequest $request)
    {
        return response()->json(
            $this->service->update($request->validated())
        );
    }

    public function brokerShow()
    {
        $config = $this->service->forBroker();

        if (!$config) {
            return response()->json(['message' => 'SMTP não configurado'], 404);
        }

        return response()->json($config);
    }
}
