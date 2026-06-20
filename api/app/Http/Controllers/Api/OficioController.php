<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewOficioRequest;
use App\Http\Requests\SendOficioRequest;
use App\Http\Requests\StoreOficioRequest;
use App\Http\Requests\UpdateOficioRequest;
use App\Http\Requests\ViewOficioRequest;
use App\Models\Oficio;
use App\Services\OficioService;

class OficioController extends Controller
{
    public function __construct(
        private OficioService $service
    ) {}

    public function index(ViewOficioRequest $request)
    {
        return response()->json($this->service->list());
    }

    public function show(ViewOficioRequest $request, Oficio $oficio)
    {
        return response()->json($this->service->getById($oficio));
    }

    public function store(StoreOficioRequest $request)
    {
        return response()->json(
            $this->service->create($request->validated()),
            201
        );
    }

    public function update(UpdateOficioRequest $request, Oficio $oficio)
    {
        return response()->json(
            $this->service->update($oficio, $request->validated())
        );
    }

    public function review(ReviewOficioRequest $request, Oficio $oficio)
    {
        return response()->json(
            $this->service->review($oficio, $request->validated())
        );
    }

    public function send(SendOficioRequest $request, Oficio $oficio)
    {
        return response()->json(
            $this->service->send($oficio)
        );
    }
}
