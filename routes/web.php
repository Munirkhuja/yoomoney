<?php

use App\Http\Controllers\Api\v1\Payment\YoumoneyController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});
Route::group(['prefix' => 'payments'], function () {
    Route::group(['prefix' => 'yoo-money'], function () {
        Route::get('check/{uniq_id}', [YoumoneyController::class, 'payment_check'])
            ->name('payment.redirect');
        Route::get('/', [YoumoneyController::class, 'index']);
    });
});
