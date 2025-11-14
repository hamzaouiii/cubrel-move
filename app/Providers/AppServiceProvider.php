<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
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
        //
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
                ->orderBy('sort_order')
                ->get()
                ->map(function (Module $module) {
                    $translatedLabel = __("modules.{$module->slug}");

                    return [
                        'slug'  => $module->slug,
                        'label' => $translatedLabel ?: $module->label,
                        'icon'  => $module->icon,
                        'color' => $module->color,
                        'path'  => $module->path,
                    ];
                })
                ->values();
        });
    }
}
