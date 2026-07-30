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

// Unauthenticated, called by Mailtrap's servers, not a browser session.
// Verified via HMAC signature (EmailInboundWebhookController::hasValidSignature)
// instead of Laravel auth, and exempted from CSRF in bootstrap/app.php.
Route::post('/webhooks/email-inbound', [EmailInboundWebhookController::class, 'handle']);

// TEMPORARY — payload discovery only, remove before committing. Logs
// whatever CloudMailin (or any provider) actually sends so the real
// endpoint above can be adapted to match, instead of guessing. Accepts
// any method — some providers GET-verify a target URL before using it,
// which a POST-only route would reject with a 405.
Route::match(['get', 'post'], '/webhooks/debug', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Log::info('Webhook debug payload', [
        'method' => $request->method(),
        'headers' => $request->headers->all(),
        'body' => $request->all(),
        'raw' => $request->getContent(),
    ]);

    return response('', 200);
});
