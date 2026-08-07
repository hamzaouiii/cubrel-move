<?php

namespace App\Http\Middleware;

use App\Support\ApiLocale;
use Closure;
use Illuminate\Http\Request;

/**
 * REST API equivalent of SetLocaleFromSettings 
 * app/Http/Middleware/SetLocaleFromAcceptLanguage.php
 */
class SetLocaleFromAcceptLanguage
{
    public function handle(Request $request, Closure $next)
    {
        app()->setLocale(ApiLocale::resolve($request));

        return $next($request);
    }
}
