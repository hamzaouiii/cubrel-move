<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IconController;

Route::get('/icons-test', function () {
    return 'icons api alive';
});

Route::get('/icons', [IconController::class, 'index']);