<?php

use Illuminate\Http\Request;

Route::post('login', 'UsersApiController@login');
Route::post('signup', 'UsersApiController@signup');

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('logout', 'UsersApiController@logout');
    Route::get('user', 'UsersApiController@user');

    Route::get('users', 'UsersApiController@index');
    Route::post('user/create', 'UsersApiController@store');
    Route::post('user/{id}', 'UsersApiController@show');
    Route::put('user/{id}', 'UsersApiController@update');
    Route::delete('user/{id}', 'UsersApiController@delete');
});
