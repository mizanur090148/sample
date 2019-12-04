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
/*
Route::get('/colors', 'ApiColorProvider@colorList');
Route::post('/colors/save', 'ApiColorProvider@saveColor');*/

Route::apiResource('/buyers', 'BuyerController');
Route::get('/search/buyers/{field}/{query}', 'BuyerController@searchBuyers');

Route::apiResource('/colors', 'ColorController');
Route::get('/search/colors/{field}/{query}', 'ColorController@searchColors');

Route::apiResource('/sizes', 'SizeController');
Route::get('/search/sizes/{field}/{query}', 'SizeController@searchSizes');

Route::apiResource('/users', 'UserController');
Route::get('/search/users/{field}/{query}', 'UserController@searchUsers');