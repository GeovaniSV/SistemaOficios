<?php

namespace App\Payloads;

final readonly class SmtpConfigUpdatedPayload implements \JsonSerializable
{
    public function jsonSerialize(): array
    {
        return [
            'event' => 'SMTP_CONFIG_UPDATED',
        ];
    }
}
