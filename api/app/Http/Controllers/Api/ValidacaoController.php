<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidacaoRequest;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;

class ValidacaoController extends Controller
{
    public function __construct(
        private MessageService $service
    ) {}

    public function validate(ValidacaoRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->validateByHash($request->input('codigo'))
        );
    }
}
