<?php

namespace App\Services;

use App\Enums\OficioStatusEnum;
use App\Models\Oficio;
use Illuminate\Support\Facades\DB;

class OficioService
{
    public function list()
    {
        return Oficio::with([
            'destinationContact',
            'responsibles'
        ])->paginate(20);
    }

    public function getById(Oficio $oficio): Oficio
    {
        return $oficio->load([
            'destinationContact',
            'responsibles'
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
        return DB::transaction(function () use (
            $oficio
        ) {

            $oficio->load('responsibles');

            $messages = [];

            foreach (
                $oficio->responsibles
                as $responsible
            ) {

                $message = $oficio->messages()->create([
                    'responsible_id' => $responsible->id,
                ]);

                $messages[] = $message->id;
            }

            $oficio->update([
                'status' => OficioStatusEnum::COMPLETED
            ]);

            return [
                'message' => 'Enviado',
                'messages_created' => $messages,
            ];
        });
    }
}
