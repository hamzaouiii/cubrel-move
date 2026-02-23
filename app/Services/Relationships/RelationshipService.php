<?php

namespace App\Services\Relationships;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Models\RelationshipLink;
use App\Models\Module;
use RuntimeException;

class RelationshipService
{
  /**
   * returns teh relationship object
   */
  public static function get(string $name): Object
  {
    $relationship = DB::table('relationships')
      ->where('name', $name)
      ->first();

    if (!$relationship) {
      throw new \RuntimeException("Unknown relationship {$name}");
    }

    return $relationship;
  }

  /**
   * Enforces relationship rules
   */
  public static function enforceCardinality(object $relationship, string $left_id, string $right_id): void
  {
    if ($relationship->relationship_type === 'one-to-one') {
      // related record can only have on "parent record" so here the right_id can only exist once under this relationship.
      $exists = DB::table('relationship_links')
        ->where('relationship_id', $relationship->id)
        ->where(function ($q) use ($left_id, $right_id) {
          $q->where('left_id', $left_id)
            ->orWhere('right_id', $right_id);
        })
        ->exists();

      if ($exists) {
        throw new RuntimeException(
          "One-to-one relationship already exists"
        );
      }
    }
    if ($relationship->relationship_type === 'one-to-many') {
      // related record can only have on "parent record" so here the right_id can only exist once under this relationship.
      $exists = DB::table('relationship_links')
        ->where('relationship_id', $relationship->id)
        ->where('right_id', $right_id)
        ->exists();

      if ($exists) {
        throw new RuntimeException(
          "Record already linked in one-to-many relationship"
        );
      }
    }

    if ($relationship->relationship_type === "many-to-many") {
      $exists = DB::table('relationship_links')
        ->where('relationship_id', $relationship->id)
        ->where('right_id', $right_id)
        ->where('left_id', $left_id)
        ->exists();
      if ($exists) {
        throw new RuntimeException(
          "Records are already linked in many-to-many relationship"
        );
      }
    }
  }

  /**
   * returns the relationship object between two modules given the type
   */
  public static function getRelationshipBetween(string $module1, string $module2, string $type): Collection
  {
    $query = DB::table('relationships')
      ->where(function ($q) use ($module1, $module2) {
        // Group 1: module1 as left, module2 as right
        $q->where(function ($q1) use ($module1, $module2) {
          $q1->where('left_module', $module1)
            ->where('right_module', $module2);
        })
          // Group 2: module2 as left, module1 as right  
          ->orWhere(function ($q2) use ($module1, $module2) {
            $q2->where('left_module', $module2)
              ->where('right_module', $module1);
          });
      });

    if ($type) {
      $query->where('relationship_type', $type);
    }

    return $query->get();
  }

  /**
   * Relationship discovery, answers the question which relationship does this module have ?
   */
  public static function getRelationshipForModule(string $class): Collection
  {
    $relationships = DB::table('relationships')
      ->where('left_class', $class)
      ->orWhere('right_class', $class)
      ->get();

    if ($relationships->isEmpty()) {
      return collect();
    }

    $relationships = $relationships->map(function ($relationship) use ($class) {
      return self::getWithSide($relationship, $class);
    });

    $relatedSlugs = $relationships
      ->pluck('related_slug')
      ->unique()
      ->values();

    $modules = Module::with('relatedfields')
      ->whereIn('slug', $relatedSlugs)
      ->get()
      ->keyBy('slug');

    return $relationships->map(function ($relationship) use ($modules) {

      $module = $modules->get($relationship->related_slug);

      $relationship->related_fields = $module
        ? $module->relatedfields
        : collect();
      return $relationship;
    });
  }


  /**
   * unfinished loads relationship links
   */
  public static function loadRelationship(string $relationship_name): Collection
  {
    $relationship = self::get($relationship_name);
    return DB::table('Relationship_links')
      ->where('relationship_id', $relationship->id)->get();
  }

  /**
   * returns related ids to a record. side discovery happens on a model level
   * 
   */
  public static function getRelatedIds(object $relationship, string $side, string $id)
  {
    return DB::table('relationship_links')
      ->where('relationship_id', $relationship->id)
      ->where($side . '_id', $id)
      ->pluck($side === 'left' ? 'right_id' : 'left_id');
  }

  /**
   * Get relationship and determine which side the given model is on
   */
  public static function getWithSide(object $relationship, string $model_class, ?string $module_id = null): object
  {

    if ($relationship->left_class === $model_class) {
      $relationship->side = 'left';
      $relationship->current_side = 'left';
      $relationship->other_side = 'right';
      $relationship->current_id_field = 'left_id';
      $relationship->other_id_field = 'right_id';
      $relationship->related_class = $relationship->right_class;
      $relationship->related_slug = $relationship->right_module;
      $relationship->current_module_id = $module_id;
    } elseif ($relationship->right_class === $model_class) {
      $relationship->side = 'right';
      $relationship->current_side = 'right';
      $relationship->other_side = 'left';
      $relationship->current_id_field = 'right_id';
      $relationship->other_id_field = 'left_id';
      $relationship->related_class = $relationship->left_class;
      $relationship->related_slug = $relationship->left_module;

      $relationship->current_module_id = $module_id;
    } else {
      throw new RuntimeException("Model {$model_class} is not part of relationship {$relationship->name}");
    }

    return $relationship;
  }

  /**
   * Get relationship with resolved IDs for linking
   */
  public static function getWithResolvedIds(object $relationship, string $model_class, string $module_id, string $related_id): object
  {
    $relationship = self::getWithSide($relationship, $model_class, $module_id);

    if ($relationship->current_side === 'left') {
      $relationship->left_id = $module_id;
      $relationship->right_id = $related_id;
    } else {
      $relationship->left_id = $related_id;
      $relationship->right_id = $module_id;
    }

    return $relationship;
  }

