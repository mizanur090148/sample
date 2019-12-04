<?php

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

/*Route::get('/', function () {
    return view('welcome');
});

Auth::routes();
*/
Route::get('/dashboard', 'DashboardController@dashboard');
Route::get('buyers', 'BuyerController');
Route::get('colors', 'ColorController');
Route::get('sizes', 'SizeController');
Route::get('users', 'UserController');
Route::resource('sample-codes', 'SampleCodeController');

Route::get('/', 'AuthController@login');
Route::post('/login-post', 'AuthController@loginPost');	
Route::get('/logout', 'AuthController@logout');	
