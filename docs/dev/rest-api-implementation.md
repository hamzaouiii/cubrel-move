# REST API (v1)

Branch: `WIP/rest-api`

## 1. What this feature is

A token-based REST API (`/api/v1/{module}`) letting an external system read
and write records for any module, gated per-token by a `module:action`
ability model on top of Sanctum. One generic controller/service/request/
resource stack serves every module — no per-module API controllers — mirroring
the same "one generic handler" philosophy `BaseModuleHandler` already uses
for the web app.

There was no API layer of any kind in this codebase before this feature —
Sanctum wasn't installed, there was no `auth:sanctum` guard, and
`routes/api.php` only had a handful of session-authenticated `web` routes
plus one public webhook.

## 2. Code structure

```
app/Http/Controllers/Api/V1/
  RecordController.php          # index/show/store/update/destroy - generic across all modules

app/Http/Requests/Api/V1/
  ModuleRecordRequest.php       # builds validation rules at runtime from Field metadata

app/Http/Resources/Api/V1/
  RecordResource.php            # response shaping - hidden/denylisted fields, custom-field unwrap

app/Services/Api/
  RecordApiService.php          # module resolution + list/find/create/update/delete

app/Http/Controllers/
  ApiTokenController.php        # token management UI - create/list/reveal-once/revoke

resources/js/Pages/Settings/ApiTokens/
  Create.vue, List.vue, Record.vue

config/api.php                  # excluded_modules (only key with default content - see §11)
config/auth.php                 # 'api' guard - driver: sanctum
routes/api.php                  # /api/v1/{module} routes, auth:sanctum + throttle:api

database/migrations/2026_07_30_090233_create_personal_access_tokens_table.php
  # Sanctum's stock migration, adapted to uuidMorphs('tokenable') since
  # User uses HasUuids - the default migration assumes an integer PK.

bootstrap/app.php                # forces JSON error bodies for ValidationException/AuthenticationException/HttpException on api/* paths
app/Providers/AppServiceProvider.php  # RateLimiter::for('api', ...) - 60/min per token; RecordApiService singleton
```

No custom Sanctum model or guard - `Laravel\Sanctum\PersonalAccessToken` and
`HasApiTokens` are used directly, unmodified.

## 3. The ability model

Sanctum only gives you two primitives: a token has a flat array of ability
*strings*, and `$token->can($ability)` is an `in_array` check (plus a `'*'`
wildcard). It has no concept of "module" or "verb" - that vocabulary is
entirely this app's.

- **Vocabulary**: `{module_slug}:{read|write|delete}`. Defined and enforced
  nowhere but `ApiTokenController`/`RecordController` - Sanctum never sees
  anything but the resulting strings.
- **Creation-time**: `ApiTokenController::grantableModules()` builds the
  picker (all active modules not in `config('api.excluded_modules')`, verbs
  limited to `['read']` for anything in `config('api.read_only_modules')`).
  `sanitizeAbilities()` re-derives the same list server-side and intersects
  it against whatever the request claims to want - the request's ability
  list is never trusted directly, only used to select from what's actually
  grantable.
- **Enforcement-time**: `RecordApiService::authorizeAbility()` is the single
  choke point for every action - called from `RecordController` directly
  for `index`/`show`/`destroy`, and from `ModuleRecordRequest::authorize()`
  for `store`/`update` (has to run there, before `rules()` builds anything,
  or an excluded module leaks field names via a 422). It 404s excluded
  modules, 403s writes to read-only modules regardless of grant, then checks
  `$token->can('*') || $token->can("{$module}:{$verb}")`. Sanctum's
  route-level `ability:` middleware couldn't be used here instead - it can't
  parametrize on the `{module}` route wildcard, and the required verb
  differs per action within the same route pattern.

## 4. `RecordApiService` / `Module::withoutGlobalScope(AdminOnlyModuleScope::class)`

