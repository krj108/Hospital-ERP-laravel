<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Patients\App\Http\Controllers\PatientController;
use Modules\Patients\App\Http\Controllers\PatientMedicalController;

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

Route::group(['middleware' => ['auth:sanctum', 'role:admin|patients admin|doctor']], function() {
    Route::get('/patients', [PatientController::class, 'index']);
});


Route::group(['middleware' => ['auth:sanctum'] , ['role:admin|patients admin']], function() {
    // Route::get('/patients', [PatientController::class, 'index']);
    Route::post('/patients', [PatientController::class, 'store']);
    Route::post('/patients/{patient}', [PatientController::class, 'update']);
    Route::delete('/patients/{patient}', [PatientController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'role:Patient'])->group(function () {
    Route::get('/patient/medical-conditions', [PatientMedicalController::class, 'index']);
    Route::get('/patient/medical-conditions/{id}', [PatientMedicalController::class, 'show']);
});