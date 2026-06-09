<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\OficioController;
use App\Http\Controllers\Api\OficioTemplateController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WorkerLogController;
use Illuminate\Support\Facades\Route;

Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('auth/me', [AuthController::class, 'me']);

    Route::apiResource('users', UserController::class);
    Route::patch('users/{user}/restore', [UserController::class, 'restore']);

    Route::apiResource('contacts', ContactController::class)->except('destroy');
    Route::get('contacts/{id}/responsibles', [ContactController::class, 'responsibles']);

    Route::apiResource('oficios', OficioController::class)->except('destroy');
    Route::post('oficios/{oficio}/review', [OficioController::class, 'review']);
    Route::post('oficios/{oficio}/send',   [OficioController::class, 'send']);

    Route::apiResource('oficio-templates', OficioTemplateController::class)->except('destroy');

    Route::get('settings', [SettingsController::class, 'show']);
    Route::put('settings', [SettingsController::class, 'update']);

    Route::apiResource('messages', MessageController::class)->only(['index', 'show']);

    Route::get('worker-logs', [WorkerLogController::class, 'index']);
    Route::post('worker-logs', [WorkerLogController::class, 'store']);
});
