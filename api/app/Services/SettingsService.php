<?php

namespace App\Services;

use App\Models\OficioSetting;

class SettingsService
{
    public function __construct(
        private OficioAuthorizedSignerService $signerService
    ) {}

    public function get(): array
    {
        $settings = OficioSetting::first();

        return [
            'header'             => $settings?->header,
            'footer'             => $settings?->footer,
            'authorized_signers' => $this->signerService->list(),
        ];
    }

    public function update(array $data): array
    {
        $settings = OficioSetting::first();
        $fields   = ['header' => $data['header'], 'footer' => $data['footer']];

        if (!$settings) {
            $settings = OficioSetting::create($fields);
        } else {
            $settings->update($fields);
        }

        return [
            'header'             => $settings->header,
            'footer'             => $settings->footer,
            'authorized_signers' => $this->signerService->replaceAll($data['signers'] ?? []),
        ];
    }
}
