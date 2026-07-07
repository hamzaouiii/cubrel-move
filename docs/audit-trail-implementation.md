# Audit Trail & Impersonation Sessions

Branch: `feat/audit-trail`

## 1. Problem

Two related accountability gaps existed before this work:

- `RecordController::update()` does a bare `$record->fill($request->except(...))->save()` — no change tracking anywhere. Nothing recorded who changed what, when, on any record in any module.
- `UserController::impersonate()`/`leaveImpersonation()` (`app/Http/Controllers/UserController.php:202-230`, `261-280`) both call `Auth::login()`, which fully swaps the authenticated identity. While impersonating, `auth()->id()` resolves to the *target* user, not the real actor — any naive "current user" logging would misattribute every action taken during impersonation to the impersonated user, with zero trace that root was actually driving. Impersonation itself (who logged in as whom, for how long) also wasn't recorded anywhere at all.

This was raised directly out of `docs/419-session-recovery.md` §9's finding (impersonation swapping the session mid-tab) and its listed follow-up items.

## 2. Two separate tables, deliberately not one

- **`audit_logs`** — record-level events (created/updated/deleted/linked/unlinked), one row per event, append-only.
- **`impersonation_sessions`** — impersonation sessions themselves (who impersonated whom, IP, start/end, duration), a genuinely different shape: mutable (a row is created on impersonate and *updated* with `ended_at` on leave), not an immutable append-only log.

Originally scoped as one shared table (`audit_logs` alone, with `impersonator_id` covering "who did what as who"). Mid-implementation the requirement expanded to explicit session tracking (duration, IP) — forcing that into `audit_logs`' single-timestamp, single-action-per-row shape would have meant fragile query-time pairing of separate "started"/"ended" rows to compute duration. A second, purpose-built table was simpler and more correct than one table serving two structurally different jobs.

### 2.1 `audit_logs` schema

```php
Schema::create('audit_logs', function (Blueprint $table) {
    $table->id();
    $table->string('module_slug')->nullable()->index();   // null for non-record events
    $table->string('record_id')->nullable()->index();      // uuid string, no FK (target table varies per module_slug)
    $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();          // acting/session identity
    $table->foreignUuid('impersonator_id')->nullable()->constrained('users')->nullOnDelete();  // real actor, set only while impersonating
    $table->string('action');       // created | updated | deleted | linked | unlinked
    $table->json('diff')->nullable(); // see §5.3 for why this isn't named 'changes'
    $table->timestamp('created_at')->useCurrent();
    $table->index(['module_slug', 'record_id']);
});
```

`module_slug` + `record_id` (plain strings, no FK constraint) follows the existing polymorphic-reference convention already used by `PdfTemplate::where('module_slug', ...)` in this codebase, rather than Eloquent's `morphs()`/FQCN convention — kept consistent with what was already here.

Deliberately schema-generic (not `impersonation_log`, not Audit-specific naming) so a planned future **Activities** feature can read/write the same table with a broader `action` vocabulary (e.g. `email.sent`, `note.added`) and reuse `AuditService::log()` as-is, without a rename.

### 2.2 `impersonation_sessions` schema

```php
Schema::create('impersonation_sessions', function (Blueprint $table) {
    $table->id();
    $table->foreignUuid('impersonator_id')->constrained('users')->cascadeOnDelete();
    $table->foreignUuid('target_user_id')->constrained('users')->cascadeOnDelete();
    $table->string('ip_address')->nullable();
    $table->timestamp('started_at');
    $table->timestamp('ended_at')->nullable(); // null = still ongoing
    $table->index(['impersonator_id', 'started_at']);
    $table->index(['target_user_id', 'started_at']);
});
```

## 3. Actor resolution and transparency

`AuditService::log()` (`app/Services/Audit/AuditService.php`) auto-resolves the acting identity on every call:

```php
'user_id' => auth()->id(),
'impersonator_id' => Session::get('impersonator_id'),
```

`Session::get('impersonator_id')` is the same session key `HandleInertiaRequests` already reads to share `auth.impersonating`/`auth.impersonator` with the frontend — no new signal was invented.

**Transparency, not masking, was a deliberate mid-planning reversal.** An earlier draft of this plan proposed hiding the real impersonator's identity from non-root/non-admin viewers (a Gate, an admin-only "reveal" setting). The user explicitly reversed that: the impersonator's identity is always included in `AuditLog::toDisplayArray()` whenever `impersonator_id` is set, unconditionally, to anyone who can see that row at all. There is no Gate, no visibility setting, no masking logic anywhere in this feature for regular audit rows.

