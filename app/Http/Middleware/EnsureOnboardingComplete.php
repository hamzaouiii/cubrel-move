<?php

namespace App\Http\Middleware;

use App\Support\Settings;
use Closure;
use Illuminate\Http\Request;

class EnsureOnboardingComplete
{
  public function handle(Request $request, Closure $next)
  {
    if (! Settings::bool('onboarding_completed') && ! $request->routeIs('onboarding.*') && ! $request->routeIs('logout')) {
      return redirect()->route('onboarding.show');
    }

    return $next($request);
  }
}
