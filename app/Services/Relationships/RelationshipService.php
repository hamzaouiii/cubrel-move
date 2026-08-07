<?php

namespace App\Services\Relationships;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Models\Relationship;
use App\Models\RelationshipLink;
use App\Models\Module;
use App\Services\Audit\AuditService;
use App\Services\Notifications\NotificationService;
use App\Services\Transformations\TransformationEngine;
use RuntimeException;
use App\Support\Settings;

class RelationshipService
{
  /**
   * Cache to prevent redundant DB lookups for module models
   */
  protected static array $moduleCache = [];

  // Cache for Relationship rows by name 
  protected static array $relationshipCache = [];

  // Cache for individual records by "class:id"
  protected static array $recordCache = [];

  // These caches key on slug/name/id, stable across a test run - tests must reset this between cases.
  public static function clearCache(): void
  {
    self::$moduleCache = [];
    self::$relationshipCache = [];
    self::$recordCache = [];
  }

  /**
   * Generates a many-to-many relationship for every pairing
   * this module's is_activity/has_activity flags call for. Safe to call every time a module is saved.
   */
  public static function syncActivityRelationships(Module $module): void
  {
    if ($module->has_activity) {
      foreach (Module::where('is_activity', true)->get() as $activity) {
        self::createActivityRelationship($module->slug, $activity->slug);
      }
    }

    if ($module->is_activity) {
      foreach (Module::where('has_activity', true)->get() as $parent) {
        self::createActivityRelationship($parent->slug, $module->slug);
      }
    }
  }

  private static function createActivityRelationship(string $parentSlug, string $activitySlug): void
  {
    if ($parentSlug === $activitySlug) {
      return;
    }

    Relationship::firstOrCreate(
      ['name' => "{$parentSlug}_{$activitySlug}"],
      [
        'label' => "modules.{$activitySlug}.label",
        'left_module' => $parentSlug,
        'right_module' => $activitySlug,
        'type' => 'many-to-many',
        'is_system' => true,
      ]
    );
  }

  /**
   * Resolves the full Module model from a slug and caches it in memory.
   */
  protected static function getModuleBySlug(string $slug): Module
  {
    if (isset(self::$moduleCache[$slug])) {
      return self::$moduleCache[$slug];
    }

    $module = Module::where('slug', $slug)->first();

    if (!$module) {
      throw new RuntimeException("Could not resolve module for slug: {$slug}");
    }

    self::$moduleCache[$slug] = $module;

    return $module;
  }

  /**
   * Resolves the Eloquent Model Class string from a given module slug
   */
  protected static function resolveClassFromSlug(string $slug): string
  {
    $module = self::getModuleBySlug($slug);

    if (empty($module->model_class)) {
      throw new RuntimeException("Module {$slug} does not have a model_class defined.");
    }

    return $module->model_class;
  }

  /**
   * Returns the relationship object
   */
  public static function get(string $name): Relationship
  {
    if (isset(self::$relationshipCache[$name])) {
      return clone self::$relationshipCache[$name];
    }

    $relationship = Relationship::where('name', $name)->first();

    if (!$relationship) {
      throw new \RuntimeException("Unknown relationship {$name}");
    }

    self::$relationshipCache[$name] = $relationship;

    return clone $relationship;
  }

  /**
   * Enforces relationship rules
   */
  public static function enforceCardinality(Relationship $relationship, string $left_id, string $right_id): void
  {
    if ($relationship->type === 'one-to-one') {

      $exists = DB::table('relationship_links')
        ->where('relationship_id', $relationship->id)
        ->where(function ($q) use ($left_id, $right_id) {
          $q->where('left_id', $left_id)
            ->orWhere('right_id', $right_id);
        })
        ->exists();

      if ($exists) {
        throw new RuntimeException("One-to-one relationship already exists");
      }
    }

    if ($relationship->type === 'one-to-many') {
      $exists = DB::table('relationship_links')
        ->where('relationship_id', $relationship->id)
        ->where('right_id', $right_id)
        ->exists();

      if ($exists) {
        throw new RuntimeException("Record already linked in one-to-many relationship");
      }
    }

    if ($relationship->type === "many-to-many") {
      $exists = DB::table('relationship_links')
        ->where('relationship_id', $relationship->id)
        ->where('right_id', $right_id)
        ->where('left_id', $left_id)
        ->exists();

      if ($exists) {
        throw new RuntimeException("Records are already linked in many-to-many relationship");
      }
    }
  }

