# Relationships: Implementation Notes

Companion to `docs/guides/relationships-guide.md`, which covers the concept and workflow. This document covers the concrete mechanics: schema, cardinality, role resolution, and the pieces that render/edit related panels.

## 1. Schema

`relationships` table (`app/Models/Relationship.php`), one row per relationship *definition* (not per link):

```php
protected $fillable = ['type', 'right_module', 'name', 'label', 'left_module', 'is_system', 'join_table'];
```

`type` is a plain `string` column (`database/migrations/2026_02_10_081248_create_table_relationships.php:17`) — no DB enum constraint. Only three values are ever actually persisted: `one-to-one`, `one-to-many`, `many-to-many`. `left_module`/`right_module` are module slugs (string, not FK — same polymorphic-by-convention pattern as `AuditLog.module_slug`, `PdfTemplate.module_slug`).

**Directionality convention:** for `one-to-many`, `left_module` is always the "one" (parent) side and `right_module` is always the "many" (child) side. This is a pure convention enforced entirely by `RelationshipService::resolveRole()` (§3) — nothing at the schema level prevents storing it backwards, so every write path that creates a `one-to-many` row must get this right.

Actual links between two specific records live in a separate table, `relationship_links` (model: `RelationshipLink`, `app/Models/RelationshipLink.php`):

```php
protected $fillable = ['id', 'relationship_id', 'left_id', 'right_id'];
```

`RelationshipLink` extends `Model` directly, **not** `BaseModule` — deliberately, since it has no corresponding `Module` row, and extending `BaseModule` would make `getModuleSlug()` try (and fail) to resolve one (see `docs/dev/module-basemodule-implementation.md` §2.1). It dynamically routes to a table via `getTableForRelationship($type)`, currently a single `match` arm that always returns `relationship_links` — a placeholder for per-type join tables that was never needed in practice.

Self-referencing relationships (`left_module === right_module`) are blocked at the model level, not just the controller:

```php
protected static function booted()
{
    static::saving(function ($relationship) {
        if ($relationship->left_module === $relationship->right_module) {
            throw new \InvalidArgumentException('Self-referencing relationships are not allowed.');
        }
    });
}
```

## 2. Creating a relationship — the `many-to-one` swap

`RelationshipManagerController::store()` (`app/Http/Controllers/RelationshipManagerController.php`) always resolves `$leftModule` to whichever module's settings page the request came from (route-bound `Module $module`), and `$rightModule` to whatever the user picked in the form. That means picking "One To Many" always means *"the current module has many of the picked module"* — there was originally no way to express the opposite ("the current module has one of the picked module, i.e. many of the current module share one of the picked module") without navigating to the *other* module's settings page and creating it from there instead.

`many-to-one` was added as a **creation-time-only** option — it's validated (`'type' => ['required', 'in:one-to-one,one-to-many,many-to-one,many-to-many']`) but never persisted as-is:

```php
$type = $validated['type'];

if ($type === 'many-to-one') {
    [$leftModule, $rightModule] = [$rightModule, $leftModule];
    $type = 'one-to-many';
}
```

The swap happens **before** the duplicate-relationship check and the `Relationship::create()` call, so:
- The stored row is byte-for-byte identical to one created the "long way" from the other module's page — `left_module` = the picked module (the "one"), `right_module` = the current module (the "many").
- The duplicate check (`where('left_module', ...)->where('right_module', ...)->where('type', ...)`) correctly catches a `many-to-one` submission that duplicates an existing `one-to-many` relationship created from the other side, since it runs against the already-swapped values.
- Nothing downstream (`resolveRole()`, `getRelationshipForModule()`, linking, panels, layouts) needs to know `many-to-one` was ever selected — it only ever sees `one-to-many`.

The type list itself lives in a `DropdownList` row (key `relationship_type_list`), seeded from `config/dropdown_lists.php` by `dropdownListSeeder` via `updateOrCreate(['key' => $key], ['values' => $values])` — editing the config alone doesn't change the running app until that seeder is re-run. `config/default_relationship_types.php` is a second, separate list of the same three *original* types, passed to `Create.vue` as a `types` prop — but that prop is unused in the component (confirmed via grep, no references in the template); the dropdown is actually driven by the `typeList` prop / `relationship_type_list` `DropdownList`. `many-to-one` was only added to the latter.

