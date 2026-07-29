# Conversion Rules: Implementation Notes

Companion to `docs/guides/en/conversion-guide.md`, which covers the concept and workflow. This document covers the concrete mechanics: schema, the execution engine, condition/operator reuse, the manual and automatic run paths, and the Studio CRUD.

Internally this feature is named `Transformation` throughout the codebase (model, migrations, controllers, routes, table names) — "Conversion"/"Conversion Rule" is UI-facing terminology only, chosen after implementation because "Transformation" read as inaccurate to a user (see the docblock on `app/Models/Transformation.php`). Nothing was renamed at the code level; don't be thrown by the mismatch.

## 1. Schema

`transformations` table (`database/migrations/2026_07_27_090000_create_table_transformations.php`), one row per conversion rule:

```php
$table->uuid('id')->primary();
$table->string('name');
$table->string('source_module');
$table->string('target_module');
$table->text('description')->nullable();
$table->boolean('enabled')->default(true);
$table->boolean('automation_enabled')->default(false);
$table->json('conditions')->nullable();
$table->string('conditions_match')->default('all');
$table->boolean('link_records_enabled')->default(true);
$table->uuid('relationship_id')->nullable();
```

`source_module`/`target_module` are module slugs (string, not FK — same polymorphic-by-convention pattern used elsewhere, e.g. `AuditLog.module_slug`). `relationship_id` points at the system `Relationship` row this rule uses to link source→target (see §5); it's `nullable` because `link_records_enabled` can be off.

Per-rule pipeline steps live in a separate `transformation_steps` table (model `TransformationStep`), one row per step, ordered by an `order` column, each carrying its own `type` (string, no DB enum) and a JSON `configuration`. There is deliberately no one-off migration history for this feature — every schema change made during development was folded directly into the single base migration above, per project convention (`migrate:fresh --seed` must always reflect the current shape).

**Self-conversion is blocked at the model level**, not just validation:

```php
static::saving(function (self $transformation) {
    if ($transformation->source_module === $transformation->target_module) {
        throw new \InvalidArgumentException('A transformation cannot target its own source module.');
    }
});
```

## 2. Execution engine

`TransformationEngine::run(Transformation $transformation, BaseModule $sourceRecord, ?User $actor = null, bool $skipLinking = false): BaseModule` (`app/Services/Transformations/TransformationEngine.php`) is the single entry point for both manual and automatic runs. It:

1. Rejects a disabled transformation outright (`TransformationException`).
2. Wraps the whole run in one `DB::transaction()` — every V1 step type is a pure DB write, so any step failing rolls back record creation, field copies, and relationship copies together.
3. Builds a `TransformationContext` (mutable value object threaded through every step — `targetRecord`, a running `summary` array, and the resolved `actor`) and runs each of the transformation's `steps` in order through a fixed executor map:

```php
protected const EXECUTORS = [
    'create_record' => CreateRecordExecutor::class,
    'copy_fields' => CopyFieldsExecutor::class,
    'copy_relationships' => CopyRelationshipsExecutor::class,
    'link_records' => LinkRecordsExecutor::class,
];
```

`$skipLinking` lets a caller run every step except `link_records` — used by the manual "create without linking" choice when a one-to-one conflict is detected (§4).

A class-level `TODO` flags that all four executors are pure synchronous DB writes; non-transactional step types (PDF/email/webhook/delay) are explicitly out of scope for V1 and would need the transaction boundary revisited before being added.

### Executors (`app/Services/Transformations/Executors/`)

- **`CreateRecordExecutor`** — resolves the target module's `model_class`, sets `name` from the source record and `owner_id` (source's owner, falling back to the acting user), and saves. This is a separate, minimal `fill()`+`save()`, deliberately not routed through `RecordController::store()` (which has no field validation of its own).
- **`CopyFieldsExecutor`** — applies every configured field mapping (`mode: field|static|expression`) onto the already-created target record, then saves again. Studio config is the *only* source of truth here — there is no per-run override; the earlier overlay-based "edit before creating" UI was removed entirely (see the pivot noted in git history / the Studio section below).
- **`CopyRelationshipsExecutor`** — copies every relationship key configured in Studio, unconditionally, whether the run is manual or automatic (no per-run checkbox selection either). `line_items` is a special sentinel handled by cloning `LineItem` rows (`replicate()`, re-pointed `parent_type`/`parent_id`, `calculateTotals()->save()`); any other key is a related module's slug, resolved to the actual relationship name independently on each side (`RelationshipService::getRelationshipBetween()`) since the source and target modules can have differently-named relationships to the same related module.
- **`LinkRecordsExecutor`** — a single `RelationshipService::link()` call using the transformation's own `relationship_id`. This one call is what makes the "Created From"/"Converted To" connection and Relationships-tab visibility work with no other new plumbing.

