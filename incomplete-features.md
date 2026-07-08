# Cubrel CRM — Incomplete / Half-Built Features

> Companion to `FEATURES.md`, whose closing "Summary of Incomplete / Half-Built Areas" table is the short-form index. This file expands every one of those rows into a full entry (what it is, where it lives, why it matters, what a fix would involve), and adds items found by a fresh codebase scan that were never surfaced in `FEATURES.md` at all — either missed originally, or introduced as a side effect of later feature work (relationships, line items, bulk update).
>
> Ordered by priority: **Critical** (security exposure or unrecoverable data loss) → **High** (real functional gap, no workaround) → **Medium** (user-facing correctness/trust, narrower blast radius) → **Low** (dead code, cleanup, no runtime impact). Each entry notes whether it was previously in `FEATURES.md`'s table or newly found in this scan.
>
> **Corrections**: two items previously in this list have been removed after user clarification confirmed they're deliberate V1 scope decisions, not accidental gaps:
> - **Module permissions (RBAC)** — `modules` doesn't have `can_view`/`can_create`/`can_edit`/`can_delete` columns (that was stale documentation); granular RBAC is intentionally deferred to V2. See `FEATURES.md` §11 for the current binary-visibility model.
> - **Audit trail: record restore** — hard delete is intentional for now ("what is deleted is gone, and I'm fine with it"). A real fix isn't a quick snapshot-and-restore — it's a full **Bin system** (soft delete, a retention window of N days, auto-purge after) planned as a complete V2 feature, matching the `// TODO: Offer recovering deleted records (Bin system)` comment already sitting in `RecordController.php`. See `[[project_record_restore_roadmap]]` in memory, updated to reflect this.
>
> **Resolved**: "No way to delete a field, at any layer" — implemented (route, `FieldsManagerController::destroy()`, layout cleanup, records-using warning, render-time existence guards across every field-rendering surface). See `FEATURES.md`'s Field Manager section and `tests/Feature/Modules/FieldDeletionTest.php`.
>
> **Resolved**: "Audit trail: 'all matching' bulk edits aren't traceable per-record" — a new `audit_log_affected_records` join table (`audit_log_id`, `record_id`, indexed) now records every affected record for **both** bulk-selection modes (`explicit` and `all_matching`), not just explicit. `AuditService::log()` takes the affected IDs as a fifth argument and writes them there instead of inline in `diff` — a plain JSON array was the wrong fix for `all_matching` (unbounded size, no indexable containment check); the join table gives an indexed per-record lookup instead. See `docs/dev/audit-trail-implementation.md` §4.2/§4.2.1 and `tests/Feature/Audit/BulkOperationsAuditTest.php`.

---

## Medium

### 1. Relationship deletion only cleans up layouts on one side
*(newly found)*

- **Where**: `app/Models/Relationship.php::cleanupRelationshipPanels(string $module_id)`, called from `RelationshipManagerController::destroy()`
- **What**: When a relationship is deleted, `cleanupRelationshipPanels($module_id)` strips it from `related`-type layouts — but only for `$module_id`, the module whose settings page the delete request came from. A relationship normally has a panel configured on **both** sides' `related` layouts (e.g. Accounts shows a "Deals" panel, Deals shows an "Account" panel). Deleting the relationship from Accounts' page only cleans up Accounts' layout; Deals' layout is left referencing a relationship that no longer exists.
- **Impact**: A stale panel reference can linger in the other module's `related` layout after deletion — likely renders as an empty/broken panel, or is silently skipped by `PanelList.vue` if it can't resolve the relationship (needs verification either way — not yet confirmed which). Narrow blast radius: only affects custom relationships, only reachable through admin-only Settings.
- **Fix**: `cleanupRelationshipPanels` should run for both `left_module` and `right_module`, not just the requesting module. Found while implementing the `many-to-one` relationship type (see `docs/dev/relationships-implementation.md` §7) — not introduced by that work, just adjacent and newly noticed.

---

## Low (cleanup / dead code / no runtime impact)

### 2. `Dashboard::scopeGlobal()` / `scopeForUser()` — dead code
*(previously flagged)*

- **Where**: `app/Models/Dashboard.php`
- **What**: Two Eloquent query scopes that reference an `owner_id` column the `dashboards` table doesn't have (the real migration, `2026_05_04_121726_create_dashboards_table.php`, only has `user_id`). Neither scope is called anywhere in the app — actual per-user scoping is a plain `Dashboard::where('user_id', $user->id)->first()` inline in `DashboardController@index`.
- **Impact**: None currently — they're simply never invoked, so the wrong column reference never executes. Purely a maintenance hazard: if someone calls `Dashboard::scopeGlobal()`/`scopeForUser()` in the future expecting it to work (the names read as if they're the real scoping mechanism), it will throw a SQL error on the missing column.
- **Fix**: Delete both scopes, or rewrite them to use `user_id` and actually call them from `DashboardController@index` instead of the inline `where()`.

