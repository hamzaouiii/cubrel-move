<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LocalAutoLogin
{
    public function handle($request, Closure $next)
    {
        if (App::environment('local') && !Auth::check()) {
            $user = User::where('username', 'admin')->first();
            Auth::login($user);
        }

        return $next($request);
    }
}