## 3. Role resolution — how a relationship becomes "parent"/"child"/"sibling"

`RelationshipService::getWithSide(Relationship $relationship, string $module_slug, ?string $module_id = null)` (`app/Services/Relationships/RelationshipService.php:328`) is the single place that turns a stored `left_module`/`right_module`/`type` row into something meaningful *from the perspective of whichever module is currently asking*:

```php
if ($relationship->left_module === $module_slug) {
    $relationship->side = 'left';
    $relationship->related_slug = $relationship->right_module;
} elseif ($relationship->right_module === $module_slug) {
    $relationship->side = 'right';
    $relationship->related_slug = $relationship->left_module;
} else {
    throw new RuntimeException("Module {$module_slug} is not part of relationship {$relationship->name}");
}

$relationship->role = self::resolveRole($relationship);
```

`resolveRole()`:

```php
case 'one-to-one':   return 'sibling';
case 'one-to-many':  return $relationship->current_side === 'left' ? 'parent' : 'child';
case 'many-to-many': return 'parent'; // both sides are symmetrically "parent" to each other
```

This is why `many-to-one` doesn't need to be a real stored type: the *same* `one-to-many` row resolves to `role='parent'` when viewed from the left module and `role='child'` when viewed from the right module — direction is entirely a function of which module is asking, computed fresh on every read, never stored.

`role` drives real UI differences, not just semantics:

- `Panel.vue`/`PanelHeader.vue` (`resources/js/Pages/Components/Modules/Relatedpanels/`): `role === 'parent'` shows the plural module label + a count badge + pagination (a list); `role === 'child'` or `'sibling'` shows the singular label and a single related record, with a one-click "unlink" action directly in the panel header (`handleUnlinkParent()` in `Panel.vue`) instead of a list-management UI.
- `record.vue`'s related-panel gating (`isSingleSelectRelationship`) uses the same `role === 'child' || role === 'sibling'` check to decide single-select vs. multi-select linking UI.

## 4. Cardinality enforcement lives in `link()`, not `enforceCardinality()`

`RelationshipService::enforceCardinality()` (`app/Services/Relationships/RelationshipService.php:72`) is **dead code** — defined, but never called from anywhere in the app (confirmed via grep). It would `throw` a `RuntimeException` on a duplicate link.

The actual behavior lives inline in `RelationshipService::link()`'s `switch ($relationship->type)`:

```php
case 'one-to-one':
    // Remove any existing link involving either side
    DB::table('relationship_links')->where('relationship_id', $relationship->id)
        ->where(fn ($q) => $q->where('left_id', $leftId)->orWhere('right_id', $rightId))->delete();
    break;

case 'one-to-many':
    // Remove existing parent of this child
    DB::table('relationship_links')->where('relationship_id', $relationship->id)
        ->where('right_id', $rightId)->delete();
    break;

case 'many-to-many':
    // Still prevent duplicates (throws)
    ...
```

For `one-to-many` (and therefore any relationship created as `many-to-one`), linking a "many"-side record to a new "one"-side record **silently re-parents it** — the old link is deleted, the new one inserted, in the same `DB::transaction`. It does not throw, and it does not create a second link. `many-to-one` relationships inherit this for free since they're stored as `one-to-many`; no special-casing was needed. See `tests/Feature/Modules/RelationshipManyToOneLinkingTest.php::test_relinking_a_deal_to_a_new_account_reparents_instead_of_duplicating`.

## 5. Linking/unlinking write path

`RelationshipLinkController` (`app/Http/Controllers/RelationshipLinkController.php`) → `BaseModule::link()`/`unlinkRelation()` (thin wrappers) → `RelationshipService::link()`/`unlink()`. Both resolve `left_id`/`right_id` via `getWithResolvedIds()`, which just calls `getWithSide()` and then assigns `$module_id`/`$related_id` to `left_id`/`right_id` based on which side the calling module is on — so the caller never needs to know or care whether it's the left or right side of the stored row.

Every link/unlink logs **on both sides** via `logLinkChange()` — one `AuditService::log()` call attributed to the acting module's record, one to the related record — so either record's own history shows the connection regardless of which side the action was performed from. See `docs/dev/audit-trail-implementation.md` §4.3 and `tests/Feature/Audit/RelationshipLinkAuditTest.php`.

