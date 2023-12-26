<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/{any_path?}', function () {
        return view('index');
})->where('any_path', '(.*)');

Route::get("favicon/{favicon}", function ($favicon) {
//        header("Content-Type: image/jpeg");

    return Storage::get("favicon/" . $favicon);
})->name("favicon");