### Expression evaluator

`ExpressionEvaluator` (`app/Services/Transformations/ExpressionEvaluator.php`) is a small, whitelisted, non-`eval()` evaluator for the `expression` field-mapping mode. An expression is an array of segments (`{type: text|field|helper, value}`), concatenated in order:

- `text` — a literal string, verbatim.
- `field` — a source-record field value, validated against the source module's real fields (`Module::allFields()`) plus `name`/`description` — throws `InvalidExpressionException` on an unknown field, both at save time (`validateExpression` endpoint) and at run time.
- `helper` — one of `today()` (current date), `current_user()` (actor's name), `uuid()` — a fixed, hardcoded map, no user-supplied code path at all.

## 3. Condition evaluation reuses the List Filter operator vocabulary

Conditions (`{field, operator, value}[]`, plus a `conditions_match: all|any`) are evaluated in-memory by `Transformation::evaluateConditions()`/`evaluateSingleCondition()` (`app/Models/Transformation.php`) — **not** a query builder, since they're checked against an already-loaded record, not the database.

The operator set is deliberately the same one the record list's filter builder uses (`config/filter_operators.php`, `App\Support\Filters\FilterOperators`/`FilterQueryBuilder`), rather than a separate hand-rolled `==`/`!=`/`>`/`<` set the feature originally shipped with:

```php
return match ($condition['operator'] ?? 'equals') {
    'equals', 'not_equals', 'contains', 'not_contains', 'starts_with',
    'greater_than', 'less_than', 'before', 'after', 'between', 'in',
    'is_empty', 'is_not_empty' => /* ... */,
};
```

`between`/`in` take an array value; everything else a scalar. `TransformationsManagerController::validateRequest()` validates a submitted operator against the full union of every field type's allowed operators (`filter_operators.by_type` flattened + `filter_operators.default`), not per-field-type — the per-field-type restriction only actually matters client-side (the Studio condition builder's operator dropdown is populated from `operatorsForType()`, mirroring `FilterZone.vue`'s own `operatorsForType()`).

An empty conditions list always passes (`evaluateConditions([])` returns `true` by design) — but see §4.2, this state is now unreachable for `automation_enabled` rules via validation, not by changing that method's own semantics.

Two match modes only (`all`/`any`), no per-condition boolean grouping trees. With `all`, the same field can't be used twice — enforced both client-side (`Edit.vue`'s `conditionFieldOptionsFor()` excludes fields already used by other rows when match is `all`) and server-side (`validateRequest()`'s duplicate-field check), since "status is accepted AND status is draft" can never be true.

## 4. Manual run: `TransformationRunController`

Three endpoints (`routes/web.php`, `auth`-gated alongside the rest of `RecordController`):

| Route | Method | Purpose |
|---|---|---|
| `GET /modules/{module}/{recordId}/transformations` | `available` | Every `enabled` transformation whose `source_module` matches, for the record page's "Convert" modal |
| `GET /transformations/{transformation}/{recordId}/preview` | `preview` | Checks `findConflictingLink()` — is there an existing one-to-one link that running this would silently replace? |
| `POST /transformations/{transformation}/{recordId}/run` | `run` | Runs it. Body: `skip_link` (bool) |

Conditions **do not gate manual visibility or execution at all** — `available()` only filters on `enabled`, and `run()` only checks `enabled`, never `passesConditions()`. This was a deliberate pivot from the feature's original design (conditions used to gate whether the manual action even appeared) after the user-facing confusion that produced was flagged directly: "any user can run any conversion rule whenever they want manually."

### One-to-one link conflicts

`Transformation::findConflictingLink(BaseModule $sourceRecord): ?array` returns the currently-linked other-side record (id + display name) only when the transformation's relationship is `type === 'one-to-one'` and something is already linked. The frontend (`TransformationModal.vue`) calls `preview()` before running; if a conflict comes back, it shows an inline confirm step offering **Override and link** (`run` with `skip_link: false`, which lets `RelationshipService::link()`'s own one-to-one replace-on-link behavior do the work) or **Create, don't link** (`run` with `skip_link: true`, which makes `TransformationEngine` skip the `link_records` step entirely).

