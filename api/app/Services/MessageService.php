<?php

namespace App\Services;

use App\Enums\MessageStatusEnum;
use App\Models\Message;
use App\Models\OficioSetting;
use Carbon\Carbon;

class MessageService
{
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

    public function sendBroker(
        Message $message
    ): array {

        $message->load([
            'oficio.destinationContact.address',
            'responsible',
        ]);

        $settings = OficioSetting::first();

        $payload = [
            'message' => [
                'id' => $message->id,
                'status' => $message->status->value,
            ],
            'oficio' => [
                'id' => $message->oficio->id,
                'subject' => $message->oficio->subject,
                'priority' => $message->oficio->priority->value,
                'content' => $message->oficio->content,
                'status' => $message->oficio->status->value,
            ],
            'destination_contact' => [
                'id' => $message->oficio
                    ->destinationContact
                    ->id,
                'name' => $message->oficio
                    ->destinationContact
                    ->name,
                'doc' => $message->oficio
                    ->destinationContact
                    ->formatted_doc,
                'type' => $message->oficio
                    ->destinationContact
                    ->type
                    ->value,
                'address' => [
                    'cep' => $message->oficio
                        ->destinationContact
                        ->address
                        ->cep,
                    'logradouro' => $message->oficio
                        ->destinationContact
                        ->address
                        ->logradouro,
                    'numero' => $message->oficio
                        ->destinationContact
                        ->address
                        ->numero,
                    'bairro' => $message->oficio
                        ->destinationContact
                        ->address
                        ->bairro,
                    'cidade' => $message->oficio
                        ->destinationContact
                        ->address
                        ->cidade,
                    'estado' => $message->oficio
                        ->destinationContact
                        ->address
                        ->estado,
                ]
            ],
            'responsible' => [
                'id' => $message->responsible->id,
                'name' => $message->responsible->name,
                'email' => $message->responsible->email,
                'treatment' => $message->responsible->treatment,
                'position' => $message->responsible->position,
                'department' => $message->responsible->department,
            ],
            'settings' => [
                'statement_text' => $settings?->statement_text,
            ],
        ];

        // Uga Buga

        $message->update([
            'status' => MessageStatusEnum::SENT,
            'sent_at' => Carbon::now(),
        ]);

        return [
            'message' => 'Payload enviado',
            'payload' => $payload,
        ];
    }
}
