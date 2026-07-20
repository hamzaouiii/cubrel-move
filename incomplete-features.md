# Cubrel CRM — Incomplete / Half-Built Features

> Companion to `FEATURES.md`, whose closing "Summary of Incomplete / Half-Built Areas" table is the short-form index. This file expands each remaining row into a full entry (what it is, where it lives, why it matters, what a fix would involve). A working to-do list, not a changelog — resolved items are removed once fixed rather than kept around as history.
>
> Ordered by priority: **Critical** (security exposure or unrecoverable data loss) → **High** (real functional gap, no workaround) → **Medium** (user-facing correctness/trust, narrower blast radius) → **Low** (dead code, cleanup, no runtime impact). A closing **Not a Bug — Left for V2** section holds real, known gaps that are deliberately deferred rather than fixed now.

---

## Low (cleanup / dead code / no runtime impact)

### 1. `Dashboard::scopeGlobal()` / `scopeForUser()` — dead code

- **Where**: `app/Models/Dashboard.php`
- **What**: Two Eloquent query scopes that reference an `owner_id` column the `dashboards` table doesn't have (the real migration, `2026_05_04_121726_create_dashboards_table.php`, only has `user_id`). Neither scope is called anywhere in the app — actual per-user scoping is a plain `Dashboard::where('user_id', $user->id)->first()` inline in `DashboardController@index`.
- **Impact**: None currently — they're simply never invoked, so the wrong column reference never executes. Purely a maintenance hazard: if someone calls `Dashboard::scopeGlobal()`/`scopeForUser()` in the future expecting it to work (the names read as if they're the real scoping mechanism), it will throw a SQL error on the missing column.
- **Fix**: Delete both scopes, or rewrite them to use `user_id` and actually call them from `DashboardController@index` instead of the inline `where()`.

### 2. Dead code: `RelationshipService::enforceCardinality()` / `getRelationshipBetween()` are never called

- **Where**: `app/Services/Relationships/RelationshipService.php:72` and `:227`
- **What**: `enforceCardinality()` is a fully-implemented method that `throw`s a `RuntimeException` on a duplicate link for `one-to-one`/`one-to-many`/`many-to-many` — confirmed via repo-wide grep, nothing calls it. The actual cardinality behavior lives inline in `link()`'s own `switch` statement, which for `one-to-many` **silently re-parents** (deletes the old link, inserts the new one) rather than throwing. `getRelationshipBetween()` looks up the `Relationship` row(s) between two module slugs (checking both directions), optionally filtered by `type` — also never called from any controller, service, or Vue component.
- **Impact**: None functionally (dead code doesn't execute), but both are actively misleading to read — a future maintainer could reasonably assume `enforceCardinality()` is the enforcement mechanism and be confused when linking behavior doesn't change, or not realize `getRelationshipBetween()` already exists and write a duplicate inline query (which `RelationshipManagerController::store()` already does instead of reusing it).
- **Fix**: Delete both, or wire `enforceCardinality()` in deliberately if the throwing behavior is actually preferred over silent re-parenting for some case; reuse `getRelationshipBetween()` in `RelationshipManagerController::store()`'s duplicate-check if keeping it.

### 3. Standalone Dropdown Manager can't create a new status list from scratch

- **Where**: `resources/js/Pages/Settings/Dropdowns/Create.vue`
- **What**: `DropdownList.is_status` (added in `2026_07_09_130000_add_is_status_to_dropdown_lists_table.php`) is what makes a list use the rich color/icon status editor everywhere it's edited — but the standalone create page has no toggle to set it. A new status-flavored list can currently only be created from a `status`-typed field's own create/edit flow (`CreateNewDropdownListModal.vue`), not directly at `/settings/dropdowns/create`.
- **Impact**: Minor UX gap, not a bug — the field-context path is a full workaround, just an extra hop.
- **Fix**: Add the same `is_status` toggle `CreateNewDropdownListModal.vue` has to `Create.vue`.

### 4. Two-factor authentication — dead, unused columns

- **Where**: `users` table (`two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `failed_login_attempts`, `locked_until`)
- **What**: 2FA isn't offered in this version by design. Confirmed these five columns are never read or written anywhere in `app/` (the only `locked_until` hits are an unrelated column on the `modules` table, used for the Module Builder's draft-editing lock). All nullable/defaulted — inert, not a risk.
- **Impact**: None. Purely cosmetic: a future dev could assume 2FA is supported and be wrong.
- **Fix**: No urgency. A one-line migration to drop the columns is optional cleanup, not a requirement.

---

## Not a Bug — Left for V2

> Real, known gaps — not doc-mismatches or dead code — but a proper fix is a larger V2 feature, not a patch to what exists today.

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

### Granular RBAC (per-user/per-role view vs. create vs. edit vs. delete)

- **Where**: `app/Scopes/AdminOnlyModuleScope.php`, `Module` (no `can_view`/`can_create`/`can_edit`/`can_delete` columns)
- **What**: Module visibility is currently a single binary split — a regular user either sees a module (and then has full create/edit/delete/link on it) or doesn't. There's no finer-grained per-action or per-role permission model.
- **Impact**: Cannot restrict a role to, say, view-only on a module, or allow create but not delete. See `FEATURES.md` §11 for the current model.
- **Why it's left for V2, not fixed now**: explicitly confirmed as deliberate V1 scope, not an accidental gap — a full RBAC system is a substantial feature on its own, not a patch.

### Record restore (Bin system)

- **Where**: `RecordController.php` (`// TODO: Offer recovering deleted records (Bin system)`)
- **What**: Deletion is a hard delete — `AuditObserver::deleted()` captures only a display label, not a full attribute snapshot, so a deleted record can't be reconstructed from its audit entry.
- **Impact**: No way to recover an accidentally-deleted record today.
- **Why it's left for V2, not fixed now**: confirmed deliberate ("what is deleted is gone, and I'm fine with it") — a real fix isn't a quick snapshot-and-restore, it's a full **Bin system** (soft delete, a retention window of N days, auto-purge after). See [[project_record_restore_roadmap]].
