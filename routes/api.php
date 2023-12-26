<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\OtherController;
use App\Http\Controllers\SoundPredictController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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
Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/', function (Request $request) {
        return response()->json(['it works']);
    });

    Route::middleware('auth:sanctum')->get('/user', [OtherController::class, 'getUser']);

    Route::controller(AuthController::class)->prefix("auth")->group(function () {
        Route::post('login', 'login');
        Route::post('register', 'register');
        Route::post('logout', 'logout');
        Route::post('refresh', 'refresh');
        Route::get('exist', 'exist');
        Route::post('glogin', 'googleLogin');
        Route::patch('name', 'changeName');
        Route::post('picture', 'changePicture');
        Route::get('logindata', 'getLoginData');
    });

    Route::controller(SoundPredictController::class)->prefix("predict")->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::post('/history', 'addToHistory');
        Route::delete('/history', 'deleteHistory');
    });


});

