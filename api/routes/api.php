<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\OficioController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\OficioTemplateController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\WorkerLogController;

Route::apiResource('contacts', ContactController::class)->except('destroy');

Route::get('contacts/{id}/responsibles', [ContactController::class, 'responsibles']);

Route::apiResource('oficios', OficioController::class)->except('destroy');

Route::post('oficios/{oficio}/send', [OficioController::class, 'send']);

Route::apiResource('oficio-templates', OficioTemplateController::class)->except('destroy');

Route::get('settings', [SettingsController::class, 'show']);

Route::put('settings', [SettingsController::class, 'update']);

Route::apiResource('messages', MessageController::class)->only(['index', 'show',]);

Route::post('messages/{message}/send-broker', [MessageController::class, 'sendBroker']);

Route::get('worker-logs', [WorkerLogController::class, 'index']);
Route::post('worker-logs', [WorkerLogController::class, 'store']);
