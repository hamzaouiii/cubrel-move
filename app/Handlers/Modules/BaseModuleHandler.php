<?php

namespace App\Handlers\Modules;

use App\Contracts\ModuleHandler;
use App\Models\ListFilter;
use App\Models\Module;
use App\Scopes\AdminOnlyModuleScope;
use App\Services\Relationships\RelationshipService;
use App\Support\Filters\FilterQueryBuilder;
use App\Support\Settings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class BaseModuleHandler implements ModuleHandler
{
    abstract protected function query(array $params = []): Builder;

    protected string $model;

    protected array $searchable = ['name', 'description'];

    protected function getPerPage(array $params): int
    {
        return $params['perPage'] ?? Settings::get('list_view_limit');
    }

    public function getRecordData(string $module_slug, string $recordId, Module $module, array $params = []): array
    {
        if (! isset($this->model)) {
            throw new \Exception('Model class not defined in handler.');
        }

        $model = $this->model;

        try {
            $record = $model::findOrFail($recordId);
            $customFields = $record->custom_fields ?? [];
            $recordData = $record->toArray();
            $mergedData = array_merge($recordData, $customFields);

            $relateFields = collect($module->allFields())
                ->filter(fn ($f) => ($f['type'] ?? null) === 'record' && ! empty($f['related_module']))
                ->values();

            if ($relateFields->isNotEmpty()) {
                [$mergedData] = $this->resolveRelateLabels([$mergedData], $relateFields->all());
            }

            $related = RelationshipService::getAllRelatedRecords($module_slug, $recordId)->toArray();
            $mergedData['related'] = $related;

            return [
                'record' => $mergedData,
            ];
        } catch (ModelNotFoundException $e) {
            throw $e;
        }
    }

    public function getListData(Module $module, array $params = []): array
    {
        $perPage = $this->getPerPage($params);
        $query = $this->query($params);

        if (! empty($params['filter'])) {
            $filter = ListFilter::findVisibleByKey($module->slug, $params['filter'], auth()->user());

            if ($filter) {
                FilterQueryBuilder::apply($query, $module, $filter->conditions, $filter->match_type);
            }
        }

        $searchable = $this->getSearchableColumns($module);

        if (! empty($params['search']) && ! empty($searchable)) {
            $search = trim($params['search']);
            $query->where(function ($q) use ($search, $searchable) {
                foreach ($searchable as $column) {
                    $q->orWhere($column, 'LIKE', "%{$search}%");
                }
            });
        }

        if ($sort = $params['sort'] ?? null) {
            $direction = in_array($params['direction'] ?? 'asc', ['asc', 'desc'])
                ? $params['direction']
                : 'asc';

            if (in_array($sort, $query->getModel()->getFillable())) {
                $query->orderBy($sort, $direction);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $paginator = $query->paginate($perPage);

        $pages = [];
        $last = $paginator->lastPage();
        for ($p = 1; $p <= $last; $p++) {
            $pages[] = [
                'label' => (string) $p,
                'page' => $p,
                'url' => $paginator->url($p),
                'active' => $p === $paginator->currentPage(),
            ];
        }

        $items = $paginator->items();

        $relateFields = collect($module->allFields())
            ->filter(fn ($f) => ($f['type'] ?? null) === 'record' && ! empty($f['related_module']))
            ->values();

        if ($relateFields->isNotEmpty()) {
            $items = $this->resolveRelateLabels($items, $relateFields->all());
        }

        return [
            'items' => $items,
            'meta' => [
                'total' => $paginator->total(),
                'perPage' => $paginator->perPage(),
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $last,
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'links' => [
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
                'pages' => $pages,
            ],
        ];
    }

    /**
     * Inject __label keys for all relate fields across all items.
     * Groups lookups by module to avoid per-row queries.
     */
    protected function resolveRelateLabels(array $items, array $relateFields): array
    {
        // Collect all IDs per related module in one pass
        $idsByModule = [];
        foreach ($relateFields as $field) {
            $name = $field['name'];
            $module = $field['related_module'];
            foreach ($items as $item) {
                $id = is_array($item) ? ($item[$name] ?? null) : ($item->{$name} ?? null);
                if ($id) {
                    $idsByModule[$module][$id] = true;
                }
            }
        }

        // Fetch labels per module in a single query each
        $labelsByModule = [];
        foreach ($idsByModule as $module => $ids) {
            $labelsByModule[$module] = $this->fetchLabels($module, array_keys($ids));
        }

        // Inject __label keys into items
        return array_map(function ($item) use ($relateFields, $labelsByModule) {
            $data = is_array($item) ? $item : $item->toArray();
            foreach ($relateFields as $field) {
                $name = $field['name'];
                $module = $field['related_module'];
                $id = $data[$name] ?? null;
                if ($id) {
                    $data["{$name}__label"] = $labelsByModule[$module][$id] ?? null;
                }
            }

            return $data;
        }, $items);
    }

    /**
     * Fetch id → display label map for a given module's records.
     * Override in a subclass handler if a module uses non-standard name columns.
     */
    protected static array $moduleLabelCache = [];

    protected static array $moduleModelClassCache = [];

    protected function fetchLabels(string $module, array $ids): array
    {

        if (empty($ids)) {
            return [];
        }

        // Cache model_class lookups to avoid repeated DB hits per module
        if (! isset(self::$moduleModelClassCache[$module])) {
            $moduleRecord = Module::withoutGlobalScope(AdminOnlyModuleScope::class)
                ->select('model_class')
                ->where('slug', $module)
                ->first();

            self::$moduleModelClassCache[$module] = $moduleRecord?->model_class;
        }
        $modelClass = self::$moduleModelClassCache[$module];

        if (! $modelClass) {
            return [];
        }

        if (! class_exists($modelClass)) {
            return [];
        }

        $cachedLabels = self::$moduleLabelCache[$module] ?? [];
        $missingIds = array_values(array_diff($ids, array_keys($cachedLabels)));

        if (! empty($missingIds)) {
            $columns = ['id'];
            $instance = new $modelClass;
            $fillable = $instance->getFillable();

            foreach (['name', 'first_name', 'last_name', 'title'] as $col) {
                if (in_array($col, $fillable)) {
                    $columns[] = $col;
                }
            }

            $records = $modelClass::withoutGlobalScope(AdminOnlyModuleScope::class)
                ->whereIn('id', $missingIds)
                ->get($columns);

            if ($records->isEmpty()) {

            }

            $records->each(function ($record) use ($module) {
                $label = $record->name
                    ?? trim(($record->first_name ?? '').' '.($record->last_name ?? ''))
                    ?: (string) $record->id;

                self::$moduleLabelCache[$module][$record->id] = $label;
            });
        }

        $result = array_intersect_key(
            self::$moduleLabelCache[$module] ?? [],
            array_flip($ids)
        );

        if (empty($result)) {

        }

        return $result;
    }

    public function getSearchableColumns(Module $module): array
    {
        $columns = $this->searchable ?? [];
        if (! isset($module)) {
            return $columns;
        }

        $dbFields = $module->allFields()
            ->where('searchable', true)
            ->pluck('name')
            ->toArray();

        return array_unique(array_merge($columns, $dbFields));
    }
}
