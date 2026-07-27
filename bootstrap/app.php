<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnsureOnboardingComplete;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetLocaleFromSettings;
use App\Providers\SearchServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withProviders([
        SearchServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            SetLocaleFromSettings::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'onboarded' => EnsureOnboardingComplete::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (HttpException $e, $request) {

            // If it's an HTTP exception, get the actual status code.
            // Otherwise, it's a code bug/fatal error, so default to 500.
            // This is for production only
            // in production catch all 500 errors in dev or local default to laravel's stack

            $status = $e instanceof HttpException ? $e->getStatusCode() : 500;

            if ($status === 419) {
                // 303 (not 302): the request that hit this may have been a PUT/PATCH/DELETE
                // and only 303 forces the browser to follow up with GET.
                // Auto-retry-with-token-refresh seam: this is where a silent replay of the
                // original request would go before falling back to the flash+redirect below.
                if ($request->user()) {
                    return back(303)->with('warning', __('globals.session.token_refreshed'));
                }

                return redirect()->guest(route('login'), 303)
                    ->with('warning', __('globals.session.expired'));
            }

            if (in_array($status, [404, 403, 405, 500, 503])) {
                return Inertia::render('Error', [
                    'error' => $status,
                ])
                    ->toResponse($request)
                    ->setStatusCode($status);
            }
        });
    })->create();
