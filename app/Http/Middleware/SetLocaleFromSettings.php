<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Support\Settings;

class SetLocaleFromSettings
{
    public function handle($request, Closure $next)
    {
        $locale = Auth::user()?->preferences['app_locale'] ?? null ?: Settings::locale();

        // if more locales are needed should be added here O.o
        if (! in_array($locale, ['de', 'en'])) {
            $locale = config('app.locale'); 
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
