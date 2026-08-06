<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnsureInstanceBootstrapped;
use App\Http\Middleware\EnsureOnboardingComplete;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetLocaleFromSettings;
use App\Providers\SearchServiceProvider;
use App\Support\ApiLocale;
use Illuminate\Foundation\Application;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Validation\ValidationException;
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
            EnsureInstanceBootstrapped::class,
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
        // Validation failures would redirect and flash errors into a session an API caller can't read.
        // force a JSON response body instead.
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], $e->status);
            }
        });

        // Doesn't extend HttpException, so the render() below won't catch it.
        // without this it redirects to /login instead of returning JSON.
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                // auth:sanctum runs before our locale middleware regardless of route order, so resolve locale here directly.
                app()->setLocale(ApiLocale::resolve($request));

                return response()->json(['message' => __('api.errors.unauthenticated')], 401);
            }
        });

        $exceptions->render(function (HttpException $e, $request) {
            $status = $e instanceof HttpException ? $e->getStatusCode() : 500;

            if ($status === 419) {
                // 303 forces a GET follow-up even after a PUT/PATCH/DELETE.
                if ($request->user()) {
                    return back(303)->with('warning', __('globals.session.token_refreshed'));
                }

                return redirect()->guest(route('login'), 303)
                    ->with('warning', __('globals.session.expired'));
            }

            if ($request->is('api/*')) {
                // Same locale caveat as above, throttle:api can also throw early.
                app()->setLocale(ApiLocale::resolve($request));

                // 404/429 always get a fixed message: findOrFail() leaks the
                // model class + id, and throttle's default text bypasses __().
                $message = match ($status) {
                    404 => __('api.errors.not_found'),
                    429 => __('api.errors.too_many_requests'),
                    default => $e->getMessage() ?: __('api.errors.generic', ['status' => $status]),
                };

                return response()->json(['message' => $message], $status);
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
