<?php

use App\Http\Controllers\UsersApiController;
use Illuminate\Support\Facades\Route;

Route::post('login', [UsersApiController::class, 'login']);
Route::post('signup', [UsersApiController::class, 'signup']);

Route::middleware('auth:api')->group(function () {
    Route::get('logout', [UsersApiController::class, 'logout']);
    Route::get('user', [UsersApiController::class, 'user']);

    Route::get('users', [UsersApiController::class, 'index']);
    Route::post('user/create', [UsersApiController::class, 'store']);
    Route::post('user/{id}', [UsersApiController::class, 'show']);
    Route::put('user/{id}', [UsersApiController::class, 'update']);
    Route::delete('user/{id}', [UsersApiController::class, 'delete']);
});
