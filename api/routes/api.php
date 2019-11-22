<?php

use Illuminate\Http\Request;

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

Route::group(['prefix'=>'orders', 'namespace'=>'API'], function() {
    Route::post('/', 'OrderController@store');
    Route::patch('/{id}', 'OrderController@update');
    Route::get('/', 'OrderController@show');
});
   

    