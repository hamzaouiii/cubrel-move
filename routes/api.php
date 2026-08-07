<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IconController;
use App\Http\Controllers\DropdownListController;
use App\Http\Controllers\RelationshipLinkController;
use App\Http\Controllers\EmailInboundWebhookController;
use App\Http\Controllers\Api\V1\RecordController as ApiV1RecordController;
use App\Http\Controllers\Api\V1\RelationshipController as ApiV1RelationshipController;
use App\Http\Middleware\SetLocaleFromAcceptLanguage;


Route::middleware(['web', 'auth'])->group(function () {
  Route::get('/icons', [IconController::class, 'index']);
  Route::get('/dropdown-lists', [DropdownListController::class, 'api']);
  Route::get('/related-module-records/{id}', [RelationshipLinkController::class, 'getRecordsForLinking']);
});

// rest api v1 routes
Route::prefix('v1')->middleware([SetLocaleFromAcceptLanguage::class, 'auth:sanctum', 'throttle:api'])->group(function () {

Route::get('/{module}/relationships', [ApiV1RelationshipController::class, 'index']);
  Route::post('/{module}/{id}/relationships/{relationship}', [ApiV1RelationshipController::class, 'link']);
  Route::delete('/{module}/{id}/relationships/{relationship}/{relatedId}', [ApiV1RelationshipController::class, 'unlink']);

  Route::get('/{module}', [ApiV1RecordController::class, 'index']);
  Route::get('/{module}/{id}', [ApiV1RecordController::class, 'show']);
  Route::post('/{module}', [ApiV1RecordController::class, 'store']);
  Route::put('/{module}/{id}', [ApiV1RecordController::class, 'update']);
  Route::patch('/{module}/{id}', [ApiV1RecordController::class, 'update']);
  Route::delete('/{module}/{id}', [ApiV1RecordController::class, 'destroy']);
});

// Unauthenticated at the Laravel auth layer
Route::post('/webhooks/email-inbound', [EmailInboundWebhookController::class, 'handle']);
