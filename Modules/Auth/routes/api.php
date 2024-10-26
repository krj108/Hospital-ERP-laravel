<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Auth\App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login']);


Route::group(['middleware' => 'auth:sanctum'], function() {
    Route::post('/logout', [AuthController::class, 'logout']); // Logout route
    Route::get('/me', [AuthController::class, 'me']); // Get the authenticated user's details
    Route::put('/me', [AuthController::class, 'updateProfile']); // Update the authenticated user's name or email
    Route::put('/me/password', [AuthController::class, 'updatePassword']); // Update the authenticated user's password
});
