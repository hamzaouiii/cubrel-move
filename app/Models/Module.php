<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Layout;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Module extends BaseModule
{
  protected $fillable = [
    'slug',
    'name',
    'icon',
    'label',
    'color',
    'path',
    'sort_order',
    'is_active',
    'description',
    'can_view',
    'can_create',
    'can_edit',
    'can_delete',
    'model_class',
    'handler_class',
    'table_name',
    'show_in_sidebar',
    'is_custom'
  ];

  protected $casts = [
    'is_active'  => 'boolean',
    'can_view'   => 'boolean',
    'can_create' => 'boolean',
    'can_edit'   => 'boolean',
    'can_delete' => 'boolean',
  ];

  public function scopeActive($query)
  {
    return $query->where('is_active', true);
  }

  public function layouts()
  {
    return $this->hasMany(Layout::class);
  }

  public function listLayout()
  {
    $layout = $this->layouts()->where('type', 'list')->first();

    if ($layout) {
      return $this->hydrateLayoutSchema($layout, 'list');
    }

    $globalDefault = \App\Models\Layout::where('type', 'list')
      ->where('module_name', 'global')
      ->where('is_list_default', 1)
      ->first();

    if ($globalDefault) {
      return $this->hydrateLayoutSchema($globalDefault, 'list');
    }

    $globalFallback = \App\Models\Layout::where('type', 'list')
      ->where('module_name', 'global')
      ->first();

    if ($globalFallback) {
      return $this->hydrateLayoutSchema($globalFallback, 'list');
    }

    throw new \Exception("No list layout found for module {$this->name} and no global fallback available.");
  }

  public function recordLayout()
  {
    $recordLayout = $this->layouts()->where('type', 'record')->first();

    if (!empty($recordLayout)) {
      return $this->hydrateLayoutSchema($recordLayout, 'record');
    }

    return $this->hydrateLayoutSchema(Layout::getDefaultLayout('record'), 'record');
  }


  public function layoutFor(string $type)
  {
    return $this->layouts()
      ->where('type', $type)
      ->first();
  }

  public function fields(): array
  {
    // when custom fields are introduced they should be taken into account as well
    $table = $this->table_name;

    if (!$table || !Schema::hasTable($table)) {
      return [];
    }

    $columns = Schema::getColumnListing($table);

    $ignored = [
      'id',
      'deleted_at',
    ];

    return collect($columns)
      ->reject(fn($column) => in_array($column, $ignored, true))
      ->map(function ($column) use ($table) {
        $dbType = Schema::getColumnType($table, $column);

        return [
          'key'      => $column,
          'label'    => "modules.{$this->slug}.fields.{$column}",
          'type'     => $this->normalizeFieldType($dbType, $column),
          'db_type'  => $dbType,
        ];
      })
      ->values()
      ->all();
  }
  public function getFieldMetadata(string $fieldKey)
  {
    return collect($this->fields())
      ->firstWhere('key', $fieldKey);
  }
  protected function normalizeFieldType(string $dbType, string $column): string
  {
    return match ($dbType) {
      'string',       => 'string',
      'text'          => 'textarea',
      'integer', 'bigint',
      'smallint'              => 'int',
      'boolean'               => 'boolean',
      'float', 'decimal'      => 'number',
      'date'                  => 'date',
      'datetime', 'timestamp' => 'datetime',
      'time'                  => 'time',
      'json'                  => 'json',
      default                 => 'string',
    };
  }


  protected function hydrateRecordFieldItem($item, $fields)
  {
    if (!is_array($item)) return $item;

    $key = $item['key'] ?? $item['field'] ?? $item['name'] ?? null;
    if (!$key) return $item;

    $field = $fields->get($key);

    $item['key'] = $key;
    $item['label'] = $item['label'] ?? ($field['label'] ?? Str::headline($key));
    $item['type'] = $item['type'] ?? ($field['type'] ?? 'string');
    $item['format'] = $item['format'] ?? null;

    return $item;
  }


  protected function hydrateLayoutSchema(Layout $layout, string $type): Layout
  {
    $fields = collect($this->fields())->keyBy('key');
    $definition = $layout->definition ?? [];

    if ($type === 'record') {
      $definition = $this->hydrateRecordDefinition($definition, $fields);
    } else {
      $definition = $this->hydrateListDefinition($definition, $fields);
    }

    $layout->setAttribute('definition', $definition);
    return $layout;
  }

  protected function hydrateListDefinition($definition, $fields): array
  {
    $columns = is_array($definition['columns'] ?? null) ? $definition['columns'] : [];

    $definition['columns'] = collect($columns)->map(function ($col) use ($fields) {
      if (!is_array($col)) return $col;

      $key = $col['key'] ?? null;
      if (!$key) return $col;

      $fieldType = $col['type'] ?? ($fields->get($key)['type'] ?? 'string');

      $col['type'] = $fieldType;
      $col['readonly'] = $col['readonly'] ?? $this->defaultReadonlyFor($key, $fieldType);

      $col['label'] = $col['label'] ?? ($fields->get($key)['label'] ?? Str::headline($key));

      return $col;
    })->values()->all();

    return $definition;
  }

  protected function hydrateRecordDefinition($definition, $fields): array
  {
    $sections = is_array($definition['sections'] ?? null) ? $definition['sections'] : [];

    $definition['sections'] = collect($sections)->map(function ($section) use ($fields) {
      if (!is_array($section)) return $section;

      $layoutItems = is_array($section['layout'] ?? null) ? $section['layout'] : [];

      $section['layout'] = collect($layoutItems)->map(function ($item) use ($fields) {
        if (!is_array($item)) return $item;

        $key = $item['key'] ?? null;
        if (!$key) return $item;

        $fieldType = $item['type'] ?? ($fields->get($key)['type'] ?? 'string');

        $item['type'] = $fieldType;
        $item['readonly'] = $item['readonly'] ?? $this->defaultReadonlyFor($key, $fieldType);

        $item['label'] = $item['label'] ?? ($fields->get($key)['label'] ?? Str::headline($key));

        return $item;
      })->values()->all();

      return $section;
    })->values()->all();

    return $definition;
  }

  protected function defaultReadonlyFor(string $key, string $type): bool
  {
    return in_array($key, ['created_at', 'updated_at'], true);
  }
}
