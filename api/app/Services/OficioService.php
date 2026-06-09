<?php

namespace App\Services;

use App\Enums\OficioStatusEnum;
use App\Models\Oficio;
use Illuminate\Support\Facades\DB;

class OficioService
{
    public function __construct(
        private MessageService $messageService
    ) {}
    public function list()
    {
        return Oficio::with([
            'destinationContact',
            'responsibles',
            'author'
        ])->paginate(20);
    }

    public function getById(Oficio $oficio): Oficio
    {
        return $oficio->load([
            'destinationContact',
            'responsibles',
            'author',
            'rejectionInfo'
        ]);
    }

    public function create(array $data): Oficio
    {
        return DB::transaction(function () use ($data) {

            $oficio = Oficio::create([
                'subject' => $data['subject'],
                'destination_contact_id' => $data['destination_contact_id'],
                'priority' => $data['priority'],
                'content' => $data['content'],
                'status' => OficioStatusEnum::PENDING,
            ]);

            $oficio->responsibles()->sync(
                $data['responsibles']
            );

            return $this->getById($oficio);
        });
    }

    public function update(
        Oficio $oficio,
        array $data
    ): Oficio {

        return DB::transaction(function () use (
            $oficio,
            $data
        ) {

            $oficio->update([
                'subject' => $data['subject'],
                'destination_contact_id' => $data['destination_contact_id'],
                'priority' => $data['priority'],
                'content' => $data['content'],
            ]);

            $oficio->responsibles()->sync(
                $data['responsibles']
            );

            return $this->getById($oficio);
        });
    }

    public function send(Oficio $oficio): array
    {
        if($oficio->status == OficioStatusEnum::SENT){
            return [
                'message' => 'O Ofício já foi enviado'
            ];
        }
        if($oficio->status !== OficioStatusEnum::APPROVED){
            return [
                'message' => 'Ofício deve estar com status Aprovado'
            ];
        }

        return DB::transaction(function () use (
            $oficio
        ) {

            $oficio->load('responsibles');

            $messages = [];

            $messagesSuccess = [];

            foreach (
                $oficio->responsibles
                as $responsible
            ) {

                $message = $oficio->messages()->create([
                    'responsible_id' => $responsible->id,
                ]);

                $messageResponse = $this->messageService->sendBroker($message);

                $messages[] = $message->id;

                if($messageResponse['success'] === true){
                    $messagesSuccess[] = $message->id;
                }
            }

            $oficio->update([
                'status' => OficioStatusEnum::SENT
            ]);

            return [
                'message' => 'Enviado',
                'messages_created' => $messages,
                'messages_success' => $messagesSuccess,
            ];
        });
    }
}
