<?php

use App\Http\Controllers\SoapTestController;
use App\Http\Controllers\TrainRouteController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/train-route', function () {
    return view('welcome');
});

Route::post('/train-route', [TrainRouteController::class, 'getRoute'])->name('train.route');
