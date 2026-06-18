<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IconController;
use App\Http\Controllers\DropdownListController;
use App\Http\Controllers\RelationshipLinkController;

// IMPORTANT: These endpoints must be guarded before going into production. Right now they are accisble with no authentication which is a security risk
Route::get('/icons-test', function () {
  return 'icons api alive';
});

Route::get('/icons', [IconController::class, 'index']);
Route::get('/dropdown-lists', [DropdownListController::class, 'api']);
Route::get('/related-module-records/{id}', [RelationshipLinkController::class, 'getRecordsForLinking']);
