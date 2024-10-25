<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Departments\App\Http\Controllers\DepartmentController;

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

// Modules/Departments/Routes/api.php



Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/departments', [DepartmentController::class, 'index']); // Accessible by both admin and doctor

    // Only admin can store, update, and destroy
    Route::middleware(['role:admin'])->group(function () {
        Route::post('/departments', [DepartmentController::class, 'store']);
        Route::put('/departments/{department}', [DepartmentController::class, 'update']);
        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy']);
    });
});