### 3.1 Actor-resolution ordering — load-bearing detail

`impersonate()` (`UserController.php:202-230`) calls `AuditService`-adjacent session-row creation *after* both `Session::put('impersonator_id', ...)` and `Auth::login($user)`, so by that point `auth()->id()` is already the target and the session key already set — no special-casing needed.

`leaveImpersonation()` is the asymmetric case. If the impersonation-session close-out ran *after* `Auth::login($originalUser)`/`session()->forget(...)` (mirroring the "call at the end" pattern used for the start), `auth()->id()` would already be root and the session key already gone — producing a session row that's silently unattributable. The fix: capture `$impersonatorId = session('impersonator_id')` and close out the session row **before** any of that mutation runs:

```php
$impersonatorId = session('impersonator_id');
if (!$impersonatorId) { abort(403); }

if ($sessionId = session('impersonation_session_id')) {
    ImpersonationSession::whereKey($sessionId)->update(['ended_at' => now()]);
}

$originalUser = User::findOrFail($impersonatorId);
Auth::login($originalUser);
session()->forget(['impersonator_id', 'impersonation_session_id']);
```

## 4. Write paths

### 4.1 `AuditObserver` — the primary hook

Registered from `BaseModule::booted()` (`app/Models/BaseModule.php:52-62`), not `AppServiceProvider::boot()`:

```php
protected static function booted(): void
{
    static::creating(function (self $model) { ... });
    static::bootAuditObserver();
}

protected static function bootAuditObserver(): void
{
    static::observe(\App\Observers\AuditObserver::class);
}
```

**Why not `AppServiceProvider`:** Laravel's Eloquent event dispatcher keys observer bindings to the literal class name passed to `observe()`. A single `BaseModule::observe(AuditObserver::class)` call in `AppServiceProvider::boot()` would bind against `App\Models\BaseModule` — but `Deal`, `Contact`, etc. fire `eloquent.created: App\Models\Modules\Deal`, which a listener registered under the parent class never receives. Because `booted()` runs once per *concrete* subclass (Eloquent tracks a `$booted` map per class), `static::` inside it late-binds correctly, and `static::observe(...)` registers against whichever concrete module class is actually booting.

`User` overrides `booted()` and deliberately does **not** call `parent::booted()` (calling it crashes `User::create()` — `users` has no `owner_id` column). It therefore needed its own explicit `static::bootAuditObserver()` call, or user-level audit events would silently never fire.

`AuditObserver` (`app/Observers/AuditObserver.php`):
- `created()` — logs with `null` diff (nothing to diff against on first creation).
- `updated()` — builds `{field: {old, new}}` from `$model->getChanges()`, excluding `updated_at` and any field whose `Field` row has `is_calculated = true` (§4.4). Skips logging entirely if the resulting diff is empty (e.g. a bare `touch()`).
- `deleted()` — logs a `record_label` snapshot (§4.5) since the record won't be queryable afterward.

### 4.2 `RecordController` bulk operations — explicit calls, not the observer

`updateMany()`/`destroyMany()` (`app/Http/Controllers/RecordController.php`) use query-builder bulk operations (`whereIn(...)->update(...)`, `chunkById(...)->delete()`), which **do not fire Eloquent model events at all** — the observer can never see these. Both selection modes (`all_matching`, `explicit`) get an explicit `AuditService::log(...)` call, logging **one row per batch** rather than one per affected record, to avoid flooding the table on a large bulk edit:

```php
AuditService::log('updated', $moduleModel->slug, null, [
    'mode' => 'explicit',
    'field' => $field_name,
    'value' => $newValue,
    'count' => $updatedCount,
    'affected_ids' => $selectedIds,
]);
```

`affected_ids` is only captured in **explicit**-selection mode (the user picked specific records via checkboxes) — **not** in `all_matching` mode (a "select everything matching this filter" bulk action without an explicit id list), to avoid a potentially huge JSON array for large filtered operations. This is a deliberate, documented gap: `all_matching` bulk edits/deletes only ever show up as one summary row in the global Audit Trail; they can't be attributed back to any individual record's own history (see §7).

