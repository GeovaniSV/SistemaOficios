<?php

namespace App\Services;

use App\Models\SmtpConfig;
use App\Payloads\SmtpConfigUpdatedPayload;

class SmtpConfigService
{
    public function __construct(
        private RabbitmqService $rabbitmq
    ) {}

    public function show(): array
    {
        return $this->toFrontendArray(SmtpConfig::first());
    }

    public function update(array $data): array
    {
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $config = SmtpConfig::first();

        if (!$config) {
            $config = SmtpConfig::create($data);
        } else {
            $config->update($data);
        }

        $this->rabbitmq->publish(
            payload: new SmtpConfigUpdatedPayload(),
            queue: config('rabbitmq.email_queue'),
            exchange: '',
            routingKey: config('rabbitmq.email_queue'),
        );

        return $this->toFrontendArray($config);
    }

    public function forBroker(): ?array
    {
        $config = SmtpConfig::first();

        if (!$config) {
            return null;
        }

        return [
            'host'       => $config->host,
            'port'       => $config->port,
            'username'   => $config->username,
            'password'   => $config->password,
            'from_name'  => $config->from_name,
            'from_email' => $config->from_email,
            'use_tls'    => $config->use_tls,
        ];
    }

    private function toFrontendArray(?SmtpConfig $config): array
    {
        return [
            'host'         => $config?->host,
            'port'         => $config?->port,
            'username'     => $config?->username,
            'from_name'    => $config?->from_name,
            'from_email'   => $config?->from_email,
            'use_tls'      => $config?->use_tls ?? false,
            'has_password' => !empty($config?->password),
        ];
    }
}
