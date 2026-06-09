<?php

namespace App\Services;

use App\Enums\OficioStatusEnum;
use App\Models\Oficio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OficioService
{
    public function __construct(
        private MessageService $messageService,
        private RejectionInfoService $rejectionInfoService
    ) {}

    public function list()
    {
        return Oficio::with([
            'destinationContact',
            'responsibles',
            'author',
        ])->paginate(20);
    }

    public function getById(Oficio $oficio): Oficio
    {
        $oficio->load([
            'destinationContact',
            'responsibles',
            'author',
            'rejectionInfos.author',
        ]);

        $oficio->setRelation(
            'rejectionInfos',
            $oficio->rejectionInfos->groupBy(fn($r) => $r->type->value)
        );

        return $oficio;
    }

    public function create(array $data): Oficio
    {
        return DB::transaction(function () use ($data) {
            $oficio = Oficio::create([
                'subject'                => $data['subject'],
                'destination_contact_id' => $data['destination_contact_id'],
                'priority'               => $data['priority'],
                'content'                => $data['content'],
                'department'             => $data['department'],
                'author_id'              => Auth::id(),
                'status'                 => OficioStatusEnum::DRAFT,
            ]);

            $oficio->responsibles()->sync($data['responsibles']);

            return $this->getById($oficio);
        });
    }

    public function update(Oficio $oficio, array $data): Oficio
    {
        $allowed = [OficioStatusEnum::DRAFT, OficioStatusEnum::RETURNED];

        if (!in_array($oficio->status, $allowed, true)) {
            abort(409, 'Esta ação não é permitida para o status atual do ofício');
        }

        return DB::transaction(function () use ($oficio, $data) {
            $this->updateMetadata($oficio, $data);

            if (!empty($data['submit'])) {
                $oficio->update(['status' => OficioStatusEnum::PENDING]);
            }

            return $this->getById($oficio);
        });
    }

    public function review(Oficio $oficio, array $data): Oficio
    {
        if ($oficio->status !== OficioStatusEnum::PENDING) {
            abort(409, 'Esta ação não é permitida para o status atual do ofício');
        }

        return DB::transaction(function () use ($oficio, $data) {
            $this->updateMetadata($oficio, $data);

            $newStatus = OficioStatusEnum::from($data['status']);

            if (in_array($newStatus, [OficioStatusEnum::REJECTED, OficioStatusEnum::RETURNED], true)) {
                $this->rejectionInfoService->create($oficio, $data['reason'], $newStatus);
            }

            $oficio->update(['status' => $newStatus]);

            return $this->getById($oficio);
        });
    }

    public function send(Oficio $oficio): array
    {
        if ($oficio->status === OficioStatusEnum::SENT) {
            abort(409, 'O ofício já foi enviado');
        }

        if ($oficio->status !== OficioStatusEnum::APPROVED) {
            abort(409, 'Esta ação não é permitida para o status atual do ofício');
        }

        return DB::transaction(function () use ($oficio) {
            $oficio->load('responsibles');

            $messages        = [];
            $messagesSuccess = [];

            foreach ($oficio->responsibles as $responsible) {
                $message = $oficio->messages()->create([
                    'responsible_id' => $responsible->id,
                ]);

                $messageResponse = $this->messageService->sendBroker($message);

                $messages[] = $message->id;

                if ($messageResponse['success'] === true) {
                    $messagesSuccess[] = $message->id;
                }
            }

            $oficio->update(['status' => OficioStatusEnum::SENT]);

            return [
                'message'          => 'Enviado',
                'messages_created' => $messages,
                'messages_success' => $messagesSuccess,
            ];
        });
    }

    private function updateMetadata(Oficio $oficio, array $data): void
    {
        $fields = [];
        foreach (['subject', 'destination_contact_id', 'priority', 'content', 'department'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[$field] = $data[$field];
            }
        }

        if (!empty($fields)) {
            $oficio->update($fields);
        }

        if (array_key_exists('responsibles', $data)) {
            $oficio->responsibles()->sync($data['responsibles']);
        }
    }
}