For `destroyMany()`'s explicit-list branch specifically, record labels are captured **before** the delete runs, same reasoning as `AuditObserver::deleted()`:

```php
$recordLabels = $modelClass::whereIn('id', $selectedIds)->pluck('name', 'id');
$deleted = $modelClass::whereIn('id', $selectedIds)->delete();
AuditService::log('deleted', $moduleModel->slug, null, [
    'mode' => 'explicit', 'count' => $deleted,
    'affected_ids' => $selectedIds, 'record_labels' => $recordLabels,
]);
```

### 4.3 `RelationshipService::link()`/`unlink()` — logged on both sides

A relationship connects two records, potentially in two different modules (e.g. an Account and a Contact). `logLinkChange()` (`app/Services/Relationships/RelationshipService.php`) logs **two** audit rows per link/unlink action — one scoped to each side — so opening *either* record's own history shows the connection, regardless of which side the action was performed from:

```php
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
```

Called from `link()` after the `RelationshipLink::create(...)` transaction commits, and from `unlink()` after the raw `DB::table('relationship_links')->delete()` call. Both `link()`/`unlink()` are the single choke point regardless of caller (`BaseModule::link()`/`unlinkRelation()`, used by `RelationshipLinkController`, or any future caller), so hooking here rather than in the controller covers all current and future call sites.

**Verified deliberately:** `RelationshipLink extends Model` directly, **not** `BaseModule` — so its own `create()` call does not also trigger `AuditObserver::created()`. If it did extend `BaseModule`, `getModuleSlug()` would attempt to resolve a `Module` row for `App\Models\RelationshipLink` (none exists) and crash the same way §5.1's bug did.

### 4.4 Auto-calculated fields excluded via an `is_calculated` Field flag

Originally a hardcoded constant (`AUTO_CALCULATED_FIELDS = ['total', 'subtotal', 'tax_amount', 'discount_amount']`), later replaced with a proper `is_calculated` boolean column on `fields` (added directly to the base `create_fields_table` migration, plus `Field::$fillable`/`$casts` and both `Module::allFields()` `select()` lists, which otherwise silently drop any column not explicitly listed there). `updated()` now derives the exclusion set per-save from the model's own module:

```php
$calculatedFields = $module->allFields()
    ->where('is_calculated', true)
    ->pluck('name')
    ->all();

$changes = collect($model->getChanges())
    ->except(array_merge(['updated_at'], $calculatedFields))
    ...
```