| Route | Controller method | Purpose |
|---|---|---|
| `GET /modules/{module}/{record}/relationships/{relationship}/available` | `getRecordsForLinking` | Paginated/searchable candidates for the "add records" picker |
| `GET /modules/{module}/{record}/relationships/{relationship}/single-link` | `getRecordsForUpdateSingleLinking` | Same, for the single-select (child/sibling) re-parent picker |
| `POST /modules/{module}/{record}/relationships/{relationship}` | `linkRecords` | Link one or more `related_ids` |
| `DELETE /modules/{module}/{record}/relationships/{relationship}/{relatedId}` | `unlink` | Unlink one record |
| `GET /modules/{module}/{record}/relationships/{relationship}` (paginated) | `loadRecords` | Load a panel's already-linked records, paginated |

## 6. Related panels — layout types and rendering chain

Two distinct layout types govern what's shown, configured per module via the Layouts editor (`Settings/Layouts/Edit.vue`):

- **`related`** (`LayoutRelatedEditor.vue`) — which relationships appear as panels on a record's Related tab, grouped into columns/sections. Stored shape: `{ columns: [{ layout: [{ name: <relationship_name> }, ...] }] }`. Consumed by `PanelList.vue`, which maps each `panel.name` to its resolved relationship (via `props.relationships`, the `related` prop on the Inertia record page) and renders one `Panel.vue` per entry.
- **`linkingPanel`** (`LayoutLinkingPanelEditor.vue`) — which *fields* (as extra columns) appear in the record-selector overlay (`RecordSelectorDrawer.vue`/`RelatedLinksOverlay.vue`) when picking a record to link. Independent of `related` — a relationship can appear as a panel without a custom linking-panel column set (falls back to just the primary label column).

Rendering chain for one panel: `PanelList.vue` → `Panel.vue` (role-aware header/body split, §3) → `PanelHeader.vue` (label/count/single-record display) + `PanelBody.vue` (the actual record list or single record, plus pagination for `role='parent'`). Linking a new record opens `RelatedLinksOverlay.vue` → `RecordSelectorDrawer.vue` (search + paginated candidate list, reused elsewhere for plain `record`-type field pickers too — see `MassUpdateZone.vue`'s bulk-update-to-a-record-field picker).

`related_panel_limit` and `linking_panel_limit` (`Settings::get(...)`) control page size for the panel body and the link-candidate picker respectively — admin-configurable, not hardcoded.

## 7. Deleting a relationship — cleans up both sides

`RelationshipManagerController::destroy()` calls `$relationship->cleanupRelationshipPanels()`, which removes the relationship from `related`-type layouts on **both** sides — not just the module the delete request came from:

```php
public function cleanupRelationshipPanels(): void
{
    $moduleIds = Module::query()
        ->whereIn('slug', [$this->left_module, $this->right_module])
        ->pluck('id');

    $layouts = Layout::where('type', 'related')->whereIn('module_id', $moduleIds)->get();
    // ... strips $relationshipName from each layout's columns[].layout[]
}
```

Originally this only took a single `$module_id` (the module whose settings page the delete came from), so deleting a relationship from one side left the other side's layout referencing a relationship that no longer existed — flagged here as a known gap since it was found while implementing the `many-to-one` type (same controller method), not introduced by it. Since a relationship can (and normally does) have a panel configured on **both** sides' `related` layouts, the fix resolves `left_module`/`right_module` to their module ids directly from the relationship itself, rather than trusting the caller to know both sides. Covered by `tests/Feature/Modules/RelationshipManagerRouteTest.php::test_deleting_a_relationship_cleans_up_related_panels_on_both_sides`.

## 8. Reference

- `docs/guides/relationships-guide.md` — plain-language guide.
- `tests/Feature/Modules/RelationshipManagerRouteTest.php` — route scoping (`edit`/`update`/`show` excluded), system-relationship delete protection, `many-to-one` swap + duplicate detection.
- `tests/Feature/Modules/RelationshipManyToOneLinkingTest.php` — linking/unlinking mechanics through a `many-to-one`-created relationship: link/unlink DB rows, re-parenting on relink, role resolution on both sides.
- `tests/Feature/Audit/RelationshipLinkAuditTest.php` — both-sides audit logging for link/unlink.
