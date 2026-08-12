<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
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
use App\Models\Modules\Meeting;
use App\Observers\MeetingOrganizerObserver;
use App\Services\Users\OwnershipService;
use App\Services\Api\RecordApiService;
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
    
    $this->app->singleton(RecordApiService::class);
  }


  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    Label::observe(LabelObserver::class);
    SettingValue::observe(SettingValueObserver::class);
    LineItem::observe(LineItemTotalsObserver::class);
    Meeting::observe(MeetingOrganizerObserver::class);

    Vite::prefetch(concurrency: 3);

    RateLimiter::for('api', function (Request $request) {
      return Limit::perMinute(60)->by($request->user()?->currentAccessToken()?->id ?: $request->ip());
    });

    // overriding system wide settings by the preferences array passed along with auth::user
    Inertia::share([
      'locale'       => fn() => app()->getLocale(),
      'translations' => fn() => TranslationService::all(),
      'appSettings'  => function () {
        $overrides = array_filter(Auth::user()?->preferences ?? [], fn ($v) => $v !== null);

        return array_merge(Settings::all(), $overrides, [
          'dark_mode_enabled' => config('theme.dark_mode_enabled'),
        ]);
      },
      'modules'      => fn() => Module::forSidebar(),
      'layouts'      => fn() => Layout::getAllLayouts(),
      'meetingAttendeeOptions' => fn() => config('meeting_attendees'),

    ]);
  }
}