The flag is set on the two config-driven sources that actually back these fields' `Field` rows — `config/default_line_item_fields.php` (the `is_default_for_line_items = true` rows merged into any has-line-items *parent* module's `allFields()`, e.g. an `Order`'s own `total`/`subtotal`/`tax_amount`/`discount_amount` columns) and `config/stock_fields.php`'s `line_items` section (the `LineItem` model's *own* per-row equivalents — `LineItem` also extends `BaseModule` and is audited the same way). Both needed the flag for the exclusion to hold regardless of which side actually saved. This is a strict improvement over the hardcoded list: any field an admin marks calculated via the Fields Manager is now excluded automatically, without an app code change.

### 4.5 `record` type fields resolve to labels, not raw IDs

Fields of type `record` (e.g. `owner_id` → `users`) store a related record's id as their value — meaningless to a viewer on its own. `AuditObserver::buildFieldChange()` detects this via the model's own `moduleDefinition()->allFields()` lookup and, when the changed field is `type === 'record'`, resolves both the old and new id to that related record's display label (`name ?? number ?? id`), storing them alongside the raw ids:

```php
$diff['old_label'] = $this->resolveRecordLabel($field->related_module, $old);
$diff['new_label'] = $this->resolveRecordLabel($field->related_module, $new);
```

Resolved **at write time**, not read time — same "snapshot now, don't rely on it still existing/being named the same later" reasoning as the deleted-record label (§4.1). The frontend's `diffValue()` helper (`HistoryModal.vue`) prefers `*_label` when present, falling back to `formatValue()` otherwise.

## 5. Bugs found during implementation

Documented in detail because each reflects a real, non-obvious mechanism worth remembering if this code needs to change later.

### 5.1 `getModuleSlug()`/`moduleDefinition()` crash for non-admins

**Symptom (caught before shipping, via `php artisan tinker`):** the very first `User::save()` performed as a non-admin threw `TypeError: BaseModule::getModuleSlug(): Return value must be of type string, null returned`.

**Root cause:** `AdminOnlyModuleScope` (`app/Scopes/AdminOnlyModuleScope.php`) is a global scope on `Module` that hides the `users`/`settings` module rows from any non-admin (`Auth::check() && Auth::user()->isAdmin()` gate). `getModuleSlug()`/`moduleDefinition()` queried `Module` without bypassing this scope — so as soon as `AuditObserver` started calling `getModuleSlug()` on every model save (including `User`), any **non-admin editing their own profile** would silently have the `users` module row filtered out of the query, `value('slug')` returns `null`, and the non-nullable `: string` return type throws.

This is a **latent bug in existing code**, not something introduced by this feature — it was simply never exercised before, since nothing previously called `getModuleSlug()`/`moduleDefinition()` from a path a non-admin could realistically hit on their own record.

**Fix:** both methods now bypass the scope explicitly:

```php
Module::withoutGlobalScope(\App\Scopes\AdminOnlyModuleScope::class)
    ->where('model_class', static::class)->value('slug');
```

Flagged to the user before making this change, given `BaseModule.php`'s blast radius across the entire module system (see project memory `feedback_core_file_changes`).

### 5.2 `ImpersonationSession::durationInSeconds()` sign bug

**Symptom:** a manually-verified 1-second session reported `duration_seconds: -1`.

**Root cause:** `($this->ended_at ?? now())->diffInSeconds($this->started_at)` — Carbon's `diffInSeconds()` sign convention depends on call-order/version defaults in a way that isn't self-evident from the code.

**Fix:** replaced with unambiguous plain timestamp subtraction:

```php
public function durationInSeconds(): int
{
    return ($this->ended_at ?? now())->getTimestamp() - $this->started_at->getTimestamp();
}
```

### 5.3 The `changes` column silently always serialized as `[]`

The most subtle bug in this feature. `AuditLog`'s diff column was originally named `changes`. Reads always came back empty (`"changes": []`) despite the raw DB row holding real data (confirmed via `getRawOriginal('changes')`), even though **writes** worked correctly.

**Root cause:** Eloquent's base `Model` class already declares a `protected $changes` property internally, populated by `syncChanges()`/used by `getChanges()` for dirty-tracking. Since it's a real declared PHP property (not a magic/undefined one), `$this->changes` resolves to that internal property directly — PHP only invokes `__get()`/attribute casting for properties that *aren't* already declared. Writes worked because `AuditLog::create(['changes' => ...])` goes through `setAttribute()`, which always writes into `$this->attributes['changes']` (an array key), never colliding with the real property. Reads via `$this->changes`, however, hit the declared property first, which — on a freshly-hydrated model with no in-request modifications — is simply empty.

**Fix:** renamed the column to `diff` everywhere (migration, model, `AuditService`), which isn't a reserved Eloquent property name. The public/JSON shape exposed to the frontend is unaffected — `toDisplayArray()` still returns the key as `'changes' => $this->getAttribute('diff')`, so no Vue changes were needed for this specific fix. The already-migrated dev table was rolled back and re-migrated (harmless — only a handful of test rows existed at that point).

### 5.4 N+1 in `AuditLogController` (two layers, found via Laravel Debugbar)

**Symptom:** Debugbar showed 37 queries for one page load, many of them `select * from dropdown_lists where id in (null)`.

**Root cause #1:** `fields_by_module` was built by calling `$module->allFields()` in a loop over every active module — `allFields()` re-queries `Field` **and** re-eager-loads its `dropdown_list` relation independently for every module, i.e. roughly 2 queries × N active modules, none of which was needed here (only `{name, label}` pairs were actually used).

**Fix:** one `Field::query()->whereIn('module_id', $modules->pluck('id'))->orWhere('is_global', true)->get(...)` for every module at once, grouped in PHP afterward.

**Root cause #2, found while verifying the fix:** the replacement query selected `['module_id', 'name', 'label', 'is_global']` — omitting `id`. `Illuminate\Database\Eloquent\Collection::merge()` (unlike the base `Collection`) dedupes by primary key via `getDictionary()`/`getKey()`. With `id` unselected, every model's key is `null`, so merging a module's own fields with the global fields collapsed **all of them into a single entry** (whichever happened to be assigned last to `$dictionary[null]`) — silently wrong grouping, not a query-count problem, and easy to miss since the code ran without error.

**Fix:** added `id` back to the `select()`. Verified with a standalone script: `DB::getQueryLog()` count dropped from ~1+2N to 2, and `contacts`' field count went from a broken `1` (only `owner_id` survived the collapse) to the correct `11` (6 module-specific + 5 global).

Also fixed the same eager-loading gap in `AuditLogController::index()`, `RecordHistoryController::index()`, and `ImpersonationSessionController::index()` themselves — `toDisplayArray()` lazily accesses `$this->user`/`$this->impersonator` per row; without `->with(['user', 'impersonator'])` on the base query, each row triggered its own `select * from users where id = ?` (seen directly in a user-supplied Debugbar screenshot: the same query running 3 times in one request). Eager-loading collapsed this to one `where id in (...)` query per relation regardless of row count.

### 5.5 Demo-data seeding logged real audit rows attributed to no one

**Symptom:** after running onboarding's demo-data seed, the global Audit Trail showed dozens of `linked`/`unlinked` entries with actor "Unknown" — but none of the corresponding record `created` events were logged at all, an inconsistency the user caught by inspection rather than a test failure.

**Root cause:** `DatabaseSeeder` uses Laravel's `WithoutModelEvents` trait, which wraps every nested `$this->call(...)` seeder in `Model::withoutEvents()`. That's why `DevSeeder`'s `Account::factory(10)->create()` etc. never reach `AuditObserver` — Eloquent's event dispatcher is suppressed entirely for the duration. But `RelationshipPopulationSeeder` calls `RelationshipService::link()`, which calls `AuditService::log()` **directly** — a plain static method call, not an Eloquent event. `Model::withoutEvents()` only suppresses the event dispatcher; it has no effect on a direct function call. So seeded links sailed straight through to `AuditLog::create()`, and since there's no authenticated user in a console/seeder context, `user_id` ended up `null` → "Unknown" in the UI. The same gap applies to any other direct (non-model-event) caller of `AuditService::log()`, including `RecordController`'s bulk operations, and would resurface identically the moment a REST API or queued job calls one of these paths without a resolvable user.

**Fix:** moved the guard into `AuditService::log()` itself rather than patching each caller — it now no-ops entirely when `auth()->check()` is false:

```php
public static function log(string $action, ?string $moduleSlug, ?string $recordId, ?array $changes = null): void
{
    if (! auth()->check()) {
        return;
    }
    AuditLog::create([...]);
}
```

This makes "no real actor → no audit row" an enforced invariant of the write path itself, covering every current caller (`AuditObserver`, bulk operations, relationship link/unlink) and any future one (a REST API endpoint, a queued job) uniformly, rather than relying on each caller to separately remember to guard against a missing actor. 768 pre-existing orphaned rows from the demo-seed run were deleted from the dev database as part of this fix (`AuditLog::whereNull('user_id')->delete()`) — they were 100% seed noise, not real activity.

## 6. Frontend

### 6.1 Two surfaces, by design

- **Global, admin-gated log** — `Settings/AuditTrail/Index.vue` (`app/Http/Controllers/AuditLogController.php`), filterable by module/user/action/date range, paginated with the same hand-rolled `meta` shape `PdfTemplatesController::index` already uses.
- **Per-record modal** — `HistoryModal.vue` (`app/Http/Controllers/RecordHistoryController.php`), opened via a "View History" item added to the record page's existing action dropdown (`Modules/Record.vue`), rather than as a third tab — the tab approach was tried first and replaced on request, since it competed for space with Overview/Related and duplicated navigation the action menu already provides.

Per-record history visibility has no additional ACL beyond being logged in, because **no such ACL exists on records themselves yet** in this codebase — confirmed by reading `RecordController`, which has no ownership/visibility restriction on `show()` beyond the outer `auth`+`onboarded` middleware. If record-level visibility restrictions land later, this endpoint's gating should move alongside them.

### 6.2 Field-aware rendering, not raw values

Both surfaces resolve raw stored values into what a user actually recognizes, rather than showing database internals:

- **Field names → labels**: `AuditLogController::index()` builds a `fields_by_module` map (`{name, label}` per module); `fieldLabel()` in `Index.vue` and `HistoryModal.vue` resolve DB column names (`email`) to their translation-key label (`modules.contacts.fields.email`), which `$t()` renders in the active locale.
- **Dropdown values → option labels**: `formatValue()`/`dropdownLabel()` look up the field's own `dropdown_list.values` (already present on the `fields` prop via `Module::allFields()`'s `with('dropdown_list')`) and match the raw stored value against it.
- **`record` type fields → related record names**: prefers the server-resolved `*_label` (§4.5) over the raw id.
- **Dates**: formatted via `@/utils/datetime`'s `formatDate`/`formatDateTime`, which respects the app's configured `date_format`/`datetime_format`/`timezone` settings — the same utility `FiledTypes/DateTime.vue` uses, not a hardcoded format.
- **Bulk / link / delete entries**: each has a distinct payload shape (`{mode, count, field, value, affected_ids}` / `{relationship, related_module, related_label}` / `{record_label}`) and gets its own rendering branch (`isBulkChange()`, `isLinkChange()`, the `deleted` check) rather than being forced through the generic per-field `{old, new}` diff table.

