<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Services\SettingsService;

class SettingsController extends Controller
{
    public function __construct(
        private SettingsService $service
    ) {}

    public function show()
    {
        return response()->json(
            $this->service->get()
        );
    }

    public function update(
        UpdateSettingsRequest $request
    ) {

        return response()->json(
            $this->service->update(
                $request->validated()
            )
        );
    }
}
