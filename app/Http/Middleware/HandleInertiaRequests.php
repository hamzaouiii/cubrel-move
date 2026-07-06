<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Support\Settings;
use App\Models\Module;
use App\Models\Settings\Settings as SettingsNav;
use App\Models\User;
use Illuminate\Support\Facades\Session;

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
        'user' => fn() => $request->user(),
        'impersonating' => fn () => Session::has('impersonator_id'),
        'impersonator'  => fn () => Session::has('impersonator_id')
            ? User::find(Session::get('impersonator_id'))?->only('id', 'name')
            : null,
          ],
      'flash' => [
        'success' => fn() => $request->session()->get('success'),
        'error'   => fn() => $request->session()->get('error'),
        'warning' => fn() => $request->session()->get('warning'),
      ],
      'settingsNav' => fn() => $request->is('settings*') ? [
        'categories' => SettingsNav::allActive(),
        'modules'    => Module::query()
          ->where('show_in_module_manager', 1)
          ->orderBy('sort_order')
          ->orderBy('name')
          ->get(['id', 'slug', 'name', 'label', 'icon', 'color'])
          ->values(),
      ] : null,
    ];
  }
}
