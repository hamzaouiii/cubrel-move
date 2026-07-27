<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EnsureInstanceBootstrapped
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->routeIs('setup.*') || User::count() > 0) {
            return $next($request);
        }

        return Inertia::render('SetupRequired')
            ->toResponse($request)
            ->setStatusCode(503);
    }
}
