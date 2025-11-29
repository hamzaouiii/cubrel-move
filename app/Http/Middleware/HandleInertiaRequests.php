<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Support\Settings;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
      return [
        ...parent::share($request),
        'auth' => [
            'user' => $request->user(),
        ],
        'locale' => app()->getLocale(),
        'appSettings' => [
          'useModuleColors' => Settings::bool('use_individual_module_colors', true),
          'locale' => Settings::get('app_locale', config('app.locale')),
        ],
      ];
    }
}
