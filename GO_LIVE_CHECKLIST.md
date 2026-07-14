# Go-Live Checklist

Pre-launch audit findings and their status. Generated from a full-codebase scan the day before go-live.

## Critical

- [x] **`routes/api.php` had a self-admitted "must guard before production" comment and was still unguarded** — `/icons-test` debug route removed entirely; `/icons`, `/dropdown-lists`, `/related-module-records/{id}` wrapped in `Route::middleware(['web', 'auth'])`. Covered by `tests/Feature/Api/ApiRouteGuardTest.php` (guests get 401, authenticated users get 200).
- [x] **Login had no working rate limiting** — `AuthController::login()` was type-hinting a plain `Request`, so the throttle logic in `LoginRequest` was never invoked. Wired `LoginRequest` up properly (fixed its field name from Breeze's stock `email` to this app's `username`, and its error key from `email` to `general` to match what `Login.vue` actually reads). 5 failed attempts locks out for 60s. Covered by new tests in `tests/Feature/Auth/AuthenticationTest.php`.
  - Noted but not yet implemented: escalating lockout (60s → 120s → 240s...) after repeated lockouts. Left for a follow-up pass.
- [x] **Seeders will nuke prod with fake data if anyone runs `db:seed` by habit** — the repo's `database/seeders/DatabaseSeeder.php` (unconditionally chains `UsersTableSeeder` + `DevSeeder`) is not what actually runs in production: confirmed that file is gitignored on the prod branch/server and prod has its own version that a deploy never overwrites. The only in-app seeding trigger reachable in production is the onboarding "populate demo data" button (`OnboardingController::seedDemoData()`), which only calls `DevSeeder` + structural seeders and never `UsersTableSeeder`. Closed.

## High

- [x] **No confirmed queue worker on the prod box** — `deploy.sh` only runs `queue:restart` (signals existing workers), never starts one; queued jobs (imports, mail, etc.) would silently sit unprocessed without a persistent worker. Confirmed a worker is running.
- [x] **Test suite doesn't finish** — root cause: `OnboardingController::seedDemoData()` unconditionally lowered `memory_limit` to `512M` before seeding demo data. `phpunit.xml` intentionally sets `memory_limit=-1` (unlimited) for test runs, so this call was actively capping an already-unlimited ceiling right before `OnboardingControllerTest::test_demo_data_seeds_records_when_populate_true` (the one test that calls it) seeds ~332 factory records on top of everything ~250 prior tests had already accumulated in the shared PHPUnit process — a fatal "memory exhausted" error can't be caught or unwound, so restoring the limit in a `finally` block (tried first) couldn't help either. Fixed by only *raising* the limit when the current one is finite and below the target, never lowering an already-sufficient or unlimited one. Full suite now passes clean: 266/266.

## Deferred out of go-live scope

Everything below was deliberately deprioritized — not going to block tomorrow's launch, and not being tracked further on this checklist.

- **`.env` hardening** — moved to its own follow-up branch, tracked separately (not part of this go-live pass):
  - [x] `APP_ENV=production` confirmed on the live server (was defaulting to `local`, which let `LocalAutoLogin` middleware auto-login as `admin` on every request — it's registered globally on every web route)
  - [x] `DEBUGBAR_ENABLED=false` confirmed/fixed (was leaking queries/session/env vars)
  - [ ] `SESSION_SECURE_COOKIE=true` — still unset, defaults to `false`, so the session cookie isn't marked `Secure` over HTTPS.
  - [ ] `ADMIN_EMAIL` — missing from `.env.example`; falls back to a personal Gmail address in `config/mail.php`.
  - [ ] `MAIL_MAILER` — `.env.example` defaults to `log` (silent no-op mail); confirm prod `.env` overrides it.
- Mass assignment wide open (`$guarded = []`) on `BaseModule`, `Module`, `Relationship`, `Bundles`, `Crankes`, `Deal`, `Order`, `Product` models. Ignored per decision.
- `console.error(...)` scattered in ~20 Vue files. Ignored per decision.
- No CI pipeline (`.github/workflows`). Ignored per decision.
- Stray TODOs (Bin/recovery system, tooltip universality) — already tracked in the incomplete-features backlog anyway. Ignored per decision.
- Dead/broken route `/api/related-module-records/{id}` (wrong param count, throws if hit while authenticated; duplicate of the properly-wired route in `routes/web.php`). Ignored per decision.

## Resolved today

- [x] Broken icon: `fa-grid-2` in `resources/js/Pages/Dashboard/Index.vue` was FontAwesome Pro-only, not in the installed free set — rendered blank on the empty-dashboard state. Fixed.
- [x] Missing German translations for the colorpicker (`colorpicker.tab_palette`, `tab_custom`, `recent`, `pick_from_screen`) — added to `lang/de/fields.php`. Also fixed a real bug found along the way: the English key was literally `"pick_from_screen "` (trailing space in the array key), so that translation never resolved even in English. `php artisan lang:compare` now reports zero missing keys across all lang files.

## Other fixes made today (not from the original security scan)

- [x] `.list-layout` and `.record-layout` had a hardcoded `left: 6rem`, breaking layout when the sidebar is hidden. Added a `sidebar-hidden` class toggle on `<html>` (in `Sidebar.vue`, alongside the existing `--sidebar-content-offset` var) and a `html.sidebar-hidden & { left: 0; }` rule in `modules.scss` for both.
- [x] Dashboard couldn't be cleared to an empty state — removing all widgets always fell back to the default preset. Root cause: `DashboardController::saveLayout()` validated `'layout' => 'required|array'`, and Laravel's `required` rule treats an empty array as absent, so `{ layout: [] }` 422'd and the frontend silently swallowed the error, leaving the last saved (non-empty) layout in place. Changed the rule to `['present', 'array']`. Covered by a new test in `tests/Feature/DashboardControllerTest.php`.
