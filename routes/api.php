<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\V1\Admin\OrganizationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Rutas públicas
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
    });

    // Rutas protegidas con JWT
    Route::middleware(['jwt.auth'])->group(function () {
        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::get('me', [AuthController::class, 'me']);
        });

        // Admin Routes
        Route::prefix('admin')->group(function () {
            Route::apiResource('organizations', OrganizationController::class);
        });
    });
});
