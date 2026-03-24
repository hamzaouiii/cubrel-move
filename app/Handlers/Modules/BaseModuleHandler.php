<?php

namespace App\Handlers\Modules;

use App\Contracts\ModuleHandler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\Relationships\RelationshipService;
use App\Models\Module;
use App\Support\Settings;

abstract class BaseModuleHandler implements ModuleHandler
{
  abstract protected function query(array $params = []): Builder;

  protected string $model;

  // updated to have dynamic searchable fields
  //TODO: needs tests
  protected array $searchable = ['name', 'description'];

  protected function getPerPage(array $params): int
  {
    return $params['perPage'] ?? Settings::get('list_view_limit');
  }

  protected function transformItems(array $items, array $params): array
  {
    return $items;
  }

  public function getListData(Module $module, array $params = []): array
  {
    $perPage = $this->getPerPage($params);
    $query   = $this->query($params);

    $searchable = $this->getSearchableColumns($module);
    if (!empty($params['search']) && !empty($searchable)) {
      $search = trim($params['search']);

      $query->where(function ($q) use ($search, $searchable) {
        foreach ($searchable as $column) {
          $q->orWhere($column, 'LIKE', "%{$search}%");
        }
      });
    }

    $paginator = $query
      ->orderBy('created_at', 'desc')
      ->paginate($perPage);

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

    $items = $this->transformItems($paginator->items(), $params);

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


  public function getRecordData(string $module_slug, string $recordId, array $params = []): array
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
      $related = RelationshipService::getAllRelatedRecords($module_slug, $recordId)->toArray();
      $mergedData['related'] = $related;
      return [
        'record' => $mergedData
      ];
    } catch (ModelNotFoundException $e) {
      throw $e;
    }
  }

  protected function getSearchableColumns(Module $module): array
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
