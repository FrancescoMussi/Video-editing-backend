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

Route::group(['middleware' => ['auth:api']], function () {

    Route::get('user', function (Request $request) {
        return $request->user()->id;
    });

    Route::post('concat_videos', 'VideoController@concat_videos');
    Route::post('logout', 'AuthController@logout');
    Route::post('admin_access', 'AuthController@admin_access');
});

Route::post('login', 'AuthController@login');
Route::post('register', 'AuthController@register');

