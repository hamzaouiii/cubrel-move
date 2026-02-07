<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IconController;
use App\Http\Controllers\DropDownListController;

// need to guard this later
Route::get('/icons-test', function () {
    return 'icons api alive';
});

Route::get('/icons', [IconController::class, 'index']);
Route::get('/dropdown-lists', [DropDownListController::class, 'api']);