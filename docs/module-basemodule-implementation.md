# `Module` and `BaseModule`: Implementation Notes

Companion to `docs/module-basemodule-guide.md`, which covers the conceptual split. This document covers the concrete mechanics: schema, caching, coupling points, and gotchas worth knowing before touching either class.

## 1. `Module` (`app/Models/Module.php`)

Concrete Eloquent model, backed by the `modules` table, one row per module type (built-in or custom). Traits: `HasTranslatableLabel`, `HasUuids`. Guarded by a global scope, `AdminOnlyModuleScope` (`app/Scopes/AdminOnlyModuleScope.php`), registered in `booted()`:

```php
protected static function booted(): void
{
    static::addGlobalScope(new AdminOnlyModuleScope);
}
```

That scope hides admin-only module rows (`users`, `settings`) from non-admin querents by default. Anything that needs to resolve a `Module` row **regardless of the acting user's role** — i.e. any internal/structural lookup rather than a user-facing listing — must bypass it explicitly with `Module::withoutGlobalScope(AdminOnlyModuleScope::class)`. See §5.1 of `docs/audit-trail-implementation.md` for a real bug this scope caused (`BaseModule::getModuleSlug()` throwing for non-admins before the bypass was added).

### 1.1 Responsibilities

- **Field resolution** — `fields()`, `allFields()`, `allEditableFields()`, `draftFields()`, `relatedfields()`, `builderFields()`, `getFieldMetadata()`. These query the `fields` table, scoped to this module plus any globally-shared fields (`is_global`), and (for `has_line_items` modules) the default line-item fields.
- **Layout resolution** — `resolveLayout()` and its typed wrappers `listLayout()`, `recordLayout()`, `relatedLayout()`, `linkingPanelLayout()`, `lineItemsSnapshotLayout()`. Three-tier fallback: DB `Layout` row → `config('module_layouts.{slug}')` → global default (`Layout::getDefaultLayout()`).
- **Sidebar/manager listing** — `forSidebar()` (static), returns the trimmed shape the sidebar nav needs.
- **Instantiation** — `getInstance()` builds a live business-model instance from `model_class`.
- **Line-item source resolution** — `lineItemSourceModuleSlug()`, `canChangeLineItemSourceModule()` (the latter guards against changing the source once line items already exist for the module, which would orphan existing `product_id` references).

### 1.2 Two static in-process caches

```php
protected static array $staticFieldCache = [];   // keyed by Module id
protected static array $staticLayoutCache = [];   // keyed by "{Module id}:{layout type}"
```

Both are request-lifetime memoization, not a cross-request cache (no TTL, no invalidation, no cache store involved — just a static array). `allFields()` populates `$staticFieldCache[$this->id]` lazily; `warmFieldsCache(Collection $modules)` exists to batch-populate it for a whole collection of modules up front (one query for module-specific + global fields, one for line-item defaults if any module in the batch needs them), avoiding the N+1 that calling `allFields()` in a loop would otherwise cause. This is the same class of bug documented in §5.4 of `docs/audit-trail-implementation.md` — `warmFieldsCache()` is the fix pattern for it when iterating multiple modules.

**Caveat:** because these are plain static arrays with no request-boundary reset beyond PHP's own process lifecycle, they're safe within a single request/CLI invocation but must not be assumed to reflect fresh data across requests in long-running contexts (queue workers, Octane) without an explicit reset — none currently exists.

### 1.3 `select()` allowlists are a footgun

Every field query in this class enumerates an explicit `select([...])` column list rather than `select('*')`. Any column added to the `fields` table will be **silently un-hydrated** everywhere it's read via `Module` unless it's added to every relevant `select()` array here too. This has already caused one real bug: `is_calculated` had to be added to `allFields()`'s two `select()` lists (`allFields()` and `warmFieldsCache()`) as part of the audit trail feature, otherwise the flag never made it into memory even though the column existed and was set (`docs/audit-trail-implementation.md` §4.4, §9). When adding a new `fields` column that any downstream consumer needs, grep this file for `select([` and update every list that should carry it.

## 2. `BaseModule` (`app/Models/BaseModule.php`)

Abstract model, no table of its own (`$table` is set per concrete subclass). Traits: `HasCustomFields`, `HasDynamicRelationships`, `HasFactory`, `HasTranslatableLabel`, `HasUuids`, `Searchable` (Scout). UUID primary key (`$incrementing = false`, `$keyType = 'string'`).

### 2.1 Concrete subclasses

Every module's business model lives in `app/Models/Modules/` and extends `BaseModule` directly:

```php
class Deal extends BaseModule
{
    protected $table = 'deals';
    protected $fillable = ['name', 'owner_id', 'amount', 'sales_stage', 'probability', 'expected_close_date', 'type'];

    public function getCasts(): array
    {
        return [...parent::getCasts(), 'amount' => 'decimal:2', 'expected_close_date' => 'date'];
    }

    public function toSearchResult(): array
    {
        return [...parent::toSearchResult(), 'label' => $this->name, 'sublabel' => $this->probability];
    }
}
```

Current subclasses: `Deal`, `Contact`, `Account`, `Invoice`, `Order`, `Quote`, `Product`, `Lead`, `SupportCase`, `LineItem`. Each overrides `$table`/`$fillable` and may override `getCasts()`/`toSearchResult()` for module-specific behavior, while inheriting everything structural from `BaseModule`.

One exception worth knowing: `RelationshipLink` extends `Model` directly, **not** `BaseModule` — deliberately, since it has no corresponding `Module` row, and extending `BaseModule` would make `getModuleSlug()` try (and fail) to resolve one. See `docs/audit-trail-implementation.md` §4.3.

