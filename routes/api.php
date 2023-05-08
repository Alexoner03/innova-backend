<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\TotalVentaController;
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

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);

});

Route::group([

    'middleware' => 'api',
    'prefix' => 'cliente'

], function ($router) {

    Route::get('/', [ClienteController::class, 'listAll']);
    Route::get('/filter', [ClienteController::class, 'filter']);

});


Route::group([

    'middleware' => 'api',
    'prefix' => 'producto'

], function ($router) {

    Route::get('/', [ProductoController::class, 'listAll']);
    Route::get('/filter', [ProductoController::class, 'filter']);

});


Route::group([

    'middleware' => 'api',
    'prefix' => 'venta'

], function ($router) {

    Route::get('/', [TotalVentaController::class, 'index']);

});
