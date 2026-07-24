# Data Retention & Cleanup

## 1. What this feature is

Before this work, several tables/mechanisms accumulated rows or locked resources with no cleanup path at all — `notifications`, `audit_logs`, `failed_jobs`, `userinvites`, `password_reset_tokens`, `setup_tokens`, and `imports` grew forever; abandoned module-builder drafts, orphaned ad-hoc image uploads, and stuck-open impersonation sessions had no reconciliation; and deleting a record (or replacing an image field's value) never cleaned up the file it was the last reference to.

This was built in two passes:

- **Pass 1 — "pure retention" batch**: five tables that just needed Laravel's `Prunable`/`MassPrunable` trait + a scheduled command (`notifications`, `audit_logs`, `failed_jobs`, `userinvites`, `password_reset_tokens`), plus a prerequisite refactor to make the settings seeder idempotent.
- **Pass 2 — everything else found in a follow-up scan**: one more pure-retention item (`setup_tokens`), one bug-fix-plus-retention item (`imports`), and four items that needed real behavior changes rather than just a cron job (module builder drafts, impersonation sessions, ad-hoc image uploads, record deletion/replacement file cleanup).

All retention windows are admin-configurable at **Settings → System → Data Retention** (`/settings/system/data-retention`), not hardcoded — an org admin can change any window without a deploy.

## 2. Code structure

```
app/Models/
  Notification.php                 # NEW — thin Prunable wrapper around Illuminate\Notifications\DatabaseNotification
  AuditLog.php                     # + Prunable
  UserInvite.php                   # + Prunable
  SetupToken.php                   # + Prunable
  Import.php                       # + Prunable, pruning() hook deletes the uploaded file

app/Jobs/
  ProcessImportJob.php             # failure path now deletes the uploaded file too (previously only the success path did)

app/Services/
  ModuleScaffolder.php             # + discardDraft(), rollback() hardened against a draft with no model_class/table yet
  ImageCleanupService.php          # NEW — deletes image-type field files on record delete / field replacement

app/Observers/
  AuditObserver.php                # + deleting() (full cleanup), updated() calls ImageCleanupService::cleanupReplacedFields()

app/Http/Controllers/
  ModuleBuilderController.php      # + discard() action
  RecordController.php             # destroyMany()'s two bulk-delete branches get manual image cleanup (bulk deletes bypass Eloquent events)
  UserController.php               # impersonate() now captures laravel_session_id after Auth::login() regenerates it

app/Console/Commands/
  PruneOrphanedImages.php          # NEW — images:prune-orphans (weekly)
  PruneStaleDraftModules.php       # NEW — modules:prune-stale-drafts (daily)
  ReconcileStaleImpersonationSessions.php  # NEW — impersonation:reconcile-stale-sessions (hourly)

app/Concerns/
  HasCustomFields.php              # bugfix — __set() called the getter instead of the setter for custom fields

routes/console.php                 # all new Schedule::command(...) entries
routes/web.php                     # DELETE /modulebuilder/{module}/discard

config/settings.php                # 'data-retention' item under the 'system' group
config/default_settings.php        # 7 retention_*_days rows (setting_item: 'data-retention') + reconciled against the seeder (see §3)
lang/{en,de}/settings.php          # field labels for all 7 retention settings + discard-draft UI strings

database/seeders/SettingValuesSeeder.php   # refactored: config-driven, idempotent (see §3)
database/migrations/
  *_add_data_retention_setting_defaults.php
  *_add_setup_token_retention_setting_default.php
  *_add_imports_retention_setting_default.php
  *_add_draft_modules_retention_setting_default.php
  *_add_laravel_session_id_to_impersonation_sessions.php

resources/js/Pages/Settings/Modules/Create.vue   # "Discard Draft" button
```

The Data Retention settings page itself needed **no new Vue component or controller action** — `SettingsController::show()`/`update()` and `Settings/Page.vue` are already fully generic, reading/writing whatever `setting_values` rows exist for a given `setting_item` (see §3).

## 3. Prerequisite: making the settings seeder idempotent

`database/seeders/SettingValuesSeeder.php` previously did a single unconditional `DB::table('setting_values')->insert([...])` from a hardcoded array. A stale, half-applied doc (`docs/dev/notifications-implementation.md` §8.4) described a config-driven refactor that was never actually merged — `config/default_settings.php` existed but was unreferenced, and the seeder still hardcoded everything.

Before building on top of this, the seeder was actually refactored:

```php
public function run(): void
{
    self::seedDefaultSettings();
    self::seedNotificationDefaults();
}

public static function seedDefaultSettings(): void   // loops config('default_settings'), guarded per-key
public static function seedNotificationDefaults(): void   // loops config('default_notification_settings'), guarded per-key
public static function seedDataRetentionDefaults(): void  // NEW — narrow, migration-safe: only the 'data-retention' rows
```

Both `seedDefaultSettings()` and `seedNotificationDefaults()` guard each row with an existence check (`if (DB::table('setting_values')->where('key', $row['key'])->exists()) continue;`) — insert-if-missing, never `updateOrCreate`, so re-running the seeder never clobbers a value an admin already changed.

**Why `seedDataRetentionDefaults()` is separate and narrow, not just a call to `seedDefaultSettings()`:** the first version of this work had the retention-settings backfill migration call the general `seedDefaultSettings()`. Since that sweeps *every* general setting, running it during `migrate:fresh` (which the test suite does, with no seeding) pre-populated ~24 unrelated settings rows that the test suite's own fixtures then tried to create again for the same keys — no unique constraint on `setting_values.key`, so this produced duplicate rows and 161 failing tests (`related_panel_limit`, `app_locale`, `onboarding_completed`, etc. all affected). Fixed by scoping the migration-safe seeding to only the keys it's actually responsible for.

**Hard rule enforced throughout:** every migration here must survive `migrate:fresh --seed` cleanly with zero duplicate `setting_values` rows — verified after every phase.

## 4. Pure retention (Prunable + scheduled command)

Each model below uses Laravel's `Prunable` trait with a `prunable()` scope reading its own setting via `App\Support\Settings::get('retention_*_days', <default>)`. `php artisan model:prune` (scheduled daily) auto-discovers all of them from `app/Models` — no need to list `--model` explicitly.

| Table | Model | Setting (default) | Rule |
|---|---|---|---|
| `notifications` | `App\Models\Notification` (new, wraps `DatabaseNotification`) | `retention_notifications_days` (180) | `created_at` older than window |
| `audit_logs` | `AuditLog` | `retention_audit_logs_days` (730) | `created_at` older than window; `audit_log_affected_records` cleans up via existing `cascadeOnDelete` FK, no separate logic |
| `userinvites` | `UserInvite` | `retention_userinvites_days` (365) | `accepted_at` set, OR `status='expired'` + `expired_notified_at`, older than window. Pending invites never touched. |
| `setup_tokens` | `SetupToken` | `retention_setup_tokens_days` (90) | `used_at` set OR `expires_at` passed, AND `created_at` older than window |
| `imports` | `Import` | `retention_imports_days` (90) | `status` in `mapping`/`failed`/`completed`, `updated_at` older than window. `queued`/`processing` deliberately excluded — a stuck-mid-flight import is a queue problem worth a manual look, not silent deletion. Uses `pruning()` (fires right before delete) to also remove the uploaded file from disk. |

Two more use Laravel's own built-in commands, scheduled daily, no new setting needed:
- `failed_jobs` → `queue:prune-failed --hours=N`, N = `retention_failed_jobs_days` (30d default) × 24.
- `password_reset_tokens` → `auth:clear-resets`, which already respects `config('auth.passwords.*.expire')`.

**Imports bug fix:** `ProcessImportJob`'s failure-path catch block set `status=failed` but never deleted the uploaded file (only the success path did). Now mirrors the success path exactly — the file is deleted the moment the row is marked failed, independent of pruning.

## 5. Module builder drafts: explicit discard + stale-draft pruning

Previously there was no way to abandon a draft module on purpose — `ModuleScaffolder::rollback()` only resets a module back to draft state (used mid-deploy-pipeline-failure), it never deletes the row, and the only way a draft ever disappeared was passive lock-recycling in `ModuleBuilderController::getOrCreateDraftModule()`.

- **`ModuleScaffolder::discardDraft(Module $module)`** (new) — calls `rollback()` (cleans up generated files/table/labels if the draft got that far) then also deletes the draft's `Field` rows and the `Module` row itself.
- **`rollback()` hardened** — a draft discarded before ever reaching the deploy pipeline has no `model_class`/`table_name` yet; `rollback()` now guards both before attempting file/table operations (previously threw a harmless-but-noisy `TypeError`/unlink warning on every bare-draft discard).
- **`DELETE /modulebuilder/{module}/discard`** → `ModuleBuilderController::discard()`, gated to the lock owner (`locked_by === Auth::id()`) or an admin/root user.
- **"Discard Draft" button** in `Settings/Modules/Create.vue`, using the existing `useConfirm()` confirmation-dialog pattern (same one `Settings/Relationships/List.vue` uses for deleting a relationship).
- **`modules:prune-stale-drafts`** (daily) — finds drafts whose lock (`locked_until`) has expired, **and** whose own `updated_at` (and the max `updated_at` across its draft `Field` rows) is also older than `retention_draft_modules_days` (7d default), then calls `discardDraft()`. `locked_until` alone isn't reliable: it's only refreshed on `create()` (page load), not on `saveDraftField()`/`update()` — an actively-edited draft can look lock-expired while genuinely still in progress, so both signals are required.

## 6. Impersonation session reconciliation

`ImpersonationSession.ended_at` was only ever set by an explicit "leave impersonation" click, keyed off `session('impersonation_session_id')` — a value living only inside the Laravel session itself, with no DB-side link back to the `impersonation_sessions` row. A crashed/closed browser mid-impersonation left the row `ended_at = null` forever (rendered as "ongoing" in the admin UI) — a correctness bug, not just unbounded growth.

- **Migration** adds `impersonation_sessions.laravel_session_id` (nullable, indexed), and does a **one-time backfill**: any pre-existing row still `ended_at = null` gets closed out using `started_at + config('session.lifetime')` as a best-effort estimate (these rows can never get a real `laravel_session_id` retroactively, so they're handled once here rather than by the recurring command).
- **`UserController::impersonate()`** now captures `Session::getId()` **after** `Auth::login($user)` — login regenerates the session ID, so capturing it before would store a session ID that's about to be discarded.
- **`impersonation:reconcile-stale-sessions`** (hourly) — for every `ended_at = null` row with a `laravel_session_id`: if that ID no longer exists in the `sessions` table, estimate `ended_at` as `started_at + session.lifetime` (capped at now) — the session most likely ran its full course and was later garbage-collected, not that it ended instantly. If the session row still exists but its `last_activity` is older than the configured lifetime, use that `last_activity` timestamp directly as `ended_at` (exact, not estimated). A genuinely active session (fresh `last_activity`) is left untouched.

## 7. Ad-hoc image upload orphans

`ImageUploadController::store()` has zero DB tracking — it just stores the file and returns a URL. The alternative considered was a new tracking table with a "claim" step wired into every save path that persists an image URL (`User.avatar`, image-type settings, image-type custom fields); the simpler option was chosen instead: a **disk-vs-database reconciliation scan**, no new table, no wiring into save paths.

- **`images:prune-orphans`** (weekly) — lists every file under `storage/app/public/uploads/images/`, builds the set of every path still referenced anywhere (`users.avatar`, `setting_values` where `type='image'`, and every module's image-type custom-field values via `custom_fields->{name}` JSON lookups), and deletes any file not in that set **and** older than a 24-hour grace period (so an in-progress upload from a not-yet-submitted form is never caught mid-flight).

## 8. Record deletion / field replacement doesn't clean up image files

Deleting a record (or replacing an image-type field's value) previously left the old file on disk forever — `BaseModule` had no `deleting` hook, and `Storage::delete` was never called anywhere image-related.

- **`App\Services\ImageCleanupService`** (new):
  - `cleanupAllForRecord()` — deletes every image-type field's current value for a record about to be deleted.
  - `cleanupReplacedFields()` — deletes the *old* file for any image-type field whose value changed, comparing old vs. new directly rather than trusting Eloquent's dirty-tracking (a custom-field change only ever shows up as one opaque `custom_fields` dirty key, never decomposed per field name — see the `HasCustomFields` note in §9).
- **`AuditObserver::deleting()`** (new method) — calls `cleanupAllForRecord()` before a record is deleted.
- **`AuditObserver::updated()`** — now also calls `cleanupReplacedFields()` after logging the audit diff.
- **`RecordController::destroyMany()`** — both bulk-delete branches (`allMatchingSelected` and the explicit-selection path) use a raw `whereIn(...)->delete()` query for performance, which bypasses Eloquent model events entirely (`AuditObserver::deleting()` never fires for these). Both branches now fetch each record's image-field values *before* issuing the bulk delete, using `Schema::hasColumn()` to tell a real-column image field apart from a `custom_fields`-JSON-backed one — `Field::is_custom` isn't reliable for this, since `Module::allFields()` doesn't even select that column.
- **Deliberately not airtight**: this doesn't need to catch every possible edge case, since §7's weekly orphan scan is a safety net underneath it — sequenced to ship first for exactly that reason.

## 9. Pre-existing bugs found and fixed along the way

Neither of these was caused by this work, but both were found while testing §8 and are now fixed:

- **`HasCustomFields::__set()`** called the *getter* (`getCustomFieldValue()`) instead of the *setter* (`setAttribute()`) for custom fields — `$model->customField = 'value'` silently did nothing. Dormant in production because every real save path uses `fill()` (which is implemented correctly), but a real bug for any code — including straightforward test code — that sets a custom field via direct property assignment.
- **`Eloquent::getOriginal($fieldName)`** only ever resolves a real DB column; a custom field's pre-change value only ever lives inside `getOriginal('custom_fields')` (the whole JSON blob, already decoded by the model's own `array` cast). `ImageCleanupService::cleanupReplacedFields()` checks both instead of branching on `is_custom`.

## 10. Verification performed

- `php artisan schedule:list` — all 8 scheduled commands appear with correct cron expressions.
- `php artisan migrate:fresh --seed` — clean run, zero duplicate `setting_values` rows across all 7 retention keys.
- Full test suite (273 tests) — passing after every phase.
- Manual, seeded-data verification via `php artisan tinker` for every phase: aged/live rows for each `Prunable` model pruned/kept correctly; `ProcessImportJob` failure path confirmed to delete its file; stale vs. actively-edited draft modules discarded/kept correctly; all three impersonation-session scenarios (genuinely active, idle-but-present, vanished) reconciled correctly; orphan image scan confirmed to delete only unreferenced files (including ~18 genuinely orphaned pre-existing dev artifacts found in `storage/app/public/uploads/images/`); single-record delete, field replacement, and both bulk-delete branches all confirmed to clean up image files correctly.
- **No automated test coverage added** — this feature was verified entirely through manual tinker scripts against seeded data, mirroring how the Notifications feature shipped without dedicated tests.

## 11. Settings reference

All configurable at **Settings → System → Data Retention** (`/settings/system/data-retention`):

| Setting | Default |
|---|---|
| `retention_notifications_days` | 180 |
| `retention_audit_logs_days` | 730 |
| `retention_userinvites_days` | 365 |
| `retention_failed_jobs_days` | 30 |
| `retention_setup_tokens_days` | 90 |
| `retention_imports_days` | 90 |
| `retention_draft_modules_days` | 7 |

No compliance/legal requirement is documented anywhere in this repo for the `audit_logs` window specifically — 730 days was chosen as new policy, not an existing rule being implemented.