### 6.3 Module badge coloring

Module badges (in the global list) use each row's own module color, following the same convention already established in `Record.vue` (`getRelatedColor()`/`module_color`): `use_individual_module_colors == "0"` falls back to the shared primary color for every badge; otherwise each badge gets its own module's color via a per-row inline `--module-color` CSS variable override (the existing SCSS already reads `var(--module-color)`, so no new styling rules were needed for this, just the per-row binding).

### 6.4 Filters use the app's real field components, not native HTML inputs

Both list pages' filter bars use `FiledTypes/Select.vue` (searchable by default, `dropdown_list`-shaped options) and `FiledTypes/DateTime.vue` (`type="date"`), replacing native `<select>`/`<input type="date">`. `DateTime.vue` emits a JS `Date` object on selection, converted back to a plain `YYYY-MM-DD` string via a small `toDateParam()` helper before being sent as a query param, since the backend filters expect that format either way. Each filter has its own `<label>` above it (matching the pattern already used in `FilterZone.vue`), since a bare `DateTime` field's `placeholder` disappears once a value is picked, leaving no indication of what the field represents.

## 7. Known limitations / explicit non-goals

- **`all_matching` bulk edits/deletes aren't traceable to individual records.** Only the `explicit`-selection bulk mode captures `affected_ids` (§4.2); records affected by a filtered "select everything matching" bulk action only show up in the global Audit Trail's batch summary row, never inside their own per-record history.
- **No restore-from-audit feature.** `deleted()` captures a `record_label` snapshot, nothing else — enough to know *what* was deleted, not enough to bring it back. See `project_record_restore_roadmap` memory note; would need a full attribute snapshot (and a plan for relationship/line-item data) to actually support restoration.
- **No per-record ACL.** Documented in §6.1 — the History modal's visibility is just "logged in," matching the record's own current (lack of) visibility restriction.
- **Impersonation session tracking is single-level.** `impersonator_id` in the session key is a single value, not a stack — nested impersonation (impersonating while already impersonating) isn't a supported concept here, consistent with `UserController::impersonate()` itself never having supported it either.