  /**
   * Link two records in a relationship
   */
  public static function link(string $relationship_name, string $module_slug, string $module_id, string $related_id): void
  {
    $relationship = self::get($relationship_name);
    $relationship = self::getWithResolvedIds($relationship, $module_slug, $module_id, $related_id);

    DB::transaction(function () use ($relationship) {

      $leftId  = $relationship->left_id;
      $rightId = $relationship->right_id;

      switch ($relationship->type) {

        case 'one-to-one':
          // Remove any existing link involving either side
          DB::table('relationship_links')
            ->where('relationship_id', $relationship->id)
            ->where(function ($q) use ($leftId, $rightId) {
              $q->where('left_id', $leftId)
                ->orWhere('right_id', $rightId);
            })
            ->delete();
          break;

        case 'one-to-many':
          // Remove existing parent of this child
          DB::table('relationship_links')
            ->where('relationship_id', $relationship->id)
            ->where('right_id', $rightId)
            ->delete();
          break;

        case 'many-to-many':
          // Still prevent duplicates
          $exists = DB::table('relationship_links')
            ->where('relationship_id', $relationship->id)
            ->where('left_id', $leftId)
            ->where('right_id', $rightId)
            ->exists();

          if ($exists) {
            throw new RuntimeException("Records are already linked in many-to-many relationship");
          }
          break;
      }

      // Now insert safely
      RelationshipLink::create([
        'relationship_id' => $relationship->id,
        'left_id'         => $leftId,
        'right_id'        => $rightId,
      ]);
    });

    self::logLinkChange('linked', $relationship, $module_slug, $module_id, $related_id);
    self::notifyActivityLinked($module_slug, $module_id, $relationship->related_slug, $related_id);
  }

  /**
   * When an is_activity record (Task/Call/Meeting/Note) gets linked to a
   * has_activity parent record, notifies the parent's owner ( part of the
   * "all activity on an owned record" notification type ) do nothing unless one side is has_activity and the other is_activity.
   */
  private static function notifyActivityLinked(string $moduleSlug, string $moduleId, string $relatedModuleSlug, string $relatedId): void
  {
    if (TransformationEngine::notificationsSuppressed()) {
      return;
    }

    $module = self::getModuleBySlug($moduleSlug);
    $related = self::getModuleBySlug($relatedModuleSlug);

    if ($related->has_activity && $module->is_activity) {
      self::notifyParentOwner($related, $relatedId);
    } elseif ($module->has_activity && $related->is_activity) {
      self::notifyParentOwner($module, $moduleId);
    }
  }

  private static function notifyParentOwner(Module $parentModule, string $parentId): void
  {
    $parentClass = self::resolveClassFromSlug($parentModule->slug);
    $parent = self::findCached($parentClass, $parentId);

    if ($parent) {
      NotificationService::notifyRecordActivity($parent, $parentModule, 'linked');
    }
  }

  /**
   * Unlink two records in a relationship
   */
  public static function unlink(string $relationship_name, string $module_slug, string $module_id, string $related_id): void
  {
    $relationship = self::get($relationship_name);
    $relationship = self::getWithResolvedIds($relationship, $module_slug, $module_id, $related_id);

    DB::table('relationship_links')
      ->where('relationship_id', $relationship->id)
      ->where('left_id', $relationship->left_id)
      ->where('right_id', $relationship->right_id)
      ->delete();

    self::logLinkChange('unlinked', $relationship, $module_slug, $module_id, $related_id);
  }

