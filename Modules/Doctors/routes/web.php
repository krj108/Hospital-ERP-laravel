<?php

use Illuminate\Support\Facades\Route;
use Modules\Doctors\App\Http\Controllers\DoctorsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([], function () {
    Route::resource('doctors', DoctorsController::class)->names('doctors');
});
