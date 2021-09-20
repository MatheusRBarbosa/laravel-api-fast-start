<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:300,1'])->group(function () {
    Route::get('ping', function () {
        return "pong";
    });
});
