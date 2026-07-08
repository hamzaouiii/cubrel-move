# Automated Test Coverage

What the existing PHPUnit suite (`tests/Feature`, `tests/Unit`) actually verifies, and what still has no coverage. All tests use `RefreshDatabase`; most Feature tests also pull in `Tests\Concerns\InteractsWithDashboardFixtures` for `completeOnboarding()`, `makeUser()`, `makeModule()`, `makeField()` helpers, since module/field rows aren't seeded by default.

**Frontend: zero automated coverage.** No `*.test.*`/`*.spec.*` files exist under `resources/js`, and `package.json` has no test-framework dependency (`vitest`, `jest`, `@vue/test-utils`, etc.). Every test below is PHP-side, hitting Laravel routes/services directly.

## Setup & instance bootstrap

| File | Covers |
| --- | --- |
| `tests/Feature/BootstrapInstanceCommandTest.php` | `cubrel:bootstrap` Artisan command: refuses to run once a user exists, creates a `SetupToken` + prints the `/setup/{token}` URL, re-running regenerates a single fresh token, `--email`/`--locale` options (incl. invalid email/locale rejection). |
| `tests/Feature/SetupTokenServiceTest.php` | `SetupTokenService`: `generate()` persists only a SHA-256 hash (never the raw token), regenerating invalidates the previous token, `validate()` rejects unknown/expired/consumed tokens, `consume()` marks a token used. |
| `tests/Feature/SetupControllerTest.php` | `/setup/{token}` guest flow: redirects once a user exists, `invalid` prop for bad tokens, `?locale=` handling, `POST` creates the root user (`is_admin`/`is_root`) and logs them in, HTTP 410 for bad tokens, persists `app_locale` setting, and token is single-use (replay can't create a second root user). |
| `tests/Feature/OnboardingControllerTest.php` | Post-setup wizard: non-onboarding routes redirect to `/onboarding` while incomplete, redirect away once `onboarding_completed=1`, demo-data seeding toggle (`populate=true/false`), `finish` step marks completion and redirects per `destination`. |
| `tests/Feature/InviteAcceptanceTest.php` | Regression test for a crash where `BaseModule::booted()` tried to auto-fill `owner_id` on `User` (which has no such column). Verifies an invite can be accepted end-to-end and the session stays authenticated. |

## Authentication

| File | Covers |
| --- | --- |
| `tests/Feature/Auth/AuthenticationTest.php` | Login screen renders, valid credentials authenticate + redirect, invalid password leaves the user a guest, logout ends the session. |
| `tests/Feature/Auth/PasswordResetTest.php` | Requesting a reset link sends `ResetPasswordNotification`, the reset screen renders using the real token, submitting a valid reset succeeds and redirects to login. Note in-file: no "forgot password" screen test exists because that form is an inline toggle on `/login`, not a separate route. |

## User profile

`tests/Feature/ProfileTest.php` — profile page renders, update persists `username`/`first_name`/`last_name`/`email` and the concatenated `name`. No account-deletion test, because there's no self-service `DELETE /profile` route (users are admin-managed).

## Dashboard

| File | Covers |
| --- | --- |
| `tests/Feature/DashboardControllerTest.php` | Row-level visibility for `metric` widgets (rep sees only own records by default, `showAllRecords` opt-out, admin always sees all, `sales_manager`-type sees all by default); validation rejects disallowed filter fields / invalid widget type / unknown module (422); dashboard shell Inertia props; module-fields and filterable-fields endpoints; layout save creates-then-updates a single `dashboards` row per user; layout save rejects non-array payloads; `people`-widget HTTP response matches `AggregationService::people()` directly; module-relationships endpoint. |
| `tests/Unit/DashboardPresetsTest.php` | `DashboardPresets`: preset resolution by user type, fallback to `admin`/`read_only` for unknown types, fresh unique `instanceId` stamped on every widget, plain string layout items left untouched. |

## Aggregation / widget data engine

`tests/Feature/AggregationServiceTest.php` — the largest single test file, covering `AggregationService`'s `metric()`, `breakdown()`, `timeSeries()`, `recordList()`, and `people()`:
- `metric()`: `count`/`sum` aggregates, rejects invalid aggregate types, missing `field`, or summing a non-numeric field.
- `breakdown()`: groups by a `select` field ordered by count descending, respects `limit`, rejects invalid `chartType`/missing `groupBy`.
- `timeSeries()`: buckets by month and fills gaps with zero, rejects invalid `interval`/non-date `dateField`.
- `recordList()`: returns rows + module metadata, respects `limit`.
- `people()` via a `record`-type field: ranks by count/sum, resolves avatar URLs, respects `limit`, rejects missing/invalid `relationField`. None of these tests call `actingAs()`, so they double as a regression guard that `resolvePeopleModule()` bypasses `AdminOnlyModuleScope` correctly even when unauthenticated.
- `people()` via a named `Relationship`: ranks/sums across a join-table relationship, rejects unknown relationship name, a relationship not involving the current module, and disallowed filter fields.

## Modules, module builder, generic CRUD

| File | Covers |
| --- | --- |
| `tests/Feature/Modules/ModuleCrudTest.php` | Data-provider-driven across all 9 stock modules (leads, accounts, contacts, deals, quotes, orders, invoices, products, cases): index/create/store/show/update/destroy via the shared `RecordController`/`ListController`. Users covered separately via `UserController` (no destroy route — admin-only user management, no self/other deletion via this path). |
| `tests/Feature/Modules/CustomFieldCrudTest.php` | Custom field definitions (`fields` table, `is_custom=true`) and value round-tripping through `HasCustomFields`' JSON column, across the same 9 modules (`inquiries` excluded — its table has no `custom_fields` column). |
| `tests/Feature/Modules/ModuleBuilderWorkflowTest.php` | Full end-to-end module builder pipeline: draft → define field → deploy (real `CREATE TABLE` + generated Model/Handler PHP files) → activate → post-deploy custom field addition → full CRUD mixing a real column and a JSON-backed custom field. Manually tears down generated tables/files/rows in `tearDown()` since the `CREATE TABLE` DDL implicitly commits and defeats `RefreshDatabase` rollback. |

## Line items

| File | Covers |
| --- | --- |
| `tests/Feature/LineItems/LineItemTotalsObserverTest.php` | `LineItemTotalsObserver` recomputes parent `subtotal`/`discount_amount`/`tax_amount`/`total` on line item create/update/delete, including the last-item-deleted-zeroes-total case; asserts the recompute does **not** itself create an audit "updated" entry (`is_calculated` exclusion + `saveQuietly()`); a line item on an unregistered parent module is a graceful no-op. |
| `tests/Feature/LineItems/LineItemCustomFieldCacheTest.php` | Repeated attribute access on a `LineItem` triggers only one `modules` query (static cache prevents an N-query regression). |

## Audit trail system

The most heavily covered subsystem (8 files), each cross-referenced against `docs/audit-trail-implementation.md`:

| File | Covers |
| --- | --- |
| `tests/Feature/Audit/AuditServiceTest.php` | `AuditService::log()` is a no-op with no authenticated actor; writes a correctly-attributed row when authenticated. |
| `tests/Feature/Audit/AuditObserverTest.php` | Create/update logging with correct diffs; identical-value re-save logs nothing; `is_calculated`-flagged fields excluded from diffs (proven by flag, not by field name); delete captures a `record_label` snapshot; `record`-type field changes resolve human-readable labels, not raw IDs; regression test for a non-admin editing their own `User` record (previously threw via `AdminOnlyModuleScope`). |
| `tests/Feature/Audit/BulkOperationsAuditTest.php` | Bulk update/delete via `RecordController::updateMany()`/`destroyMany()` (query-builder writes that bypass Eloquent events) log one row per batch: `mode: explicit` with `affected_ids`, vs `mode: all_matching` with a count only; bulk delete captures pre-deletion `record_labels`. |
| `tests/Feature/Audit/ImpersonationAuditTest.php` | Starting impersonation creates an `ImpersonationSession` row; actions while impersonating log `user_id` as the impersonated identity but `impersonator_id` always reveals the real actor; leaving impersonation closes the session with correct attribution (regression: must read impersonator session keys before `Auth::logout()`); session duration is positive (regression: previous sign-flip bug); ongoing sessions flagged correctly. |
| `tests/Feature/Audit/ImpersonationSessionControllerTest.php` | Non-admin gets 403; plain (non-root) admin can view; filter by `target_user_id`; `ended_at: null` sessions flagged `ongoing`. |
| `tests/Feature/Audit/AuditLogControllerTest.php` | Non-admin gets 403; admin can filter the global trail by `?module=`; regression test for `fields_by_module` collapsing across modules (an unselected `id` broke `Collection::merge()` dedup); `fields_by_module` carries full field metadata (`type`, `related_module`) for the frontend. |
| `tests/Feature/Audit/RecordHistoryControllerTest.php` | Per-record history is scoped to that record only; surfaces bulk-batch entries the record was part of (matched via `affected_ids`, since bulk rows have `record_id = null`); does not leak batch entries belonging to unrelated records. |
| `tests/Feature/Audit/RelationshipLinkAuditTest.php` | Linking/unlinking logs one row per side (each carrying the other side's resolved display label); unauthenticated `link()` calls (e.g. console/seeder) log nothing, same as the general `AuditService` no-op rule. |

## Miscellaneous

`tests/Unit/ExampleTest.php` is the default Laravel scaffold placeholder (`assertTrue(true)`) — no real coverage, candidate for removal.

## Known gaps (not yet tested)

Findings from an exploration pass over `app/` and `resources/js/` against the suite above.

**High priority** — core logic with zero coverage:
- `ExportController` — JSON/CSV export (incl. line items); row-building/escaping bugs would silently corrupt exported data.
- `RelationshipLinkController` — linking/unlinking is only incidentally exercised via audit logging assertions, not correctness, authorization, or duplicate-link handling.
- `OwnershipService` — raw SQL union queries behind dashboard "records by user" counts, only incidentally covered through `DashboardControllerTest`; `validateTableNames` is exactly the kind of code that should have direct tests.
- No `app/Policies` directory — record-level and admin-only authorization appears to live ad hoc in controllers/middleware rather than centralized policies, and isn't directly tested anywhere.
- `MassUpdateZone.vue` — recently changed, no JS tests exist at all project-wide; the server-side mass-update path has partial coverage (`BulkOperationsAuditTest`) but the frontend's explicit-ids/all-matching selection logic is unverified.
- `LeadController` — a separate, seemingly-legacy JSON API for Leads with no visible auth middleware and no tests; Leads are already fully covered via the generic module system, so this looks like untested (possibly unauthenticated) dead code worth confirming.

**Medium priority**:
- `ModuleManagerController`, `ModuleDeploymentController` (beyond the deploy steps already covered), `LayoutManagerController`, `RelationshipManagerController`, `FieldsManagerController` — module-settings screens, layout editor, and relationship *definition* CRUD have no Feature tests (only field creation and the deploy wizard are exercised).
- `SearchController`, `ListFilterController` — global search and saved list filters, untested.
- `LineItemController` — totals/custom-field-cache are tested via observers, but the controller's own add/update/remove endpoints aren't exercised through HTTP.
- `PdfController` / `PdfTemplatesController` — no tests; rendering regressions would be invisible.
- Maintenance Artisan commands (`CleanCustomModules`, `CompareLangKeys`, `FixArrayKeys`, `FixFieldConfig`, `FixModuleLayouts`, `RemoveFieldsFromConfig`, `SyncLangKeys`) — several mutate module/field config directly with no test coverage.
- `HasCustomFields` — no direct unit test beyond the basic-text-field cases exercised through `CustomFieldCrudTest`.

**Low priority** — smaller CRUD/settings controllers with limited blast radius: `DropdownListController`, `LabelController`, `IconController`, `ImageUploadController`, `LocaleController`, `SettingsController`, `RelatedFieldController`.
