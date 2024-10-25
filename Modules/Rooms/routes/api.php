<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Rooms\App\Http\Controllers\RoomController;
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
    Route::get('/rooms', [RoomController::class, 'index']); // Accessible by both admin and doctor
    Route::put('/rooms/{room}', [RoomController::class, 'update']); // Accessible by both admin and doctor

    // Only admin can store and destroy
    Route::middleware(['role:admin'])->group(function () {
        Route::post('/rooms', [RoomController::class, 'store']);
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy']);
    });
});