### 3. IP whitelist — removed entirely
*(previously flagged)*

- **Where**: N/A — nothing exists anymore
- **What**: `ip_whitelists` table and `IpWhitelist` model were added in commit `96dd5e8`, then deleted two days later in `eab2507` ("cleaned up migration to avoid 500 bugs"). No table, model, migration, or string reference to `ip_whitelist(s)` exists anywhere in the current codebase.
- **Impact**: None today — it's just absent. Flagged here (as in `FEATURES.md`) specifically so nobody mistakes this for "not started" when it's actually "built, then reverted."
- **Fix**: If wanted again, treat as new feature work; check `git show eab2507` for why it caused 500s before repeating the same approach.

### 4. Dead code: `RelationshipService::enforceCardinality()` is never called
*(newly found)*

- **Where**: `app/Services/Relationships/RelationshipService.php:72`
- **What**: A fully-implemented method that `throw`s a `RuntimeException` on a duplicate link for `one-to-one`/`one-to-many`/`many-to-many`. Confirmed via repo-wide grep — nothing calls it. The actual cardinality behavior lives inline in `link()`'s own `switch` statement, which for `one-to-many` **silently re-parents** (deletes the old link, inserts the new one) rather than throwing.
- **Impact**: None functionally (dead code doesn't execute), but it's actively misleading to read — a future maintainer could reasonably assume this is the enforcement mechanism, "fix" or extend it, and be confused when linking behavior doesn't change.
- **Fix**: Delete it, or if the throwing behavior is actually preferred over silent re-parenting for some case, wire it in deliberately and decide which behavior should win where.

### 5. Dead code: `RelationshipService::getRelationshipBetween()` is never called
*(newly found)*

- **Where**: `app/Services/Relationships/RelationshipService.php:227`
- **What**: Looks up the `Relationship` row(s) between two module slugs (checking both directions), optionally filtered by `type`. Confirmed via grep — no controller, service, or Vue component calls it.
- **Impact**: None functionally. Same maintenance-hazard category as #4.
- **Fix**: Delete unless there's a near-term use for it (e.g. it might be a natural fit for validating "does a relationship already exist between these two modules" during relationship creation — currently `RelationshipManagerController::store()` does its own inline duplicate query instead of reusing this).

### 6. `config/default_relationship_types.php` is dead and out of sync
*(newly found)*

- **Where**: `config/default_relationship_types.php`, `app/Http/Controllers/RelationshipManagerController::create()`, `resources/js/Pages/Settings/Relationships/Create.vue`
- **What**: A second, separate list of relationship types (`['one-to-one', 'one-to-many', 'many-to-many']`) — missing `many-to-one` entirely. It's read by `create()` and passed to `Create.vue` as a `types` prop, but that prop is never referenced anywhere in the component's template or script (confirmed via grep) — the actual dropdown is driven by the `typeList` prop, sourced from the `relationship_type_list` `DropdownList` row (`config/dropdown_lists.php`), which *was* updated to include `many-to-one`.
- **Impact**: None today (the config is simply unused), but it's a trap: it looks like a second source of truth for relationship types, and if anyone starts using it (or "fixes" it by adding `many-to-one` there too, assuming it matters), there'd be two lists to keep in sync for no reason.
- **Fix**: Delete `config/default_relationship_types.php` and the `types` prop it feeds, or repurpose `Create.vue` to actually use it instead of `typeList` (there'd need to be a reason to prefer one over the other — right now there isn't).

### 7. Two-factor authentication — dead, unused columns
*(previously flagged, downgraded)*

- **Where**: `users` table (`two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `failed_login_attempts`, `locked_until`)
- **What**: 2FA isn't offered in this version by design. Confirmed these five columns are never read or written anywhere in `app/` (the only `locked_until` hits are an unrelated column on the `modules` table, used for the Module Builder's draft-editing lock). All nullable/defaulted — inert, not a risk.
- **Impact**: None. Purely cosmetic: a future dev could assume 2FA is supported and be wrong.
- **Fix**: No urgency. A one-line migration to drop the columns is optional cleanup, not a requirement.

---

## Not a Bug — Left for V2

> Reclassified out of the High-priority list above: these are real, known gaps, not doc-mismatches or dead code — but a proper fix is a larger V2 feature, not a patch to what exists today, so they're tracked here rather than as open bugs.

### Line items are orphaned, not cascade-deleted, when their parent record is deleted

- **Where**: `app/Models/Modules/LineItem.php`, `app/Http/Controllers/RecordController.php::destroy()`/`destroyMany()`
- **What**: `LineItem` rows reference their parent via `parent_type`/`parent_id` (polymorphic-by-convention, no real FK). Deleting a parent record (an Order, Invoice, Quote, or any custom has-line-items module record) — whether single or bulk — does not delete its associated `LineItem` rows. No model event, observer, or FK cascade handles this.
- **Impact**: Orphaned `LineItem` rows accumulate in the `line_items` table indefinitely, invisible in the UI (nothing queries for a parent-less line item) but never cleaned up. Checks that query `LineItem::where('parent_type', $slug)->exists()` (e.g. determining whether a module "has line items in use") would see these ghost rows and could report `true` for a module that, from a user's perspective, has no visible line items left anywhere.
- **Why it's left for V2, not fixed now**: the real fix is entangled with the planned Bin system (soft delete, retention window, auto-purge — see [[project_record_restore_roadmap]]). Cascade-hard-deleting line items the moment a parent is deleted would conflict with a future soft-delete/restore window — a record restored from the Bin would come back with no line items. Worth solving once, as part of that feature, not twice.

### Active session listing / revoke-a-device — missing entirely

- **Where**: N/A
- **What**: No UI or backend endpoint lists a user's other active sessions, and no "log out other devices" action exists. `sessions.user_id` is indexed but not unique — the same user can be logged in on unlimited devices with no visibility into or control over that from either the user's or an admin's side.
- **Impact**: A user who suspects unauthorized access to their account has no self-service way to see or kill other sessions; neither does an admin on their behalf.
- **Why it's left for V2, not fixed now**: this is new feature work (a sessions list scoped to `auth()->id()`, or any user for admins, reading the `sessions` table, with a per-row "revoke" action), not a small patch — same scale of effort as the other V2-deferred items above (granular RBAC, the Bin system).

### Admin-configurable idle session window

- **Where**: `docs/guides/en/session-timeout-guide.md`, `.env` `SESSION_LIFETIME` (reality)
- **What**: An admin setting to shorten/lengthen the idle timeout (30 min – 24h). No such setting exists in code — the 8-hour idle window is a static `.env` value (`SESSION_LIFETIME=480`), not configurable from Settings. `docs/dev/419-session-recovery.md` §9 lists this as "planned follow-up, not built."
- **Impact**: None currently beyond the missing capability itself — the user guide previously described this as already shipped (a real doc/code mismatch), which has now been corrected in `docs/guides/en/session-timeout-guide.md` to stop claiming it exists.
- **Why it's left for V2, not fixed now**: Laravel's session lifetime is normally a boot-time config value, not trivially per-request-dynamic, so making it admin-configurable needs real design (a `SettingValue` plus wiring into whatever reads `SESSION_LIFETIME` at runtime) rather than a quick patch.

### Admin toggle to hide "remember me"

- **Where**: `docs/guides/en/session-timeout-guide.md`, `resources/js/Pages/Login.vue` (reality)
- **What**: An admin toggle to remove the "Keep me signed in" checkbox from the login screen entirely. No such setting exists — the checkbox is unconditionally rendered for every user.
- **Impact**: Same as above — the guide previously overclaimed this; now corrected.
- **Why it's left for V2, not fixed now**: needs a new `SettingValue` (e.g. `allow_remember_me`) plus gating the checkbox's rendering in `Login.vue` — small in isolation, but grouped with the idle-window setting above since both come from the same guide correction and the same "admin session controls" surface, worth designing together rather than piecemeal.

---

## Not a gap (fixed during a previous pass)

`FEATURES.md` had six references to `docs/419-session-recovery.md`, `docs/session-timeout-guide.md`, `docs/audit-trail-implementation.md`, and `docs/audit-trail-guide.md` using the old flat `docs/*.md` paths, left stale after the `docs/guides/` + `docs/dev/` split. Corrected in place — listed here only so it's not mistaken for something still broken.

---

## Reference

- `FEATURES.md` — the short-form summary table this file expands on; §11 has the corrected module-visibility model; the Field Manager section documents the now-resolved field-deletion feature; §16 documents the now-resolved audit-trail join table.
- `docs/dev/relationships-implementation.md` §7 — source of #1.
- `docs/dev/audit-trail-implementation.md` §4.2/§4.2.1 — technical detail for the resolved "all matching" bulk-edit traceability fix.
- `docs/dev/419-session-recovery.md` §9 — source of the "planned follow-up" confirmation for the idle-window and remember-me items in "Not a Bug — Left for V2".
- `tests/Feature/Modules/FieldDeletionTest.php` — coverage for the resolved field-deletion feature.
- `tests/Feature/Audit/BulkOperationsAuditTest.php`, `tests/Feature/Audit/RecordHistoryControllerTest.php` — coverage for the resolved audit-trail join table.