### Frontend: `TransformationModal.vue`

Modeled directly on `PdfModal.vue`'s phase pattern (`select | checking | confirm | running | error`), mounted from `Record.vue`'s action dropdown behind a single **Convert** item (only shown when `availableTransformations.length > 0`, sourced from `RecordController`'s Inertia payload). Lists every available transformation by name/icon, plus a "Create new conversion rule" link straight into Studio. On success, shows a toast with an "Open" action pointing at the new record — the source record's own page is never navigated away from.

## 5. Automatic run: observer + provider

`TransformationAutomationObserver::saved(BaseModule $model)` (`app/Observers/TransformationAutomationObserver.php`) is the entire automation hook — a targeted observer, not a generic trigger/condition/action engine (none exists in Cubrel yet):

```php
$transformations = Transformation::where('source_module', $moduleSlug)
    ->where('enabled', true)
    ->where('automation_enabled', true)
    ->get();

foreach ($transformations as $transformation) {
    $conditionFieldChanged = collect($transformation->conditions ?? [])
        ->contains(fn (array $c) => $model->wasChanged($c['field'] ?? null));

    if (! $conditionFieldChanged) continue;

    if ($transformation->passesConditions($model)) {
        app(TransformationEngine::class)->run($transformation, $model);
    }
}
```

### 5.1 Registration is opt-in per module, not global on `BaseModule`

`TransformationServiceProvider::boot()` (`app/Providers/TransformationServiceProvider.php`, registered in `bootstrap/providers.php`) queries which `source_module` slugs have at least one `automation_enabled` transformation, resolves each to its `model_class`, and calls `$modelClass::observe(TransformationAutomationObserver::class)` only for those. Every other module carries zero query cost on save. Wrapped in a `try`/`catch` that silently no-ops (returns) on any `\Throwable` — this specifically covers a fresh install before the `transformations` table/migration exists yet, since a service provider's `boot()` runs on every request including ones before `migrate` has run.

### 5.2 Idempotency guard, and its known gap

`wasChanged()` on at least one condition field is required before `passesConditions()` is even checked — this is what stops the observer firing on every unrelated save of an already-qualifying record. It does **not** prevent a record from spawning a second target if a condition field is toggled off and back on across separate saves — each save is evaluated independently, so this refires. Accepted V1 limitation (one-to-many relationship, not a generic rule engine's job to solve) — documented directly in the observer's own docblock.

**A related edge case, now closed off rather than fixed in place**: if `conditions` were empty, `collect([])->contains(...)` is always `false`, so the observer would never fire at all for that transformation — silently contradicting `evaluateConditions([])`'s own "empty list always passes" semantics. Rather than changing the observer's logic, this was made unreachable instead: `automation_enabled` can no longer be saved with zero conditions (§6.2), so `conditions` is guaranteed non-empty for anything this observer will ever actually query.

### 5.3 One-to-one link risk on automatic runs

Automatic runs always call `TransformationEngine::run()` with `$skipLinking = false` (default) — there is no confirmation step available, since nothing is watching. If `link_records_enabled` is on and the underlying relationship is one-to-one, every automatic run silently replaces whichever record was previously linked (`RelationshipService::link()`'s one-to-one branch deletes the old link row before inserting the new one). The Studio "Setup" tab surfaces this explicitly as a warning banner when both `automation_enabled` and `link_records_enabled` are true (`Edit.vue`, `.transformations-edit__link-warning`) — there is no code-level guard against it, it's a user-facing warning only.

## 6. Studio CRUD: `TransformationsManagerController`

Routes under `settings/transformations` (admin-gated, alongside the rest of Settings):

| Route | Method | Purpose |
|---|---|---|
| `GET /settings/transformations` | `index` | List, each row's icon borrowed from its target module |
| `GET /settings/transformations/create` | `create` | New-rule form |
| `POST /settings/transformations` | `store` | Create |
| `GET /settings/transformations/{transformation}` | `edit` | Edit form |
| `PUT /settings/transformations/{transformation}` | `update` | Update |
| `PATCH /settings/transformations/{transformation}/toggle` | `toggle` | Flip `enabled` only (list-view row action) |
| `DELETE /settings/transformations/{transformation}` | `destroy` | Delete |
| `POST /settings/transformations/expressions/validate` | `validateExpression` | Validate one expression's segments without running a real transformation |

### 6.1 Fixed step order, not a freeform builder

`store()`/`update()` assemble `transformation_steps` server-side in one canonical order (`STEP_ORDER = ['create_record', 'copy_fields', 'copy_relationships']`, plus `link_records` appended conditionally) via `syncSteps()` — the Studio form is section-based (Setup tab / Mapping tab), not a drag-and-reorder pipeline builder, since V1's step types always run in this one order regardless. `syncSteps()` is keyed purely off the submitted `link_records_enabled` value; which Studio tab the corresponding checkbox lives in has no bearing on this logic (the toggle was moved from the Mapping tab to Setup partway through development — a pure UI relocation, `syncSteps()` didn't need to change).

### 6.2 Save-time validation beyond basic field rules

`validateRequest()` throws `ValidationException` (surfaced to the user as the actual message, not a generic fallback — see `Edit.vue`'s `submit()`) for three business-rule violations basic Laravel rules can't express:

1. **Duplicate mapped target field** — the same target field configured twice in `field_mappings` (which value would even apply?).
2. **Duplicate condition field under `conditions_match: all`** — see §3.
3. **`automation_enabled` with zero conditions** — an automatic rule with nothing to check would never actually run (see §5.2's gap this closes off). Enforced client-side too (`Edit.vue`'s `goToStep()` blocks leaving the Setup tab, and a `watch()` on `automation_enabled` auto-adds one empty condition row so the panel is never blank) — the server check exists specifically in case that client gate gets bypassed.

### 6.3 Record-type condition values resolve a display label server-side

`edit()` runs saved conditions through `withResolvedConditionLabels()`, which for any `record`-type condition field looks up the related record fresh from the DB (same pattern as `AuditObserver`'s change-diff labels) and injects a `valueLabel` alongside the stored id — so the condition builder shows the record's actual name instead of a bare UUID, and self-heals if that record gets renamed later, since it's resolved on every page load rather than trusted from a stale stored value.

### 6.4 Relationship auto-provisioning

`Transformation::ensureRelationship()` runs on every `saved()` (model `booted()` hook). If `link_records_enabled` is off, it does nothing. Otherwise it reuses an existing `Relationship` between the two modules if one already exists, or creates a new system `one-to-many` relationship named `{source}_{target}_transformation` — this is the *only* new relationship-creation code path this feature needed; everything else (linking, unlinking, Relationships-tab rendering) is the existing generic relationship system.

## 7. Reference implementation seeder

`database/seeders/TransformationSeeder.php` seeds a Quote → Invoice conversion rule as ordinary rows (`updateOrCreate`, idempotent against `migrate:fresh --seed`), not migration-baked, so it stays fully editable through Studio afterward like any admin-created rule. Exercises every configurable option: two AND'd conditions (`status equals accepted`, `total greater_than 0`), field/static/expression mapping modes, a `line_items` + generic relationship copy, and linking. Model events are suppressed during seeding (`DatabaseSeeder` uses `WithoutModelEvents`), so `ensureRelationship()` is called explicitly rather than relying on the `saved()` hook.

## 8. Known gaps / not built

- **No automated test coverage** — this feature has no dedicated test suite as of this writing.
- **Duplicate-on-toggle** automation firing (§5.2) — accepted V1 limitation, not fixed.
- **One-to-one silent override on automatic runs** (§5.3) — warned about in the UI, not prevented in code.
- **No generic action/trigger engine** — "run this transformation" is the only action type; a sketched V2 direction (discussed but not built) would generalize this into a proper `Automation` model (trigger type + conditions + an ordered list of action types, of which running a transformation would be only one) with a real per-record run-log for idempotency, instead of the `wasChanged()` heuristic.
- **Notifications on conversion** — a `RecordConvertedNotification`/`TransformationTriggeredNotification` pair (owner told their record was converted; actor told their edit triggered an automatic run) was designed and briefly implemented, then deliberately shelved before commit to keep this feature's diff focused — not present in the current codebase.

## 9. Reference

- `docs/guides/en/conversion-guide.md` — plain-language, user-facing guide.
- `docs/guides/relationships-implementation.md` — the relationship linking mechanics this feature builds on (`RelationshipService::link()`'s one-to-one replace behavior in particular).