### 2.2 `moduleCasts` escape hatch

```php
public function __construct(array $attributes = [])
{
    parent::__construct($attributes);
    if (property_exists($this, 'moduleCasts')) {
        $this->casts = array_merge($this->casts, $this->moduleCasts);
    }
}
```

Lets a subclass declare a `protected array $moduleCasts` property instead of overriding `getCasts()`, used by modules like Deals/Cases where casts need to be merged onto the base `custom_fields => array` cast rather than replacing it outright.

### 2.3 Per-subclass event registration (late static binding)

```php
protected static function booted(): void
{
    static::creating(function (self $model) {
        if (empty($model->owner_id)) {
            $model->owner_id = static::getDefaultOwnerId();
        }
    });
    static::bootAuditObserver();
}

protected static function bootAuditObserver(): void
{
    static::observe(\App\Observers\AuditObserver::class);
}
```

This is registered from `booted()`, not `AppServiceProvider::boot()`, specifically because Laravel's Eloquent event dispatcher keys observer bindings to the literal class passed to `observe()`. A single `BaseModule::observe(...)` call in a service provider would bind against `App\Models\BaseModule` itself and never fire for `Deal`/`Contact`/etc., since those fire events under their own class names. Because `booted()` runs once per *concrete* subclass (Eloquent tracks a `$booted` flag per class), `static::` here late-binds correctly to whichever concrete class is actually booting. Full writeup: `docs/audit-trail-implementation.md` §4.1.

`User` does **not** extend `BaseModule`, but deliberately mirrors this pattern: it overrides `booted()` without calling `parent::booted()` (since `users` has no `owner_id` column and the owner-defaulting closure would crash), but still calls `static::bootAuditObserver()` itself so audit events aren't silently lost.

### 2.4 Looking up its own `Module` row

```php
public function moduleDefinition(): Module
{
    return Module::withoutGlobalScope(\App\Scopes\AdminOnlyModuleScope::class)
        ->where('model_class', static::class)
        ->firstOrFail();
}

public static function getModuleSlug(): string
{
    return static::$moduleSlugCache[static::class] ??=
        Module::withoutGlobalScope(\App\Scopes\AdminOnlyModuleScope::class)
            ->where('model_class', static::class)->value('slug');
}
```

Both explicitly bypass `AdminOnlyModuleScope` (see §1 above) — this must resolve for any authenticated user, admin or not, since it's answering "which module row describes this model class," not "which modules can this user see." `getModuleSlug()` additionally memoizes per-class in `static array $moduleSlugCache`, keyed by `static::class`, so repeated calls within a request don't re-hit the database.

`moduleDefinition()` is currently only called from `searchableFields()` internally — flagged in the source as possibly warranting a different approach if more call sites appear.

### 2.5 `searchableFields()` reaches into `Module::allFields()`

```php
protected function searchableFields(): array
{
    $module = $this->moduleDefinition();
    $dbFields = $module->allFields()->where('searchable', true)->pluck('name')->toArray();
    return array_unique($dbFields);
}
```

This is the one place `BaseModule` reads back into `Module`'s field-resolution logic rather than just its own attributes, since "which fields are searchable" is metadata that lives on the `Module`/`Field` side, not something a business record can answer about itself.

## 3. The coupling surface, summarized

There are exactly two directions of coupling between the classes, and both are narrow:

| Direction | Call | Purpose |
| --- | --- | --- |
| `Module` → `BaseModule` | `Module::getInstance()` | Instantiate the concrete business class for this module row |
| `BaseModule` → `Module` | `moduleDefinition()`, `getModuleSlug()`, `searchableFields()` | Resolve this record's own module metadata |

No other file needs to know both classes exist simultaneously; most consumers only ever touch one side (e.g. the Module Builder UI only touches `Module`/`Field`/`Layout`; `RecordController` mostly touches concrete `BaseModule` subclasses).

## 4. Why these can't be merged

Raised and settled in review: merging `Module` and `BaseModule` into a single class isn't a stylistic call, it's blocked by Eloquent's one-model-one-table assumption plus a cardinality mismatch:

- `modules` has **one row per module type**. `deals`, `contacts`, etc. each have **many rows**, one per actual record. A single Eloquent model can't represent both a "one row" registry entry and a "many rows, in a different table per type" record store at once without single-table-inheritance across every business table, a schema rewrite, not a refactor.
- `Module` carries `AdminOnlyModuleScope`; `BaseModule` subclasses must not, records like a `Deal` aren't admin-only just because the `modules` row describing "Deals" might be hidden from a non-admin in some listing context.
- `BaseModule` being abstract, extended by many concrete classes (`Deal`, `Contact`, ...) each with their own table, is the entire mechanism for "shared behavior, one class per module type." `Module` is concrete and generic, with no subclasses. There's no shape that satisfies both patterns in one class.

If the split feels confusing day to day, the friction is more likely that `Module` has accumulated field/layout **resolution** logic (§1.1) alongside pure metadata storage, than that the two-class split itself is wrong. If that logic grows further, extracting it into a dedicated service (mirroring `app/Services/Relationships/RelationshipService.php`) would be a more direct fix than merging the models.

## 5. Where new logic should go

Concrete rule of thumb, matching the guide's table:

- Touches `modules`/`fields`/`layouts` tables, or answers a question about a module **type** rather than a specific record → `Module`.
- Needs `$this->id` or any other record attribute, or is behavior every module record should share → `BaseModule`.
- Is specific to one module only → override on the concrete subclass (`Deal`, `Contact`, ...), not on `BaseModule`.
