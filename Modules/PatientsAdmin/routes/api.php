<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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