<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\LocalAutoLogin;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Illuminate\Support\Facades\App;


return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    api: __DIR__ . '/../routes/api.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(append: [
      \App\Http\Middleware\HandleInertiaRequests::class,
      \App\Http\Middleware\SetLocaleFromSettings::class,
      \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
      // LocalAutoLogin::class,
    ]);
    $middleware->alias([
      'admin' => \App\Http\Middleware\AdminMiddleware::class,
    ]);
  })
  ->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (Throwable $e, $request) {

      // If it's an HTTP exception, get the actual status code. 
      // Otherwise, it's a code bug/fatal error, so default to 500.
      // This is for production only
      // in production catch all 500 errors in dev or local default to laravel's stack

      $status = $e instanceof HttpException ? $e->getStatusCode() : 500;

      if (in_array($status, [404, 403, 405, 500, 503])) {
        return Inertia::render("Error", [
          'error' => $status,
        ])
          ->toResponse($request)
          ->setStatusCode($status);
      }
    });
  })->create();
