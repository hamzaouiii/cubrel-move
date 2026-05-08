<?php

namespace App\Handlers\Modules;

use App\Contracts\ModuleHandler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\Relationships\RelationshipService;
use App\Models\Module;
use App\Support\Settings;
use Illuminate\Support\Facades\Log;

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
      throw new \Exception("Model class not defined in handler.");
    }

    $model = $this->model;

    try {
      $record = $model::findOrFail($recordId);
      $customFields = $record->custom_fields ?? [];
      $recordData = $record->toArray();
      $mergedData = array_merge($recordData, $customFields);

      $relateFields = collect($module->allFields())
        ->filter(fn($f) => ($f['type'] ?? null) === 'record' && !empty($f['related_module']))
        ->values();

      if ($relateFields->isNotEmpty()) {
        [$mergedData] = $this->resolveRelateLabels([$mergedData], $relateFields->all());
      }

      $related = RelationshipService::getAllRelatedRecords($module_slug, $recordId)->toArray();
      $mergedData['related'] = $related;

      return [
        'record' => $mergedData
      ];
    } catch (ModelNotFoundException $e) {
      throw $e;
    }
  }

  public function getListData(Module $module, array $params = []): array
  {
    $perPage    = $this->getPerPage($params);
    $query      = $this->query($params);
    $searchable = $this->getSearchableColumns($module);

    if (!empty($params['search']) && !empty($searchable)) {
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
    $last  = $paginator->lastPage();
    for ($p = 1; $p <= $last; $p++) {
      $pages[] = [
        'label'  => (string) $p,
        'page'   => $p,
        'url'    => $paginator->url($p),
        'active' => $p === $paginator->currentPage(),
      ];
    }

    $items = $paginator->items();

    $relateFields = collect($module->allFields())
      ->filter(fn($f) => ($f['type'] ?? null) === 'record' && !empty($f['related_module']))
      ->values();

    if ($relateFields->isNotEmpty()) {
      $items = $this->resolveRelateLabels($items, $relateFields->all());
    }

    return [
      'items' => $items,
      'meta'  => [
        'total'       => $paginator->total(),
        'perPage'     => $paginator->perPage(),
        'currentPage' => $paginator->currentPage(),
        'lastPage'    => $last,
        'from'        => $paginator->firstItem(),
        'to'          => $paginator->lastItem(),
        'links'       => [
          'prev' => $paginator->previousPageUrl(),
          'next' => $paginator->nextPageUrl(),
        ],
        'pages'       => $pages,
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
      $name   = $field['name'];
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
        $name   = $field['name'];
        $module = $field['related_module'];
        $id     = $data[$name] ?? null;
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
    Log::debug('[fetchLabels] Called', ['module' => $module, 'ids' => $ids]);

    if (empty($ids)) {
        Log::debug('[fetchLabels] Returning early — ids array is empty');
        return [];
    }

    // Cache model_class lookups to avoid repeated DB hits per module
    if (!isset(self::$moduleModelClassCache[$module])) {
        $moduleRecord = Module::query()
            ->select('model_class')
            ->where('slug', $module)
            ->first();


        self::$moduleModelClassCache[$module] = $moduleRecord?->model_class;
    } else {
        Log::debug('[fetchLabels] Module model_class served from cache', [
            'module'      => $module,
            'model_class' => self::$moduleModelClassCache[$module],
        ]);
    }

    $modelClass = self::$moduleModelClassCache[$module];

    if (!$modelClass) {
        Log::warning('[fetchLabels] Returning early — model_class is null/empty', ['module' => $module]);
        return [];
    }

    if (!class_exists($modelClass)) {
        Log::warning('[fetchLabels] Returning early — model_class does not exist', [
            'module'      => $module,
            'model_class' => $modelClass,
        ]);
        return [];
    }

    $cachedLabels = self::$moduleLabelCache[$module] ?? [];
    $missingIds   = array_values(array_diff($ids, array_keys($cachedLabels)));

    Log::debug('[fetchLabels] Cache state before fetch', [
        'module'          => $module,
        'cached_ids'      => array_keys($cachedLabels),
        'requested_ids'   => $ids,
        'missing_ids'     => $missingIds,
    ]);

    if (!empty($missingIds)) {
        $columns  = ['id'];
        $instance = new $modelClass;
        $fillable = $instance->getFillable();

        foreach (['name', 'first_name', 'last_name', 'title'] as $col) {
            if (in_array($col, $fillable)) {
                $columns[] = $col;
            }
        }

        Log::debug('[fetchLabels] Querying DB for missing records', [
            'module'      => $module,
            'model_class' => $modelClass,
            'missing_ids' => $missingIds,
            'columns'     => $columns,
        ]);

        $records = $modelClass::whereIn('id', $missingIds)->get($columns);

        Log::debug('[fetchLabels] DB query result', [
            'module'        => $module,
            'records_found' => $records->count(),
            'record_ids'    => $records->pluck('id')->toArray(),
        ]);

        if ($records->isEmpty()) {
            Log::warning('[fetchLabels] No records returned from DB — IDs may not exist or scope is filtering them out', [
                'module'      => $module,
                'missing_ids' => $missingIds,
                'model_class' => $modelClass,
            ]);
        }

        $records->each(function ($record) use ($module) {
            $label = $record->name
                ?? trim(($record->first_name ?? '') . ' ' . ($record->last_name ?? ''))
                ?: (string) $record->id;

            Log::debug('[fetchLabels] Resolved label for record', [
                'module'     => $module,
                'id'         => $record->id,
                'label'      => $label,
                'name'       => $record->name ?? 'N/A',
                'first_name' => $record->first_name ?? 'N/A',
                'last_name'  => $record->last_name ?? 'N/A',
            ]);

            self::$moduleLabelCache[$module][$record->id] = $label;
        });
    }

    $result = array_intersect_key(
        self::$moduleLabelCache[$module] ?? [],
        array_flip($ids)
    );

    Log::debug('[fetchLabels] Final result', [
        'module'          => $module,
        'requested_ids'   => $ids,
        'result_keys'     => array_keys($result),
        'result'          => $result,
    ]);

    if (empty($result)) {
        Log::warning('[fetchLabels] Returning empty result — possible causes: IDs not in DB, global scope filtering, UUID type mismatch', [
            'module'        => $module,
            'requested_ids' => $ids,
            'cache_keys'    => array_keys(self::$moduleLabelCache[$module] ?? []),
        ]);
    }

    return $result;
}

  public function getSearchableColumns(Module $module): array
  {
    $columns = $this->searchable ?? [];
    if (!isset($module)) {
      return $columns;
    }

    $dbFields = $module->allFields()
      ->where('searchable', true)
      ->pluck('name')
      ->toArray();
    return array_unique(array_merge($columns, $dbFields));
  }
}
