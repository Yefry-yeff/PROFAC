<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(\App\Http\Middleware\AuthenticatePosIntegration::class)->prefix('pos-integration')->group(function () {
    Route::post('/events', [\App\Http\Controllers\PosIntegrationController::class, 'event']);
    Route::get('/catalog/{entity}', [\App\Http\Controllers\PosIntegrationController::class, 'catalog']);
    Route::get('/products/{product}/image', [\App\Http\Controllers\PosIntegrationController::class, 'productImage'])->whereNumber('product');
});
Route::middleware(\App\Http\Middleware\AuthenticatePosIntegration::class.':health')
    ->get('/pos-integration/health', [\App\Http\Controllers\PosIntegrationController::class, 'health']);