 /**
  * for Audit Trail
  */
  private static function logLinkChange(string $action, Relationship $relationship, string $moduleSlug, string $moduleId, string $relatedId): void
  {
    $relatedModuleSlug = $relationship->related_slug;

    $thisLabel = self::resolveRecordLabel($moduleSlug, $moduleId);
    $relatedLabel = self::resolveRecordLabel($relatedModuleSlug, $relatedId);

    AuditService::log($action, $moduleSlug, $moduleId, [
      'relationship' => $relationship->name,
      'relationship_label' => $relationship->label,
      'related_module' => $relatedModuleSlug,
      'related_id' => $relatedId,
      'related_label' => $relatedLabel,
    ]);

    AuditService::log($action, $relatedModuleSlug, $relatedId, [
      'relationship' => $relationship->name,
      'relationship_label' => $relationship->label,
      'related_module' => $moduleSlug,
      'related_id' => $moduleId,
      'related_label' => $thisLabel,
    ]);
  }

  private static function resolveRecordLabel(string $moduleSlug, string $id): ?string
  {
    $modelClass = self::resolveClassFromSlug($moduleSlug);
    $record = self::findCached($modelClass, $id);

    return $record ? ($record->name ?? $record->number ?? $id) : null;
  }

  /**
   * find() a record by class+id, cached.
   */
  private static function findCached(string $modelClass, string $id)
  {
    $key = "{$modelClass}:{$id}";

    if (array_key_exists($key, self::$recordCache)) {
      return self::$recordCache[$key];
    }

    return self::$recordCache[$key] = $modelClass::find($id);
  }

  /**
   * returns the relationship object between two modules given the type
   */
  public static function getRelationshipBetween(string $module1_slug, string $module2_slug, ?string $type = null): Collection
  {
    $query = Relationship::query()
      ->where(function ($q) use ($module1_slug, $module2_slug) {
        // Group 1: module1 as left, module2 as right
        $q->where(function ($q1) use ($module1_slug, $module2_slug) {
          $q1->where('left_module', $module1_slug)
            ->where('right_module', $module2_slug);
        })
          // Group 2: module2 as left, module1 as right  
          ->orWhere(function ($q2) use ($module1_slug, $module2_slug) {
            $q2->where('left_module', $module2_slug)
              ->where('right_module', $module1_slug);
          });
      });

    if ($type) {
      $query->where('type', $type);
    }

    return $query->get();
  }

