<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\PatientsAdmin\App\Http\Controllers\PatientVisitController;
use Modules\PatientsAdmin\App\Http\Controllers\PatientsAdminController;
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

Route::group(['middleware' => ['auth:sanctum', 'role:admin']], function() {
    // Route to add a new Patients Admin
    Route::post('/patients-admin', [PatientsAdminController::class, 'store']);
    
    // Route to get all Patients Admins
    Route::get('/patients-admin', [PatientsAdminController::class, 'index']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    
    Route::middleware('role:admin|patients admin')->group(function () {
        Route::get('/patient-visits', [PatientVisitController::class, 'index']);
        Route::post('/patient-visits', [PatientVisitController::class, 'store']);
        Route::put('/patient-visits/{visit}', [PatientVisitController::class, 'update']);
    });

    
    Route::middleware('role:admin')->group(function () {
        Route::delete('/patient-visits/{visit}', [PatientVisitController::class, 'destroy']);
    });
});