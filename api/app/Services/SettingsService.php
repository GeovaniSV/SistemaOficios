<?php

namespace App\Services;

use App\Models\OficioSetting;

class SettingsService
{
    public function get()
    {
        return OficioSetting::first();
    }

    public function update(
        array $data
    ): OficioSetting {

        $settings = OficioSetting::first();

        if (!$settings) {

            return OficioSetting::create($data);
        }

        $settings->update($data);

        return $settings;
    }
}
