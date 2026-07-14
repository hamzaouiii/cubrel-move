<?php

namespace App\Http\Controllers;

use App\Contracts\ModuleHandler;
use App\Exceptions\ModuleHandlerNotFoundException;
use App\Models\ListFilter;
use App\Models\Module;
use App\Support\Filters\FilterQueryBuilder;
use App\Support\Settings;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ListController extends Controller
{
    public function __invoke(string $module)
    {
        $moduleModel = Module::query()
            ->where('slug', $module)
            ->where('is_active', true)
            ->firstOrFail();

        $handlerClass = $moduleModel->handler_class
          ?? 'App\\Handlers\\Modules\\'.Str::studly($moduleModel->slug).'ModuleHandler';

        if (empty($handlerClass)) {
            throw new ModuleHandlerNotFoundException(
                "Handler class [{$handlerClass}] not found for module [{$module}]. Please check if the file exists or re-deploy."
            );
        }

        $props = [];

        if (class_exists($handlerClass)) {
            $handler = app($handlerClass);

            if ($handler instanceof ModuleHandler) {
                $params = request()->all();
                $params['perPage'] = $params['perPage'] ?? Settings::get('list_view_limit');
                $params['sort'] = request()->input('sort');
                $params['direction'] = request()->input('direction', 'asc');
                $props = $handler->getListData($moduleModel, $params);
            }
        }

        $listLayout = $moduleModel->listLayout();
        $recorddropdownLists = $moduleModel->dropdownLists;
        $fields = $moduleModel->allFields();

        $availableFilters = ListFilter::query()
            ->forModule($moduleModel->slug)
            ->visibleTo(request()->user())
            ->orderBy('last_used','desc')
            ->get(['id', 'slug', 'name', 'label', 'last_used', 'is_shared', 'is_system', 'is_global', 'user_id', 'conditions', 'match_type'])
            ->filter(fn ($f) => FilterQueryBuilder::isApplicable($moduleModel, $f->conditions))
            ->values()
            ->groupBy(fn ($f) => $f->is_shared ? 'shared' : 'private');

        $activeFilterKey = request()->input('filter');
        $activeFilter = $activeFilterKey
                      ? $availableFilters->flatten()->first(fn ($f) => $f->slug === $activeFilterKey || $f->id === $activeFilterKey)
                      : null;
        if ($activeFilter) {
            $activeFilter->forceFill(['last_used' => now()])->saveQuietly();
        }

        return Inertia::render('Modules/List', array_merge([
            'module' => $moduleModel,
            'listLayout' => $listLayout,
            'fields' => $fields,
            'filterableFields' => array_values(FilterQueryBuilder::allowedFieldsMap($moduleModel)),
            'filterOperators' => config('filter_operators'),
            'filters' => request()->only(['search', 'perPage', 'sort', 'direction', 'filter']),
            'availableFilters' => $availableFilters,
            'activeFilter' => $activeFilter,
            'dropdownLists' => $recorddropdownLists,
            // import config
            'importMaxFileSizeKb' => config('import.max_file_size_kb'),
            'importAcceptedExtensions' => config('import.accepted_extensions'),
            'importExcludedFieldTypes' => config('import.excluded_fields'),

        ], $props));
    }
}
