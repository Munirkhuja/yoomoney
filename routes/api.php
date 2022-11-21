<?php

use App\Http\Controllers\Api\v1\Payment\YoumoneyController;
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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::group(['prefix' => 'payments'], function () {
    Route::group(['prefix' => 'yoo-money'], function () {
        Route::post('/{user_id?}', YoumoneyController::class)->name('payment.yoo_money');
        Route::post('/change_statuses', [YoumoneyController::class, 'change_statuses']);
    });
});
