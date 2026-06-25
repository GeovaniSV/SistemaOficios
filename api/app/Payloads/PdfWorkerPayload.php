<?php

namespace App\Payloads;

use App\Models\Message;
use App\Models\OficioSetting;

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
        public string $oficioHeader,
        public string $oficioFooter,
        public int    $userId,
        public string $hash,
    ) {}

    public static function fromMessage(Message $message): self
    {
        $oficio      = $message->oficio;
        $responsible = $message->responsible;
        $contact     = $responsible->contact;
        $setting = OficioSetting::first();

        return new self(
            oficioNumero:                 $oficio->number,
            oficioDestinatarioTratamento: $responsible->treatment ?? '',
            oficioDestinatarioNome:       $responsible->name,
            oficioDestinatarioCargo:      $responsible->position ?? '',
            oficioDestinatarioInstituicao:$contact->name ?? '',
            oficioAssunto:                $oficio->subject,
            oficioCorpo:                  $oficio->content,
            oficioDestinatario:           $responsible->email,
            oficioAutor:                  $oficio->author->name,
            oficioAutorCargo:             $oficio->author->position->name ?? '',
            oficioHeader:                 $setting?->header ?? '',
            oficioFooter:                 $setting?->footer ?? '',
            userId:                       $oficio->author->id,
            hash:                         $message->pdf_hash,
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
            'oficioHeader'                  => $this->oficioHeader,
            'oficioFooter'                  => $this->oficioFooter,
            'hash'                          => $this->hash,
        ];
    }
}
