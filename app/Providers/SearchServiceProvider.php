<?php

namespace App\Providers;

use App\Search\GlobalSearchService;
use App\Search\Searchers\UniversalSearcher;
use Illuminate\Support\ServiceProvider;

class SearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GlobalSearchService::class, fn () => new GlobalSearchService([
            new UniversalSearcher,
            // in case other searchers are needed, should be added here
            ]
        ));
    }
}