`AdminOnlyModuleScope` hides the `users`/`settings` modules from any query
made outside an authenticated web-session admin (`Auth::check() &&
Auth::user()->isAdmin()`, checked against the **default `web` guard**). A
Sanctum bearer-token request never satisfies that check - the token's user
is authenticated on the `api` guard, not `web` - so `RecordApiService::resolveModule()`
explicitly bypasses the scope. It's the only place a module is looked up in
this API layer - `ModuleRecordRequest::rules()` calls into it too, rather
than running its own separate query. This is safe
*only* because `authorizeAbility()` is the real gate here - a token still
needs an explicit `users:read`/`users:write` grant to touch that module, the
same as any other. Bypassing the scope without that per-request ability
check would have reopened exactly what the scope exists to close.

## 5. Custom fields: the bug that motivated this doc's most important section

This is worth reading before touching either `RecordApiService` or
`RecordResource` again - the fix here isn't obvious from the diff alone.

### 5.1 The trap

`BaseModule` uses a `HasCustomFields` trait (`app/Concerns/HasCustomFields.php`)
that already overrides `fill()`: it inspects each *individual* key in the
input array, and if that key's name is a registered `is_custom=1` field for
the module, it writes the value straight into the `custom_fields` JSON
column via `$this->attributes[...]`, bypassing Eloquent's `$fillable` check
entirely (the same way a real column wouldn't need to be in `$fillable` to
be settable if you assigned it directly). Every concrete module model
(`Lead`, `Deal`, `Account`, ...) declares its own explicit `$fillable` array,
and **none of them include `custom_fields` itself** - only `Invoice.php`
happens to.

The original `RecordApiService::allowedInput()` pre-packaged custom field
input into a nested key before calling `create()`/`fill()`:

```php
// WRONG - do not reintroduce this
if (! empty($custom)) {
    $topLevel['custom_fields'] = $custom;
}
```

By the time `fill()` ran, the array contained a literal key named
`"custom_fields"`. `HasCustomFields::fill()`'s per-key check
(`isCustomField('custom_fields')`) is false - that string isn't itself a
registered field name - so it fell through to `parent::fill()`, i.e. normal
Eloquent mass-assignment, which silently dropped it: `custom_fields` isn't
in any module's `$fillable`, and Eloquent's fill-rejection is silent, not an
exception. **A partner sending custom field values got a `201`/`200` back
with no error, and the values were simply never persisted**, for every
module except Invoice.

### 5.2 The fix

Stop wrapping. Pass every writable field through flat and let
`HasCustomFields::fill()`'s per-key routing do its job:

```php
protected function allowedInput(Module $module, array $input): array
{
    return Arr::only($input, $module->writableFieldNames());
}
```

`update()`'s manual "merge custom_fields with what's already there" block
was also removed - `HasCustomFields::fill()` already does that merge
internally (`array_merge($this->getCustomFieldsArray(), $customFields)`), so
doing it a second time in the service was redundant dead weight once the
input is no longer wrapped.

**Do not re-add a per-module `$fillable` entry for `custom_fields` as an
alternative fix.** That would work too, but it's 15 files to remember to
keep in sync (and counting - every future module) instead of one, and it
still requires the caller to know to route custom values into a
`custom_fields` key at all, which is exactly the coupling that caused this
bug. Let `HasCustomFields` own that decision; callers should just pass flat
data.

### 5.3 Response side: don't double-flatten

`HasCustomFields::toArray()` **already** flattens each *registered* custom
field into a top-level key on read - schema-driven, by name - but it leaves
the raw `custom_fields` blob sitting in the array too, alongside the
flattened copies. `RecordResource::foldCustomFields()` only needs to drop
that redundant raw key:

```php
protected function foldCustomFields(array $data): array
{
    unset($data['custom_fields']);
    return $data;
}
```

An earlier version of this method instead did
`array_merge($data['custom_fields'] ?? [], $data)` - re-merging the raw
blob's contents on top. That's redundant with what `HasCustomFields` already
did correctly, and worse: if a custom field is ever removed from a module's
schema while old records still carry it in their `custom_fields` JSON,
`HasCustomFields::toArray()` correctly stops surfacing it (it only iterates
*currently* registered field names) - but the blind re-merge would have
resurfaced that orphaned data anyway. Don't re-add it.

