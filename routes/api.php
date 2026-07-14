<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IconController;
use App\Http\Controllers\DropdownListController;
use App\Http\Controllers\RelationshipLinkController;


Route::middleware(['web', 'auth'])->group(function () {
  Route::get('/icons', [IconController::class, 'index']);
  Route::get('/dropdown-lists', [DropdownListController::class, 'api']);
  Route::get('/related-module-records/{id}', [RelationshipLinkController::class, 'getRecordsForLinking']);
});
