<?php

namespace App\Services;

use App\Enums\MessageStatusEnum;
use App\Models\Message;
use Carbon\Carbon;
use App\Payloads\PdfWorkerPayload;

class MessageService
{
    public function __construct(
        private RabbitmqService $rabbitmq
    ) {}
    public function list()
    {
        return Message::with([
            'oficio.destinationContact.address',
            'responsible',
        ])->paginate(20);
    }

    public function getById(
        Message $message
    ): Message {

        return $message->load([
            'oficio.destinationContact.address',
            'responsible',
        ]);
    }

    public function sendBroker(Message $message): array
    {
        $message->load([
            'oficio.destinationContact.address',
            'responsible',
        ]);

        $payload = PdfWorkerPayload::fromMessage($message);

        $this->rabbitmq->publish($payload);

        $message->update([
            'status'  => MessageStatusEnum::SENT,
            'sent_at' => Carbon::now(),
        ]);

        return [
            'success' => true,
            'message' => 'Payload enviado',
        ];
    }
}
