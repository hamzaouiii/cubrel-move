<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use App\Services\ModuleScaffolder;
use App\Models\Module;
use App\Models\Layout;
use App\Services\Translations\TranslationService;
use App\Support\Settings;
use App\Models\Label;
use App\Observers\LabelObserver;
use App\Models\Settings\SettingValue;
use App\Observers\SettingValueObserver;
use App\Models\Modules\LineItem;
use App\Observers\LineItemTotalsObserver;
use App\Services\Users\OwnershipService;
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
    $this->app->singleton(OwnershipService::class);
  }


  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    Label::observe(LabelObserver::class);
    SettingValue::observe(SettingValueObserver::class);
    LineItem::observe(LineItemTotalsObserver::class);

    Vite::prefetch(concurrency: 3);

    Inertia::share([
      'locale'       => fn() => app()->getLocale(),
      'translations' => fn() => TranslationService::all(),
      'appSettings'  => Settings::all(...),
      'modules'      => fn() => Module::forSidebar(),
      'layouts'      => fn() => Layout::getAllLayouts(),
      
    ]);
  }
}
