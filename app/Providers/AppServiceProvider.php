<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use App\Models\Module;
use App\Services\ModuleScaffolder;
use App\Services\Translations\TranslationService;
use App\Services\Settings\SettingService;
use App\Models\Label;
use App\Observers\LabelObserver;
use App\Models\Settings\SettingValue;
use App\Observers\SettingValueObserver;

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


  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    Label::observe(LabelObserver::class);
    SettingValue::observe(SettingValueObserver::class);

    Vite::prefetch(concurrency: 3);

    Inertia::share([
      'locale'       => fn() => app()->getLocale(),
      'translations' => fn() => TranslationService::all(),
      'appSettings'  => fn() => SettingService::all(),
      'modules'      => fn() => Module::forSidebar(),
    ]);
  }
}