## 8. Planned follow-up (not built here)

- **Activities feature** — intended to read/write the same `audit_logs` table with a broader `action` vocabulary (see §2.1). If built, start there rather than introducing a parallel event log; a `visibility`/`is_user_facing` flag would be the natural way to let Activities hide noisy field-level `updated` diffs from its own feed while Audit still shows them.
- **Record restore** (§7) — full attribute snapshot capture at delete time, a restore endpoint, and a plan for relationship/line-item data that a bare attribute snapshot won't capture.
- **Per-record ACL** (§7) — if this lands, the History endpoint's gating needs to move alongside it.

## 9. Files touched

**Backend**
- `database/migrations/2026_07_07_120000_create_audit_logs_table.php` (new)
- `database/migrations/2026_07_07_120001_create_impersonation_sessions_table.php` (new)
- `app/Models/AuditLog.php` (new)
- `app/Models/ImpersonationSession.php` (new)
- `app/Services/Audit/AuditService.php` (new)
- `app/Observers/AuditObserver.php` (new)
- `app/Models/BaseModule.php` — observer registration, `getModuleSlug()`/`moduleDefinition()` scope-bypass fix
- `app/Models/User.php` — explicit `bootAuditObserver()` call
- `app/Http/Controllers/RecordController.php` — bulk `updateMany`/`destroyMany` audit calls
- `app/Http/Controllers/UserController.php` — impersonation session create/close-out
- `app/Services/Relationships/RelationshipService.php` — link/unlink audit calls
- `app/Http/Controllers/AuditLogController.php` (new)
- `app/Http/Controllers/ImpersonationSessionController.php` (new)
- `app/Http/Controllers/RecordHistoryController.php` (new)
- `routes/web.php` — `settings.audit-trail.*`, `settings.impersonation-sessions.*`, `modules.record.history`
- `config/settings.php` — new `audit` settings group
- `lang/en|de/{settings,globals,modules}.php` — new translation keys throughout
- `database/migrations/2026_01_08_163003_create_fields_table.php` — added `is_calculated` boolean (not run at edit time, per explicit instruction — applies whenever this migration next runs)
- `app/Models/Field.php` — `is_calculated` in `$fillable`/`$casts`/`$excludedFromForms`
- `app/Models/Module.php` — `allFields()`'s two `select()` lists now include `is_calculated` (otherwise silently un-hydrated)
- `config/default_line_item_fields.php`, `config/stock_fields.php` (`line_items` section) — `is_calculated => true` on `total`/`subtotal`/`tax_amount`/`discount_amount`

