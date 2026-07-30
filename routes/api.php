<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IconController;
use App\Http\Controllers\DropdownListController;
use App\Http\Controllers\RelationshipLinkController;
use App\Http\Controllers\EmailInboundWebhookController;


Route::middleware(['web', 'auth'])->group(function () {
  Route::get('/icons', [IconController::class, 'index']);
  Route::get('/dropdown-lists', [DropdownListController::class, 'api']);
  Route::get('/related-module-records/{id}', [RelationshipLinkController::class, 'getRecordsForLinking']);
});

// Unauthenticated at the Laravel auth layer — called by the self-hosted
// Postfix relay on this same server (deploy/cubrel-inbound-relay.sh), not
// a browser session. Sits outside the 'web' group above, so it gets
// api.php's default stateless middleware (no session, no CSRF) rather
// than needing an explicit exemption. Verified via a shared secret
// header instead (EmailInboundWebhookController::hasValidSecret).
Route::post('/webhooks/email-inbound', [EmailInboundWebhookController::class, 'handle']);
