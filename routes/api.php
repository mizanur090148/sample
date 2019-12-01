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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/colors', 'ApiColorProvider@colorList');
Route::post('/colors/save', 'ApiColorProvider@saveColor');


Route::apiResource('/sizes', 'SizeController');
Route::get('/search/sizes/{field}/{query}', 'SizeController@searchSizes');