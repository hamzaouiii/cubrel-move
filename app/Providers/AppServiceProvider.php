<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use App\Models\Module;

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
        Vite::prefetch(concurrency: 3);
        Inertia::share('locale', fn () => app()->getLocale());

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
        return [
            'settings' => __('settings'),
            'modules' => __('modules'),
            'pagination' => __('pagination'),
            'sidebar' => __('sidebar'),
            'topbar' => __('topbar'),
            'layouts' => __('layouts'),
            'custom' => __('custom')
        ];
    });
    }
}
