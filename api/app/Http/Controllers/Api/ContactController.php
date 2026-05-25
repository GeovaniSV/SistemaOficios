<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Services\ContactService;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function __construct(
        private ContactService $service
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(
            $this->service->list()
        );
    }

    public function responsibles(
        int $id
    ): JsonResponse {

        return response()->json(
            $this->service->getResponsibles($id)
        );
    }

    public function show(
        int $id
    ): JsonResponse {

        return response()->json(
            $this->service->getById($id)
        );
    }

    public function store(
        StoreContactRequest $request
    ): JsonResponse {

        $contact = $this->service->create(
            $request->validated()
        );

        return response()->json(
            $contact,
            201
        );
    }

    public function update(
        UpdateContactRequest $request,
        int $id
    ): JsonResponse {

        $contact = $this->service->update(
            $id,
            $request->validated()
        );

        return response()->json($contact);
    }
}
