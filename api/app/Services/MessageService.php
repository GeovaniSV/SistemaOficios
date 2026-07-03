<?php

namespace App\Services;

use App\Enums\MessageStatusEnum;
use App\Models\Message;
use Carbon\Carbon;
use App\Payloads\PdfWorkerPayload;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MessageService
{
    public function __construct(
        private RabbitmqService $rabbitmq
    ) {}
    public function list()
    {
        return Message::with([
            'oficio',
            'responsible.contact.address',
        ])->paginate(20);
    }

    public function getById(
        Message $message
    ): Message {

        return $message->load([
            'oficio',
            'responsible.contact.address',
        ]);
    }

    public function sendBroker(Message $message): array
    {
        $message->load([
            'oficio',
            'responsible.contact.address',
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

    public function validateByHash(string $codigo): array
    {
        $message = Message::where('validation_hash', $codigo)->first();

        if (!$message) {
            return [
                'success' => false,
                'message' => 'don\'t exists a PDF for this hash',
            ];
        }

        $path = "oficios/{$message->pdf_hash}.pdf";

        if (!Storage::disk('r2')->exists($path)) {
            return [
                'success' => false,
                'message' => 'don\'t exists a PDF for this hash',
            ];
        }

        $url = Storage::disk('r2')->temporaryUrl($path, now()->addMinutes(60));

        return [
            'success' => true,
            'message' => 'a PDF exists for this hash',
            'path'    => $url,
        ];
    }

    public function downloadPdf(Message $message): StreamedResponse
    {
        if (!$message->pdf_hash) {
            abort(404, 'PDF não disponível para esta mensagem');
        }

        $path = "oficios/{$message->pdf_hash}.pdf";

        if (!Storage::disk('r2')->exists($path)) {
            abort(404, 'PDF ainda não foi gerado');
        }

        $message->load('oficio');
        $filename = 'oficio-' . str_replace('/', '-', $message->oficio->number) . '.pdf';

        return Storage::disk('r2')->download($path, $filename);
    }
}
