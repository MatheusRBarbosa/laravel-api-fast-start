<?php

use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;


Route::middleware(['throttle:300,1'])->group(function () {
    Route::post('signup', [UserController::class, 'store']);

    Route::post('user/forgot-password', [UserController::class, 'forgotPassword']);
    Route::post('user/validate-code', [UserController::class, 'validateCode']);
    Route::post('user/change-password', [UserController::class, 'changePassword']);
});
