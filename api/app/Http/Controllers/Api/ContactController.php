<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyContactRequest;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Services\ContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct(
        private ContactService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $isActive = null;

        if ($request->has('is_active')) {
            $isActive = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        return response()->json(
            $this->service->list($isActive)
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

    public function destroy(
        DestroyContactRequest $request,
        int $id
    ): JsonResponse {

        $activate = filter_var($request->input('activate', false), FILTER_VALIDATE_BOOLEAN);

        $contact = $this->service->toggleActive($id, $activate);

        return response()->json($contact);
    }
}
