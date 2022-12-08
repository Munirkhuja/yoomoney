<?php

use App\Http\Controllers\Api\v1\Payment\YooMoneyController;
use App\Http\Controllers\Api\v1\TrainingNeural\SmartDevController;
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
Route::get('/dataset/{dataset_id}', [SmartDevController::class, 'send_dataset']);
Route::group(['prefix' => 'payments'], function () {
    Route::group(['prefix' => 'yoo-money'], function () {
        Route::post('/{user_id?}', YooMoneyController::class)->name('payment.yoo_money');
        Route::post('/change_statuses', [YooMoneyController::class, 'change_statuses']);
    });
});
