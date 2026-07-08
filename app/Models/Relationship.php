<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Layout;

class Relationship extends Model
{
  protected $table = 'relationships';
  use HasUuids;
  protected $guarded = [];
  protected $fillable = [
    'type',
    'right_module',
    'name',
    'label',
    'left_module',
    'is_system',
    'join_table',
  ];
  protected $excludedFromForms = [
    'is_system',
    'join_table',
    'left_module',


  ];
  protected static function booted()
  {
    static::saving(function ($relationship) {
      if ($relationship->left_module === $relationship->right_module) {
        throw new \InvalidArgumentException('Self-referencing relationships are not allowed.');
      }
    });
  }
  public function links(): HasMany
  {
    return $this->hasMany(RelationshipLink::class);
  }

  public function leftModule()
  {
    return $this->belongsTo(Module::class, 'left_module', 'slug');
  }

  public function rightModule()
  {
    return $this->belongsTo(Module::class, 'right_module', 'slug');
  }
  public function getEmptyMetadata()
  {
    return array_diff($this->fillable, $this->excludedFromForms);
  }

  public function cleanupRelationshipPanels(): void
  {
    $relationshipName = $this->name;

    $moduleIds = Module::query()
      ->whereIn('slug', [$this->left_module, $this->right_module])
      ->pluck('id');

    $layouts = Layout::where('type', 'related')
      ->whereIn('module_id', $moduleIds)
      ->get();
    foreach ($layouts as $layout) {
      $value = $layout->definition;
      $changed = false;
      foreach ($value['columns'] ?? [] as $cIndex => $column) {

        if (!isset($column['layout'])) {
          continue;
        }

        $originalCount = count($column['layout']);

        $value['columns'][$cIndex]['layout'] = array_values(
          array_filter($column['layout'], function ($panel) use ($relationshipName) {
            return ($panel['name'] ?? null) !== $relationshipName;
          })
        );

        if ($originalCount !== count($value['columns'][$cIndex]['layout'])) {
          $changed = true;
        }
      }

      if ($changed) {
        $layout->definition = $value;
        $layout->save();
      }
    }
  }
}