**Frontend**
- `resources/js/Pages/Settings/AuditTrail/Index.vue` (new)
- `resources/js/Pages/Settings/ImpersonationSessions/Index.vue` (new)
- `resources/js/Pages/Components/Modules/HistoryModal.vue` (new)
- `resources/js/Pages/Components/Settings/AuditTrail/ImpersonationBadge.vue` (new)
- `resources/js/Pages/Modules/Record.vue` — "View History" action-menu item
- `resources/scss/settings.scss`, `resources/scss/globals.scss` — new styling

**Tests** (`tests/Feature/Audit/`, all against the real `cubrel_testing` MySQL database, not sqlite)
- `AuditObserverTest.php` — created/updated/deleted logging, `is_calculated` exclusion (flag-driven, not name-driven), `record`-type label resolution, the `AdminOnlyModuleScope` regression (§5.1)
- `AuditServiceTest.php` — the no-authenticated-actor no-op guard (§5.5), directly
- `ImpersonationAuditTest.php` — session creation/close-out, actor-resolution transparency, the duration sign-bug regression (§5.2)
- `BulkOperationsAuditTest.php` — `updateMany`/`destroyMany`, both selection modes
- `RelationshipLinkAuditTest.php` — both-sides logging, plus the no-actor regression (§5.5)
- `AuditLogControllerTest.php` — admin gate, filtering, the `fields_by_module` collapse regression (§5.4)
- `RecordHistoryControllerTest.php` — per-record scoping, `affected_ids` matching
- `ImpersonationSessionControllerTest.php` — admin (not root-only) gate, filtering

## 10. Manual verification

Verified two ways: an automated test suite (`tests/Feature/Audit/`, 32 tests as of this writing) covering the behaviors and regressions described above, plus direct exercising via `php artisan tinker` during development for anything faster to check by hand than to write a full test for:

- Plain field update (non-impersonated) → single audit row, correct `user_id`, `impersonator_id` null.
- Impersonated field update → row has `user_id` = impersonated user, `impersonator_id` = root, fully visible (no masking) in both the record's History modal and the global Audit Trail.
- Impersonation start → `impersonation_sessions` row created with `ip_address`/`started_at`; leave → same row gets `ended_at`, duration computed correctly (positive, matching real elapsed wall-clock time).
- Deleting a record → audit row's `changes.record_label` matches the record's name at time of deletion.
- Bulk explicit-selection update/delete → exactly one batch-level audit row, not one per affected record; `affected_ids` present.
- Linking/unlinking two records → exactly two audit rows (one per side), each correctly cross-referencing the other's resolved label.
- `owner_id` (a `record`-type field) reassignment → diff includes both the raw ids and resolved `old_label`/`new_label` names.
- No authenticated actor (console/seeder context) → `AuditService::log()` no-ops entirely, verified both directly and via `RelationshipService::link()` after `auth()->logout()`.
- `AuditLogController`/`RecordHistoryController`/`ImpersonationSessionController` query counts confirmed via `DB::enableQueryLog()` after the eager-loading fix (§5.4) — collapsed from one query per row to one query per relation per page load.
