<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use App\Models\Module;
use App\Models\Label;
use App\Services\ModuleScaffolder;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    $this->app->singleton(ModuleScaffolder::class, function ($app) {
      return new ModuleScaffolder($app['files']);
    });
  }

  private function translate($key, $replace = [], $locale = null)
  {
    $customLabel = Label::where('key', $key)->first();
    logger('Label accessed: ' . $key);
    if ($customLabel && $customLabel->value) {
      return $customLabel->value;
    }

    return __($key, $replace, $locale);
  }
  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    Vite::prefetch(concurrency: 3);
    Inertia::share('locale', fn() => app()->getLocale());

    Inertia::share('modules', function () {
      return Module::active()
        ->where('show_in_sidebar', 1)
        ->orderBy('id')
        ->get()
        ->map(function (Module $module) {

          return [
            'slug'  => $module->slug,
            'name' => $module->name,
            'icon'  => $module->icon,
            'color' => $module->color,
            'path'  => $module->path,
            'label' => $module->label
          ];
        })
        ->values();
    });
    Inertia::share('translations', function () {
      $customLabels = Label::pluck('value', 'key')->toArray();
      return [
        'settings' => $this->translate('settings'),
        'modules' => $this->translate('modules'),
        'pagination' => $this->translate('pagination'),
        'sidebar' => $this->translate('sidebar'),
        'topbar' => $this->translate('topbar'),
        'layouts' => $this->translate('layouts'),
        'fields' => $this->translate('fields'),
        'globals' => $this->translate('globals'),
        'custom' => $customLabels
      ];
    });
  }
}
