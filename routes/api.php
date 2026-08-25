<?php

use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\BootstrapController;
use App\Http\Controllers\Api\LicenseController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\SyncController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [ApiAuthController::class, 'login']);
Route::post('/license/activate', [LicenseController::class, 'activate']);
Route::post('/license/renew', [LicenseController::class, 'renew']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [ApiAuthController::class, 'logout']);
    Route::get('/bootstrap', [BootstrapController::class, 'show']);

    Route::get('/shifts/current', [ShiftController::class, 'current']);
    Route::post('/shifts/open', [ShiftController::class, 'open']);
    Route::post('/shifts/close', [ShiftController::class, 'close']);

    Route::get('/sessions/active', [SessionController::class, 'active']);
    Route::post('/sessions/entry', [SessionController::class, 'entry']);
    Route::post('/sessions/exit', [SessionController::class, 'exit']);
    Route::post('/sessions/preview', [SessionController::class, 'preview']);

    Route::post('/sync/push', [SyncController::class, 'push']);
    Route::get('/sync/pull', [SyncController::class, 'pull']);
});
