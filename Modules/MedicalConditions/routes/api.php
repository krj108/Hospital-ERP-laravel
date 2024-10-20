<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\MedicalConditions\App\Http\Controllers\MedicalConditionController;

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


Route::group(['middleware' => ['auth:sanctum'] , ['role:doctor']], function() {
    Route::post('/medical-conditions', [MedicalConditionController::class, 'store']);
 
});

Route::group(['middleware' => ['auth:sanctum'] , ['role:doctor|admin']], function() {
    Route::get('/medical-conditions', [MedicalConditionController::class, 'index']);
    Route::put('/medical-conditions/{medicalCondition}', [MedicalConditionController::class, 'update']);
    Route::delete('/medical-conditions/{medicalCondition}', [MedicalConditionController::class, 'destroy']);

});