  /**
   * Link two records in a relationship
   */
  public static function link(string $relationship_name, string $model_class, string $module_id, string $related_id): void
  {
    $relationship = self::get($relationship_name);
    $relationship = self::getWithResolvedIds($relationship, $model_class, $module_id, $related_id);

    DB::transaction(function () use ($relationship) {
      self::enforceCardinality(
        $relationship,
        $relationship->left_id,
        $relationship->right_id
      );

      RelationshipLink::updateorcreate([
        'relationship_id' => $relationship->id,
        'left_id' => $relationship->left_id,
        'right_id' => $relationship->right_id
      ]);
    });
  }

  /**
   * Unlink two records in a relationship
   */
  public static function unlink(object $relationship, string $model_class, string $module_id, string $related_id): void
  {
    $relationship = self::getWithResolvedIds($relationship, $model_class, $module_id, $related_id);

    DB::table('relationship_links')
      ->where('relationship_id', $relationship->id)
      ->where('left_id', $relationship->left_id)
      ->where('right_id', $relationship->right_id)
      ->delete();
  }

  /**
   * Get related records for a model
   */
  public static function getRelatedRecords(object $relationship, string $model_class, string $module_id): Collection
  {
    $relationship = self::getWithSide($relationship, $model_class, $module_id);

    $relatedIds = DB::table('relationship_links')
      ->where('relationship_id', $relationship->id)
      ->where($relationship->current_id_field, $module_id)
      ->pluck($relationship->other_id_field);

    if ($relatedIds->isEmpty()) {
      return collect();
    }

    $related_class = $relationship->related_class;

    return $related_class::query()
      ->whereIn('id', $relatedIds)
      ->get();
  }

  /**
   * Check if a relationship exists between two records
   */
  public static function exists(object $relationship, string $model_class, string $module_id, string $related_id): bool
  {
    $relationship = self::getWithResolvedIds($relationship, $model_class, $module_id, $related_id);

    return DB::table('relationship_links')
      ->where('relationship_id', $relationship->id)
      ->where('left_id', $relationship->left_id)
      ->where('right_id', $relationship->right_id)
      ->exists();
  }

  public static function getLinkingLayout(string $slug)
  {
    $module = Module::query()
      ->where('slug', $slug)
      ->firstOrFail();

    return $module->linkingPanelLayout();
  }

  /**
   * on second thought this function's name does not sound right, perhaps it required changing in the future
  
   */
  public static function getAllRelatedRecords(string $modelClass, string $recordId): Collection
  {
    $relationships = self::getRelationshipForModule($modelClass);

    if ($relationships->isEmpty()) {
      return collect();
    }

    $relationshipIds = $relationships->pluck('id');

    $allLinks = DB::table('relationship_links')
      ->whereIn('relationship_id', $relationshipIds)
      ->where(function ($query) use ($recordId) {
        $query->where('left_id', $recordId)
          ->orWhere('right_id', $recordId);
      })
      ->get()
      ->groupBy('relationship_id');

    $result = collect();

    foreach ($relationships as $relationship) {

      $rel = self::getWithSide($relationship, $modelClass, $recordId);
      $linksForRelationship = $allLinks[$relationship->id] ?? collect();

      $relatedIds = $linksForRelationship
        ->map(function ($link) use ($rel, $recordId) {
          return $link->{$rel->other_id_field};
        })
        ->unique()
        ->values();

      $records = collect();

      if ($relatedIds->isNotEmpty()) {
        $records = $rel->related_class::query()
          ->whereIn('id', $relatedIds)
          ->get();
      }

      $result[$relationship->name] = [
        'name'    => $relationship->name,
        'type'    => $relationship->relationship_type,
        'label'   =>  $relationship->label,
        'records' => $records,
        'related_slug' => $relationship->related_slug,
        'linking_layout' => self::getLinkingLayout($relationship->related_slug)
      ];
    }

    return $result;
  }

  /**
   * Get available records from related module for linking.
   */
  public static function getRecordsForLinking(
    object $relationship,
    string $modelClass,
    string $recordId,
    int $perPage = 25,
    ?string $search = null
  ) {

    $relationship = self::getWithSide($relationship, $modelClass, $recordId);

    // One-to-one: if already linked, nothing available
    if ($relationship->relationship_type === 'one-to-one') {
      $alreadyLinked = DB::table('relationship_links')
        ->where('relationship_id', $relationship->id)
        ->where($relationship->current_id_field, $recordId)
        ->exists();

      if ($alreadyLinked) {
        return collect();
      }
    }

    $relatedClass = $relationship->related_class;

    $query = $relatedClass::query();

    // Exclude records already linked to this record
    $linkedIds = DB::table('relationship_links')
      ->where('relationship_id', $relationship->id)
      ->where($relationship->current_id_field, $recordId)
      ->pluck($relationship->other_id_field);

    if ($linkedIds->isNotEmpty()) {
      $query->whereNotIn('id', $linkedIds);
    }

    // Enforce one-to-many
    if ($relationship->relationship_type === 'one-to-many') {
      $takenIds = DB::table('relationship_links')
        ->where('relationship_id', $relationship->id)
        ->pluck('right_id');

      $query->whereNotIn('id', $takenIds);
    }

    // Search
    if ($search) {
      $query->where('name', 'like', "%{$search}%");
    }

    return $query->orderBy('name')->paginate($perPage);
  }
}
