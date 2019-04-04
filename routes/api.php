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

Route::post('/login', 'Api\JWTController@login')->name('api.auth.login');
Route::post('/register', 'Api\JWTController@register')->name('api.auth.register');

Route::group(['middleware' => ['jwt.auth', 'jwt.refresh']], function () {
    Route::post('/logout', 'Api\JWTController@logout')->name('api.auth.logout');

    Route::get('/sns-responses/{type}', 'Api\SnsController@index')->name('api.sns.index');
    Route::delete('/sns-responses', 'Api\SnsController@destroy')->name('api.sns.destroy');
});
