<?php

use App\Http\Controllers\AdelantoController;
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

Route::post('auth/login', [AuthController::class, 'login']);


Route::group([

    'middleware' => 'auth:api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);

});

Route::group([

    'middleware' => 'auth:api',
    'prefix' => 'cliente'

], function ($router) {

    Route::get('/filter', [ClienteController::class, 'filter']);
    Route::get('/', [ClienteController::class, 'listAll']);
    Route::post('/', [ClienteController::class, 'store']);

});


Route::group([

    'middleware' => 'auth:api',
    'prefix' => 'producto'

], function ($router) {

    Route::get('/filter', [ProductoController::class, 'filter']);
    Route::get('/', [ProductoController::class, 'listAll']);

});


Route::group([

    'middleware' => 'auth:api',
    'prefix' => 'venta'

], function ($router) {

    Route::get('/adelanto', [AdelantoController::class, 'findBySerie']);
    Route::post("/adelanto", [AdelantoController::class, 'store']);
    Route::get('/', [TotalVentaController::class, 'index']);
    Route::post("/", [TotalVentaController::class, 'store']);

    Route::get("/reporte-cliente", [TotalVentaController::class, 'recordClient']);
    Route::get("/reporte-vendedor", [TotalVentaController::class, 'recordSeller']);

});
