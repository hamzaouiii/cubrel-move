<?php

namespace App\Providers;

use App\Models\Module;
use App\Models\Transformation;
use App\Observers\TransformationAutomationObserver;
use Illuminate\Support\ServiceProvider;

/**
 * Registers TransformationAutomationObserver only against modules have an automation_enabled transformation
 * zero query cost on save everywhere else. 
 * Runs once per request boot rather than being wired into BaseModule itself
 */
class TransformationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerObservers();
    }

    protected function registerObservers(): void
    {
        try {
            $sourceSlugs = Transformation::where('automation_enabled', true)
                ->distinct()
                ->pluck('source_module');
        } catch (\Throwable $e) {
            return;
        }

        foreach ($sourceSlugs as $slug) {
            $modelClass = Module::withoutGlobalScope(\App\Scopes\AdminOnlyModuleScope::class)
                ->where('slug', $slug)
                ->value('model_class');

            if ($modelClass && class_exists($modelClass)) {
                $modelClass::observe(TransformationAutomationObserver::class);
            }
        }
    }
}