  /**
   * Relationship discovery, answers the question what relationships does this module have ?
   */
  public static function getRelationshipForModule(string $module_slug): Collection
  {
    $relationships = Relationship::query()
      ->where('left_module', $module_slug)
      ->orWhere('right_module', $module_slug)
      ->get();

    if ($relationships->isEmpty()) {
      return collect();
    }

    // Get usage counts in one query
    $links_used = DB::table('relationship_links')
      ->select('relationship_id', DB::raw('count(*) as links_used'))
      ->whereIn('relationship_id', $relationships->pluck('id'))
      ->groupBy('relationship_id')
      ->pluck('links_used', 'relationship_id');

    $relationships = $relationships->map(function ($relationship) use ($module_slug, $links_used) {

      $relationship = self::getWithSide($relationship, $module_slug);

      // attach usage
      $relationship->links_used = $links_used[$relationship->id] ?? 0;

      return $relationship;
    });

    $relatedSlugs = $relationships
      ->pluck('related_slug')
      ->unique()
      ->values();

    $modules = Module::whereIn('slug', $relatedSlugs)
      ->get()
      ->keyBy('slug');

    Module::warmFieldsCache($modules);

    // Warms getModuleBySlug()'s cache from data we already have, so getDataForPanel() below doesn't re-query per relationship.
    foreach ($modules as $slug => $module) {
      self::$moduleCache[$slug] = $module;
    }

    return $relationships->map(function ($relationship) use ($modules) {

      $module = $modules->get($relationship->related_slug);

      $relationship->related_fields = $module
        ? $module->allFields()
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
    return DB::table('relationship_links')
      ->where('relationship_id', $relationship->id)->get();
  }

  /**
   * returns related ids to a record. side discovery happens on a model level
   */
  public static function getRelatedIds(Relationship $relationship, string $side, string $id)
  {
    return DB::table('relationship_links')
      ->where('relationship_id', $relationship->id)
      ->where($side . '_id', $id)
      ->pluck($side === 'left' ? 'right_id' : 'left_id');
  }

  /**
   * Get relationship and determine which side the given module is on
   */
  public static function getWithSide(Relationship $relationship, string $module_slug, ?string $module_id = null): Relationship
  {
    if ($relationship->left_module === $module_slug) {
      $relationship->side = 'left';
      $relationship->current_side = 'left';
      $relationship->other_side = 'right';
      $relationship->current_id_field = 'left_id';
      $relationship->other_id_field = 'right_id';
      $relationship->related_slug = $relationship->right_module;
      $relationship->current_module_id = $module_id;
    } elseif ($relationship->right_module === $module_slug) {
      $relationship->side = 'right';
      $relationship->current_side = 'right';
      $relationship->other_side = 'left';
      $relationship->current_id_field = 'right_id';
      $relationship->other_id_field = 'left_id';
      $relationship->related_slug = $relationship->left_module;
      $relationship->current_module_id = $module_id;
    } else {
      throw new RuntimeException(
        "Module {$module_slug} is not part of relationship {$relationship->name}"
      );
    }

    $relationship->role = self::resolveRole($relationship);

    return $relationship;
  }

  /**
   * Get relationship with resolved IDs for linking
   */
  public static function getWithResolvedIds(Relationship $relationship, string $module_slug, string $module_id, string $related_id): Relationship
  {
    $relationship = self::getWithSide($relationship, $module_slug, $module_id);

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
   * Get related records for a model
   */
  public static function getRelatedRecords(string $relationship_name, string $module_slug, string $module_id): Collection
  {
    $relationship = self::get($relationship_name);

    $relationship = self::getWithSide($relationship, $module_slug, $module_id);

    $relatedIds = DB::table('relationship_links')
      ->where('relationship_id', $relationship->id)
      ->where($relationship->current_id_field, $module_id)
      ->pluck($relationship->other_id_field);

    if ($relatedIds->isEmpty()) {
      return collect();
    }

    $relatedClass = self::resolveClassFromSlug($relationship->related_slug);

    return $relatedClass::query()
      ->whereIn('id', $relatedIds)
      ->get();
  }

  /**
   * Check if a relationship exists between two records
   */
  public static function exists(Relationship $relationship, string $module_slug, string $module_id, string $related_id): bool
  {
    $relationship = self::getWithResolvedIds($relationship, $module_slug, $module_id, $related_id);

    return DB::table('relationship_links')
      ->where('relationship_id', $relationship->id)
      ->where('left_id', $relationship->left_id)
      ->where('right_id', $relationship->right_id)
      ->exists();
  }

  public static function getDataForPanel(string $slug)
  {
    $module = self::getModuleBySlug($slug);

    return $module->getDataForPanel();
  }
  /**
   * on second thought this function's name does not sound right, perhaps it required changing in the future ---- ??? why
 
   * $includePanelData defaults true to keep the web panel unchanged; the
   * API passes false to skip getDataForPanel()'s unused metadata queries.
   */
  public static function getAllRelatedRecords(string $module_slug, string $recordId, bool $includePanelData = true): Collection
  {
    $relationships = self::getRelationshipForModule($module_slug);

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

      $rel = self::getWithSide($relationship, $module_slug, $recordId);
      $linksForRelationship = $allLinks[$relationship->id] ?? collect();

      $relatedIds = $linksForRelationship
        ->map(function ($link) use ($rel, $recordId) {
          return $link->{$rel->other_id_field};
        })
        ->unique()
        ->values();

      $count = $relatedIds->count();
      $records = collect();
      $pagination = null;
      $panel_limit = Settings::getPersonal('related_panel_limit');

      $relatedClass = self::resolveClassFromSlug($rel->related_slug);

      if ($relatedIds->isNotEmpty()) {
        $paginator = $relatedClass::query()
          ->whereIn('id', $relatedIds)
          ->paginate($panel_limit, ['*'], $relationship->name . '_page');

        $records = collect($paginator->items());
        $count = $paginator->total();

        // Structure the pagination data for the frontend
        $pagination = [
          'from'          => $paginator->firstItem() ?: 0,
          'to'            => $paginator->lastItem() ?: 0,
          'current_page'  => $paginator->currentPage(),
          'last_page'     => $paginator->lastPage(),
          'prev_page_url' => $paginator->previousPageUrl(),
          'next_page_url' => $paginator->nextPageUrl(),
        ];
      }
      $result[$relationship->name] = [
        'name'         => $relationship->name,
        'type'         => $relationship->type,
        'label'        =>  $relationship->label,
        'role'         =>  $relationship->role,
        'count'        => $count,
        'records'      => $records,
        'pagination'   => $pagination,
        'related_slug' => $relationship->related_slug,
      ];

      if ($includePanelData) {
        $panelData = self::getDataForPanel($relationship->related_slug);
        $result[$relationship->name] = array_merge($result[$relationship->name], $panelData);

        if ($relationship->role === "child" || $relationship->role === "sibling") {
          if ($count == 1) {
            $parent_record = array('parent_record' => $records->first());
            $result[$relationship->name] =  array_merge($result[$relationship->name], $parent_record);
          }
        }
      }
    }

    return $result;
  }

  /**
   * Get available records from related module for linking.
   */
  public static function getRecordsForLinking(
    Relationship $relationship,
    string $module_slug,
    string $recordId,
    int $perPage,
    ?string $search = null
  ) {

    $relationship = self::getWithSide($relationship, $module_slug, $recordId);

    // One-to-one: if already linked, nothing available
    if ($relationship->type === 'one-to-one') {
      $alreadyLinked = DB::table('relationship_links')
        ->where('relationship_id', $relationship->id)
        ->where($relationship->current_id_field, $recordId)
        ->exists();

      if ($alreadyLinked) {
        return collect();
      }
    }

    $relatedClass = self::resolveClassFromSlug($relationship->related_slug);

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
    if ($relationship->type === 'one-to-many') {
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

  public static function getRecordsForUpdateSingleLinking(
    Relationship $relationship,
    string $module_slug,
    string $recordId,
    int $perPage,
    ?string $search = null
  ) {

    $relationship = self::getWithSide($relationship, $module_slug, $recordId);
    $relatedClass = self::resolveClassFromSlug($relationship->related_slug);

    $query = $relatedClass::query();

    // Search
    if ($search) {
      $query->where('name', 'like', "%{$search}%");
    }
    return $query->orderBy('name')->paginate($perPage);
  }

  protected static function resolveRole(Relationship $relationship): string
  {
    switch ($relationship->type) {
      case 'one-to-one':
        return 'sibling';

      case 'one-to-many':
        return $relationship->current_side === 'left'
          ? 'parent'
          : 'child';

        // the reason we want why many-to-many to behave as a parent role in this relationship is because effectively both records are parents to each other
      case 'many-to-many':
        return 'parent';

      default:
        return 'parent';
    }
  }

  /**
   * Returns a query builder for related records in a given relationship,
   * Loads related records for panels
   */
  public static function loadRelatedRecords(string $module_slug, string $record_id, string $relationshipName): \Illuminate\Database\Eloquent\Builder
  {
    $relationship = self::get($relationshipName);
    $relationship = self::getWithSide($relationship, $module_slug, $record_id);

    $relatedIds = DB::table('relationship_links')
      ->where('relationship_id', $relationship->id)
      ->where($relationship->current_id_field, $record_id)
      ->pluck($relationship->other_id_field);

    $relatedClass = self::resolveClassFromSlug($relationship->related_slug);

    // todo: update the order by to be dynamic
    return $relatedClass::query()
      ->whereIn('id', $relatedIds)
      ->orderBy('created_at', 'desc');
  }
}
