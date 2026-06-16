<?php

namespace App\Payloads;

use App\Models\Message;

final readonly class PdfWorkerPayload implements \JsonSerializable
{
    public function __construct(
        public string $oficioNumero,
        public string $oficioDestinatarioTratamento,
        public string $oficioDestinatarioNome,
        public string $oficioDestinatarioCargo,
        public string $oficioDestinatarioInstituicao,
        public string $oficioAssunto,
        public string $oficioCorpo,
        public string $oficioDestinatario,
        public string $oficioAutor,
        public string $oficioAutorCargo,
        public int    $userId,
    ) {}

    public static function fromMessage(Message $message): self
    {
        $oficio      = $message->oficio;
        $contact     = $oficio->destinationContact;
        $responsible = $message->responsible;

        return new self(
            oficioNumero:                 (string) $oficio->id,
            oficioDestinatarioTratamento: $responsible->treatment ?? '',
            oficioDestinatarioNome:       $contact->name,
            oficioDestinatarioCargo:      $contact->position ?? '',       // campo ainda não existe no Contact
            oficioDestinatarioInstituicao:$contact->institution ?? '',    // campo ainda não existe no Contact
            oficioAssunto:                $oficio->subject,
            oficioCorpo:                  $oficio->content,
            oficioDestinatario:           $responsible->email,
            oficioAutor:                  $responsible->name,
            oficioAutorCargo:             $responsible->position ?? '',
            userId:                       $message->id,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'oficioNumero'                  => $this->oficioNumero,
            'oficioDestinatarioTratamento'  => $this->oficioDestinatarioTratamento,
            'oficioDestinatarioNome'        => $this->oficioDestinatarioNome,
            'oficioDestinatarioCargo'       => $this->oficioDestinatarioCargo,
            'oficioDestinatarioInstituicao' => $this->oficioDestinatarioInstituicao,
            'oficioAssunto'                 => $this->oficioAssunto,
            'oficioCorpo'                   => $this->oficioCorpo,
            'oficioDestinatario'            => $this->oficioDestinatario,
            'oficioAutor'                   => $this->oficioAutor,
            'oficioAutorCargo'              => $this->oficioAutorCargo,
            'userId'                        => $this->userId,
        ];
    }
}