### 5.4 Verified

Round-tripped directly through `RecordApiService` (not `forceFill`, not a
bypass) with a temporary registered custom field: `create()` with a custom
field value persisted correctly and appeared flat in the response; a
partial `update()` touching only that custom field left every other field
untouched and merged correctly against the existing value; no nested
`custom_fields` key appeared in either response. No automated test exists
for this yet (see §12).

## 6. Error responses always JSON on `api/*`

`bootstrap/app.php` registers three exception renderers scoped to
`$request->is('api/*')`: `ValidationException` (422s), `AuthenticationException`
(401s), and the generic Symfony `HttpException` family (covers every
`abort(403/404/...)` call, including `ModelNotFoundException` - Laravel's
own `prepareException()` converts that to a `NotFoundHttpException` before
any renderer runs, so `findOrFail()` 404s are covered too).

### 6.1 The `AuthenticationException` gap (fixed)

This was a real, shipped gap for a while: `AuthenticationException` - thrown
by the `auth:sanctum` middleware itself when a token is missing/invalid -
doesn't extend `HttpException`, so it wasn't covered by the `HttpException`
renderer. It fell through to Laravel's own default `unauthenticated()`
handler, which checks `$request->expectsJson()` (true only if the client
sent an `Accept: */json/` header) and otherwise does
`redirect()->guest(route('login'))`. A client that doesn't set an `Accept`
header - which is common; Postman doesn't by default - got a `302` to
`/login` instead of a `401` JSON body.

Fixed with a third `$exceptions->render()` closure, same `is('api/*')` guard
as the others:

```php
$exceptions->render(function (AuthenticationException $e, $request) {
    if ($request->is('api/*')) {
        return response()->json(['message' => __('api.errors.unauthenticated')], 401);
    }
});
```

The user guide no longer tells partners to send `Accept: application/json`
as a workaround - it isn't needed anymore for this failure mode.

### 6.2 404s are forced to a fixed generic message

`findOrFail()`'s default `ModelNotFoundException` message leaks the
internal model class and record id - e.g. `"No query results for model
[App\Models\Modules\Lead] 019fd3ea-f0e5-7338-bd24-5ae333a939e0"`. The
`HttpException` renderer now overrides the message unconditionally for
`$status === 404` (regardless of cause - an excluded module's bare
`abort(404)` shouldn't read differently than a genuinely missing record
either), and separately for `$status === 429` (`ThrottleRequestsException`'s
`"Too Many Attempts."` is a hardcoded framework string, never routed through
`__()`). Every other status still prefers the real exception message
(`abort(403, __('api.errors...'))` calls elsewhere already carry a real,
translated message), falling back to a generic `api.errors.generic` string
only if empty.

## 7. Localization (`Accept-Language`)

