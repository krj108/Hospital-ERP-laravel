<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Service\App\Http\Controllers\ServiceController;


/*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // Routes accessible by both admin and doctor
    Route::get('/services', [ServiceController::class, 'index'])->middleware('role:admin|doctor');
    Route::get('/services/{id}', [ServiceController::class, 'show'])->middleware('role:admin|doctor');

    // Routes accessible only by admin
    Route::middleware('role:admin')->group(function () {
        Route::post('/services', [ServiceController::class, 'store']);
        Route::put('/services/{service}', [ServiceController::class, 'update']);
        Route::delete('/services/{service}', [ServiceController::class, 'destroy']);
    });
});

