<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOficioTemplateRequest;
use App\Http\Requests\UpdateOficioTemplateRequest;
use App\Models\OficioTemplate;
use App\Services\OficioTemplateService;

class OficioTemplateController extends Controller
{
    public function __construct(
        private OficioTemplateService $service
    ) {}

    public function index()
    {
        return response()->json(
            $this->service->list()
        );
    }

    public function show(
        OficioTemplate $oficioTemplate
    ) {

        return response()->json(
            $this->service->getById(
                $oficioTemplate
            )
        );
    }

    public function store(
        StoreOficioTemplateRequest $request
    ) {

        return response()->json(
            $this->service->create(
                $request->validated()
            ),
            201
        );
    }

    public function update(
        UpdateOficioTemplateRequest $request,
        OficioTemplate $oficioTemplate
    ) {

        return response()->json(
            $this->service->update(
                $oficioTemplate,
                $request->validated()
            )
        );
    }
}
