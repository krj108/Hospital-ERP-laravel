<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Doctors\App\Http\Controllers\DoctorController;
use Modules\Doctors\App\Http\Controllers\DoctorScheduleController;
use Modules\Doctors\App\Http\Controllers\SpecializationController;

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

Route::middleware('auth:sanctum')->group(function () {

Route::get('/doctor-schedules', [DoctorScheduleController::class, 'index']);

});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

    // Specializations Routes
    Route::get('/specializations', [SpecializationController::class, 'index']);
    Route::post('/specializations', [SpecializationController::class, 'store']);
    Route::put('/specializations/{specialization}', [SpecializationController::class, 'update']);
    Route::delete('/specializations/{specialization}', [SpecializationController::class, 'destroy']);

    // Doctors Routes
    Route::post('/doctors', [DoctorController::class, 'store']);
    Route::post('/doctors/{doctor}', [DoctorController::class, 'update']);
    Route::delete('/doctors/{doctor}', [DoctorController::class, 'destroy']);

    // Doctor Schedules Routes
    Route::get('/doctor-schedules', [DoctorScheduleController::class, 'index']);
    Route::post('/doctor-schedules', [DoctorScheduleController::class, 'store']);
    Route::put('/doctor-schedules/{schedule}', [DoctorScheduleController::class, 'update']);
    Route::delete('/doctor-schedules/{schedule}', [DoctorScheduleController::class, 'destroy']);
});



Route::middleware(['auth:sanctum', 'role:doctor|admin'])->group(function () {

    Route::get('/doctors', [DoctorController::class, 'index']);
    
    });