<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ImportController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\TestingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register')->name('register');
    Route::post('/login', 'login')->name('login');
    Route::post('/logout', 'logout')->name('logout');
});


Route::middleware(['api.auth'])->group(function () {
    Route::resource('testing', TestingController::class)->only('index', 'store', 'show', 'update', 'destroy');

    Route::prefix('import')->controller(ImportController::class)->group(function () {
        Route::post('/store', 'import')->name('store');
    });

    Route::controller(PatientController::class)->group(function () {
        Route::post('/patientResults', 'patientResults')->name('patientResults');
    });
});
