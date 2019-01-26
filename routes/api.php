<?php

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

Route::post('/login', 'Api\JWTController@login');
Route::post('/register', 'Api\JWTController@register');


Route::group(['middleware' => ['jwt.auth', 'jwt.refresh']], function () {
    Route::post('/logout', 'Api\JWTController@logout');
//    Route::post('/me', 'Api\JWTController@me');
});