Every message in §6 (and Laravel's own validation messages) can now come
back in a partner's preferred language instead of always English.

### 7.1 Code structure

```
lang/{en,de}/api.php               # errors.* keys - not_found, forbidden_*, etc.
app/Support/ApiLocale.php          # Accept-Language parsing, shared
app/Http/Middleware/SetLocaleFromAcceptLanguage.php
routes/api.php                     # middleware added to the v1 group
bootstrap/app.php                  # AuthenticationException/HttpException renderers call ApiLocale::resolve() directly
app/Http/Controllers/Api/V1/RecordController.php  # authorizeAbility()'s two abort() messages
```

`ApiLocale::resolve()` parses `Accept-Language` (comma-separated tags,
`;q=` weights, region subtags like `de-DE` stripped to `de` since this app
only has locale files per base language), returns the highest-weighted tag
that's in `['de', 'en']`, or `config('app.locale')` if none match/the header
is absent.

### 7.2 Why there are two ways locale gets set, not one

`SetLocaleFromAcceptLanguage` (a normal middleware, added to `routes/api.php`'s
`v1` group) covers every request that reaches a controller or `FormRequest` -
this is what makes `ModuleRecordRequest`'s validation messages localized
essentially for free, since they already go through Laravel's own
`lang/{locale}/validation.php` once `app()->setLocale()` has run before the
`FormRequest` resolves.

It does **not** cover auth failures. `auth:sanctum` throws
`AuthenticationException` from *within its own middleware*, and Laravel's
default `Kernel::$middlewarePriority` list reorders known framework
middleware (`auth`, `throttle`, etc.) to run before any custom middleware
that isn't itself in that list - **regardless of the literal order in the
route group's array**. `SetLocaleFromAcceptLanguage` was listed *first* in
`routes/api.php`'s `v1` group and still ran *after* `auth:sanctum` for an
unauthenticated request, because it isn't a priority-listed middleware.
Confirmed empirically before working out why: a request with a valid token
and `Accept-Language: de` got a German validation message (proving the
middleware works when it runs), but the exact same header on an
unauthenticated request still got `"Unauthenticated."` in English, not
`"Nicht authentifiziert."`.

**Fix:** don't depend on middleware ordering for anything an exception
*renderer* needs. `bootstrap/app.php`'s `AuthenticationException` closure
and the api/* branch of the `HttpException` closure (which also covers
`throttle:api`'s 429, thrown from a similarly early-running middleware) both
call `ApiLocale::resolve($request)` directly and set the locale themselves,
right before building the translated message - they never assume
`app()->getLocale()` already reflects the header. `ApiLocale` was factored
out into its own class specifically so both the middleware and the exception
closures could share the exact same parsing logic without duplicating it.

**Lesson for next time:** any exception renderer that needs
request-derived state (locale, or anything else) that a middleware would
normally provide cannot assume that middleware ran, if the exception might
originate from *inside* a framework-priority middleware itself (auth,
throttling, session, etc.) rather than from a controller. Resolve what you
need directly in the renderer instead.

### 7.3 Verified

```
$ curl -H "Accept-Language: de" http://.../api/v1/leads/<bad-id>
{"message":"Ressource nicht gefunden."}

$ curl -H "Accept-Language: de" http://.../api/v1/leads              # no token
{"message":"Nicht authentifiziert."}

$ curl -H "Accept-Language: de" -d '{}' http://.../api/v1/leads      # valid token, missing name
{"message":"Das Feld Name ist erforderlich.","errors":{"name":["Das Feld Name ist erforderlich."]}}
```

All three round-tripped in English too (default, or with an explicit
`Accept-Language: en`). No automated test yet - see §12.

## 8. Rate limiting

`AppServiceProvider::boot()`:

```php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->currentAccessToken()?->id ?: $request->ip());
});
```

Keyed by the specific token's ID (not the user's ID) - two tokens for the
same user get independent budgets. Unauthenticated requests key by IP
instead, so they still get throttled rather than falling through unbounded.
Applied via `throttle:api` in `routes/api.php`'s route group. `X-RateLimit-*`
headers are added automatically by Laravel's throttle middleware; no custom
code needed for those.

## 9. Embedded child data: `line_items`, `attendees`, `related`

Every single-record response (`show`/`store`/`update` - never `index`, to
avoid extra queries per row on a list page) can carry up to three extra
top-level keys alongside the record's own fields.

### 9.1 `line_items` - quotes/orders/invoices only

`line_items` is itself an excluded module - a partner never queries
`/api/v1/line_items` directly - but a Quote/Order/Invoice response would be
incomplete without them. `RecordController::presentRecord()` checks
`$moduleModel->has_line_items` and embeds them via
`RecordApiService::lineItemsFor()`, which queries `LineItem` by
`parent_type`/`parent_id` (`parent_type` is the owning module's *slug*, the
same convention `LineItemsPanel.vue` already uses on the web side).

### 9.2 `attendees` - meetings only

Same idea, for the one other module with a child collection that isn't its
own queryable resource - there's no "meeting attendees" `Module` row at all,
so there's no exclusion to enforce, just the embedding via
`RecordApiService::attendeesFor()`. Ordering matches
`MeetingAttendeeController::index()` exactly (organizer, then required, then
optional, then by name).

### 9.3 `related` - every module, keyed by relationship name

Applies to *every* module's single-record response, always present (an
empty object if nothing's linked). Reuses `RelationshipService::getAllRelatedRecords()`
- the same method the web app's related-panels sidebar calls - via
`RecordApiService::relatedRecordsFor()`. Two things worth knowing: it's
capped to one page per relationship (`related_panel_limit`, no pagination
exposed here), and relationships pointing at an excluded module are dropped
entirely rather than embedding data that module's exclusion should block.

## 10. Query waste found and fixed while building §9

Measured directly (`DB::listen()`, isolated `tinker` process per test, not
guessed).

### 10.1 `related` was ~24 queries for one record, 16 wasted

`getAllRelatedRecords()` also called `getDataForPanel()` once per
relationship type - fetching `linkingPanel`/`allFields` metadata purely for
web panel rendering, which `relatedRecordsFor()` never reads. Worse: the
module lookup inside it re-queried data `getRelationshipForModule()` had
*already fetched in one batched query* moments earlier, because that batch
result was never written into the static cache `getModuleBySlug()` checks.

Fixed in the pre-existing, web-shared `RelationshipService` (not API-only
code) two ways: `getAllRelatedRecords()` gained an `$includePanelData = true`
parameter (defaults preserve the web sidebar exactly; the API passes
`false`), and `getRelationshipForModule()` now warms `self::$moduleCache`
from its own batch fetch - which also speeds up the *web app's* panel
rendering, since it's the same shared cache. Verified: a lead with 8
relationship types went from 25 queries (16 wasted) to 9, zero duplicates.

### 10.2 `store()`/`update()` resolved the same module twice

`ModuleRecordRequest::rules()` used to run its own independent module query,
and `RecordController` ran a second one for the same module in the same
request. Fixed by binding `RecordApiService` as a singleton
(`AppServiceProvider`) with a per-instance `resolveModule()` cache; `rules()`
now calls `RecordApiService::resolveModule()` instead of querying
independently, so both call sites share one cached lookup.

### 10.3 `AuditObserver::resolveModule()` had no caching at all

Unlike `BaseModule::getModuleSlug()` (same idea, already cached), this
re-queried on every single `created()`/`updated()`/`deleted()` event -
regardless of surface, so this affected the web app too. Given the same
static-cache treatment, keyed by model class.

## 11. Extending the API

**Excluding a module, or making it read-only** - edit `config/api.php`.
`excluded_modules` 404s a module outright (also removed from the
token-creation picker) and is the only key declared by default.
`read_only_modules` does the same job for write/delete (keeps the module
visible/grantable but limits the picker to `read`, enforced in
`RecordApiService::authorizeAbility()`) but isn't in the config file right
now - add `'read_only_modules' => [...]` back if a module ever needs it, the
call site already defaults to `[]` when it's absent.

**Stripping a field from every response for a module** - add a
`'hidden_fields' => ['slug' => [...]]` entry to `config/api.php` (also
removed for now, same reasoning). This is independent of the
`DENYLIST_PATTERNS` regexes in `RecordResource` (`/token/i`, `/secret/i`,
`/password/i`, `/_hash$/i`, `/recovery_codes/i`), which strip
credential-shaped columns from *every* module's response regardless of that
config - a backstop against a column being missed there, not a replacement
for it.

**A new module added via the module builder** needs nothing API-specific to
become reachable - it's picked up automatically as soon as it's `is_active`
and not listed in `excluded_modules`. Its `$fillable` array does not need
`custom_fields` added to it (see §5).

## 12. No automated test coverage

Everything in this feature was verified manually - `curl`, `php artisan
tinker`, and `Cubrel-REST-API.postman_collection.json` (repo root, committed)
covering the full CRUD happy path, validation/auth/permission negative
cases, rate-limit headers, localization (§7), and the embedded-data/query
fixes (§9-10). If this API gets a real test suite, `tests/Feature/Api/V1/`
with `actingAs()`-style Sanctum token
assertions (`Sanctum::actingAs($user, ['leads:read'])`) would be the natural
shape - none of that scaffolding exists yet, and the Postman collection is
not a substitute for one (no CI can run it).
