<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Services\MessageService;

class MessageController extends Controller
{
    public function __construct(
        private MessageService $service
    ) {}

    public function index()
    {
        return response()->json(
            $this->service->list()
        );
    }

    public function show(
        Message $message
    ) {

        return response()->json(
            $this->service->getById(
                $message
            )
        );
    }

    // Função para testes e debugs da conexão com o broker

    /* public function sendBroker(
        Message $message
    ) {

        return response()->json(
            $this->service->sendBroker(
                $message
            )
        );
    } */
}
