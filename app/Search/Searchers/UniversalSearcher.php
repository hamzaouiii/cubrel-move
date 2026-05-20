<?php

namespace App\Search\Searchers;

use App\Models\Module;
use Illuminate\Support\Collection;

class UniversalSearcher
{
    public function search(string $query): Collection
    {
        return Module::query()
            ->where('is_active', true)
            ->get()
            ->flatMap(function (Module $module) use ($query) {
                $modelClass = $module->model_class;

                return $modelClass::search($query)
                    ->get()
                    ->map(fn ($r) => array_merge(
                        $r->toSearchResult(),
                        ['module_label' => $module->label]
                    ));
            })
            ->groupBy('module');
    }
}
