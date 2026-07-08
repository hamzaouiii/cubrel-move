# 419 Session Recovery

Branch: `fix/419`

## 1. Problem

Idle Cubrel sessions expire. When that happens, the browser still holds a stale
CSRF (`XSRF-TOKEN`) cookie and/or a dead session cookie. The next `POST`/`PUT`/
`PATCH`/`DELETE` a user makes — most commonly saving a record — gets rejected by
Laravel's CSRF middleware with HTTP 419.

Before this change, `bootstrap/app.php`'s `withExceptions()` render callback only
whitelisted `[404, 403, 405, 500, 503]` for a custom Inertia `Error` page. 419
wasn't in that list, so it fell through to Laravel's default handling, which
Inertia's client renders as either a raw "419 Page Expired" HTML flash or its own
generic error modal — a jarring, full-screen failure for what is, from the user's
perspective, a trivial and recoverable problem.

The key fact that shaped the whole design: **on a failed Inertia `PUT`, the Vue
component does not unmount** (as long as the response lands back on the same
page/component) — so form state, including whatever the user was mid-typing, is
still sitting in memory. The goal was to exploit that and keep the user on the
record instead of throwing them at an error page.

## 2. Two-branch design

419 (`TokenMismatchException`) can happen for two structurally different
reasons, and they need different recoveries:

| Branch | Cause | Recovery |
|---|---|---|
| **Still authenticated** | CSRF token in the page is stale (idle tab, token rotated), but the PHP session itself is alive | Redirect back to the same page, flash a "please save again" toast. Nothing else needed — the component never unmounted, the user's edits are still in the form. |
| **Not authenticated** | The session itself died (expired, garbage-collected, or was invalidated) | Redirect to `/login` with the current URL captured as the "intended" destination. Stash the in-flight form draft to `sessionStorage` before the component unmounts, and restore it once the user is back on the record after re-authenticating. |

The branch is decided by `$request->user()` at the moment the exception is
rendered. This works because `StartSession` runs *before* CSRF verification in
Laravel's default `web` middleware group, so the session (and therefore
`Auth::check()`/`$request->user()`) is already resolved by the time
`TokenMismatchException` is thrown — if the underlying session was genuinely
expired/GC'd, the request gets a fresh, empty session and `$request->user()` is
`null`; if only the client's cached CSRF token is stale, the real session (and
its auth data) is intact and `$request->user()` resolves normally.

### 2.1 Interaction with "remember me"

`$request->user()` isn't a pure "is there a session" check, it also transparently
triggers Laravel's remember-me recall, and that materially changes how often the
logged-out branch actually fires in practice.

`AuthController::login()` passes the Login page's "keep me signed in" checkbox
through to `Auth::attempt($credentials, $data['remember'] ?? false)`. When
checked, Laravel writes a long-lived `remember_web_...` cookie (paired with a
`remember_token` column on the user row), independent of the session cookie and
its 8-hour lifetime, default duration 576000 minutes (400 days,
`SessionGuard::$rememberDuration`).

`Illuminate\Auth\SessionGuard::user()` (`vendor/laravel/framework/src/Illuminate/Auth/SessionGuard.php:161-187`)
resolves lazily: check the session first, and if that's empty, check for a valid
remember-me cookie and silently log the user back in from it, regenerating their
session on the spot. This resolution happens the *first* time anything calls
`$request->user()` / `Auth::user()` in the request.

Our 419 handler's `$request->user()` call is that first call, because the
`auth` route middleware never gets to run (the exception fires earlier, in
`VerifyCsrfToken`, before routing). So for a user who checked "remember me,"
even a genuinely expired 8-hour session usually won't send them to `/login` at
all, Laravel quietly re-authenticates them from the cookie right there, and
they land in the *still-authenticated* branch (a toast, not a login screen).

Practically, the logged-out branch (redirect + draft stash/restore) mainly
matters for: users who didn't check "remember me," or whose remember
cookie/token is itself gone (explicit logout elsewhere, cleared cookies, or the
~400-day window elapsed).

**Security note, checked deliberately:** a manual logout
(`AuthController::logout()` → `Auth::logout()`) does properly invalidate this,
`SessionGuard::logout()` (line 623-631) rotates the `remember_token` in the
database and clears the cookie, so "remember me" isn't a lingering backdoor
after someone deliberately signs out.

**The `XSRF-TOKEN` cookie doesn't have its own independent clock either.**
`Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::newCookie()` sets its
`Max-Age` to `60 * config('session.lifetime')`, the exact same duration as the
session cookie, and `addCookieToResponse()` re-issues it on *every* request
that passes CSRF verification (including plain GETs, which skip the check
entirely via `isReading()`). So both cookies decay on the same rolling window
and get refreshed together, there's no scenario where simple idle time alone
causes the token to go stale while the session itself stays alive. Passive
idling lands a user in the logged-out branch (both die together), not the
still-authenticated one.

In this specific app, the only thing that invalidates the token independently
of the session is `AuthController::login()`/`logout()` calling
`session()->regenerate()`/`regenerateToken()` (confirmed by grepping `app/` for
any other caller, none exist). Laravel's other usual trigger for this pattern,
regenerating the token after a sensitive action like a password change without
also logging the user out, doesn't apply here either: password reset in this
app only happens on the `guest` middleware group, before an authenticated
session exists to preserve. So as currently built, the still-authenticated
branch is real and correctly handled, but its organic (non-tampered) trigger is
narrower than "just being away for a while" might suggest, see §2.2 for the one
genuine trigger found.

### 2.2 A genuine trigger: impersonation swapping the session under another tab (informational, not addressed here)

Found while investigating what could organically distinguish "stale token"
from "dead session": `UserController::impersonate()` and
`leaveImpersonation()` (`app/Http/Controllers/UserController.php:201-220`,
`251-265`) both call `Auth::login($user)`. That calls
`SessionGuard::updateSession()` (line 574-579), which does
`$this->session->regenerate(true)`, `regenerate()` always also calls
`regenerateToken()`, and `$destroy = true` means the *old* session row is
deleted immediately via `Store::migrate()`
(`vendor/laravel/framework/src/Illuminate/Session/Store.php:616-627`), not just
left to expire.

Since cookies are shared across every tab of the same browser (not per-tab),
the moment an admin impersonates or leaves impersonation in one tab, every
other open tab's next request picks up the new session/token pair
automatically. Two outcomes are possible for a tab that was sitting open on
something else at that moment:

- A narrow timing race, a request already in flight with the old token,
  produces a real 419, landing in the still-authenticated branch above. But
  "still authenticated" here means authenticated as whichever identity the
  swap just landed on, not necessarily the identity that tab's UI still shows.
- Far more likely: no race at all, since the client always reads the cookie
  live rather than caching it, the next action in that tab just succeeds
  silently under the new identity. No 419, no warning, no error.

That second case is the real concern, and it's an impersonation-feature
problem, not a session-recovery one, our 419 handling doesn't cause it and
only incidentally catches a narrow slice of it. Deliberately not addressed by
this branch, see §9.

### 2.3 Multiple devices are fully independent

Worth confirming since it's adjacent: this entire mechanism is per-device, not
per-user. `database/migrations/..._create_sessions_table.php` keys the
`sessions` table on `id` (the session ID) as primary key; `user_id` is just an
indexed lookup column, not unique, so nothing enforces a single active session
per user. Logging in on a second device creates a wholly separate session row
and doesn't touch, invalidate, or reference the first device's session at all.
Each device has its own independent idle clock, its own `XSRF-TOKEN` cookie,
and its own `useKeepAlive` heartbeat (since visibility is evaluated per browser
tab).

"Remember me" is per-user but not per-device either, and doesn't rotate:
`SessionGuard::ensureRememberTokenIsSet()` (line 587-592) only generates a new
`remember_token` if the user doesn't already have one, so the same token value
gets reused across every device that checks the box. Multiple devices can hold
valid, simultaneously-active remember-me cookies referencing the same token,
and logging in on a new device doesn't invalidate any other device's copy.

Net effect: unlike §2.2's same-browser, shared-cookie-jar risk, there is no
cross-device equivalent of it, separate devices never share any cookie storage
at all.

## 3. Backend implementation

### 3.1 `bootstrap/app.php` — the 419 branch

```php
if ($status === 419) {
    if ($request->user()) {
        return back(303)->with('warning', __('globals.session.token_refreshed'));
    }

    return redirect()->guest(route('login'), 303)
        ->with('warning', __('globals.session.expired'));
}
```

This sits *before* the existing `[404, 403, 405, 500, 503]` whitelist check, so
419 never reaches the `Inertia::render('Error', ...)` path.

**Why status `303`, not the default `302`:** Inertia's Laravel adapter
(`inertiajs/inertia-laravel`) auto-converts `302` → `303` for PUT/PATCH/DELETE
redirects — necessary because a `302` redirect in response to a non-GET method
is *not* guaranteed to be followed as a GET by the browser (only `303` forces
that per the Fetch spec; `302` only carries the legacy POST→GET downgrade,
not PUT/PATCH/DELETE→GET). Without it, a redirect after a record save (a `PUT`)
could get replayed as a `PUT` against a GET-only route and 405.

That auto-conversion lives in `Inertia\Middleware::handle()`
(`vendor/inertiajs/inertia-laravel/src/Middleware.php:111-113`) and only runs
when a response returns *normally* through the middleware stack. A
`TokenMismatchException` is thrown by `VerifyCsrfToken`, which unwinds the
middleware stack via a PHP exception — this skips every middleware's
post-`$next()` code, including that 303 rewrite. So the exception-handler
redirects have to set `303` explicitly, which is exactly what `back(303)` and
`redirect()->guest(route('login'), 303)` do (both `Redirector` methods accept an
explicit status as an argument).

**Why `redirect()->guest()` and not a plain `redirect()->route('login')`:**
`Redirector::guest()` captures the "intended" URL to return to after login. Since
the triggering request was a `PUT` (not `GET`), it falls back to
`UrlGenerator::previous()`, i.e. the `Referer` header — which for an Inertia
XHR request is the current record page. That intended URL is stashed in the
session (`url.intended`) and later consumed by `redirect()->intended()`.

**Auto-retry seam (not built):** a silent replay of the original request (fetch
a fresh CSRF token, resubmit the original `PUT` transparently) would hook in
right where the `back(303)` branch is now, before falling back to the
flash+redirect. Left as a comment marker in the code, not implemented — that's
a post-launch polish pass per the original task scope.

### 3.2 `AuthController::login()` — consuming the intended URL

```php
$request->session()->regenerate();
return redirect()->intended('/');
```

Previously this was a hardcoded `redirect()->to('/')`, which silently discarded
whatever `redirect()->guest()` had stashed. Without this change, re-authenticating
after the logged-out 419 branch would always land on the dashboard instead of
back on the record.

### 3.3 `HandleInertiaRequests` — `warning` flash channel

```php
'flash' => [
  'success' => fn() => $request->session()->get('success'),
  'error'   => fn() => $request->session()->get('error'),
  'warning' => fn() => $request->session()->get('warning'),
],
```

Only `success`/`error` existed before. `warning` was added so the 419 messages
(non-blocking, not really "errors") get their own semantic channel — `Alerts.vue`
already supported a `warning` visual style, it just had nothing feeding it.

### 3.4 `/keep-alive` heartbeat endpoint

- `app/Http/Controllers/SessionController.php` (new) — `keepAlive()` returns
  `response()->noContent()` (204). No explicit "touch the session" logic needed:
  merely being a request that passes through the `auth` + `web` middleware group
  causes `StartSession` to save/refresh the session's `last_activity`, which is
  exactly what resets Laravel's idle-lifetime clock.
- `routes/web.php` — `GET /keep-alive`, registered inside the existing
  `Route::middleware(['auth'])` group (not gated by `onboarded`, matching the
  existing `/uploads/image` route's rationale — it should work regardless of
  onboarding state).

### 3.5 Session lifetime

- `config/session.php` — default lifetime `1440` → `480` (8 hours).
- `.env.example` — `SESSION_LIFETIME=120` → `480`, added
  `SESSION_EXPIRE_ON_CLOSE=false` explicitly.
- **The real `.env` was deliberately left untouched** (it may hold
  secrets/live config — not something to silently overwrite). Manual change
  needed: `SESSION_LIFETIME=120` → `480` on line 33 of `.env`, plus add
  `SESSION_EXPIRE_ON_CLOSE=false` if you want it explicit (it already defaults
  to `false` via `config/session.php`).
- Idle expiry itself was intentionally **not** disabled or drastically extended —
  it's a wanted security control for a CRM holding customer data. 8 hours covers
  a full workday of idle time between interactions; the keep-alive heartbeat
  (below) handles the "actively has the tab open" case so idle timeout only
  really bites genuinely abandoned tabs.

### 3.6 Translations

`lang/de/globals.php` / `lang/en/globals.php`, new `session` key:

```php
'session' => [
    'token_refreshed' => 'Ihre Sitzung wurde aktualisiert. Bitte speichern Sie erneut.',
    'expired' => 'Ihre Sitzung ist abgelaufen. Bitte melden Sie sich erneut an.',
],
```

(English: "Your session was refreshed. Please save again." / "Your session has
expired. Please sign in again.")

Originally written in *du*-form German per the initial task spec, then changed
to *Sie*-form on request to match the rest of the app's existing tone (e.g.
`login.setup_subtitle`). The du/Sie inconsistency elsewhere in the app was
flagged as a separate cleanup, not addressed here.

## 4. Frontend implementation

### 4.1 `useFlashToasts.js` (new) — global flash → toast bridge

```js
export function useFlashToasts() {
  const { success, error, warning, clearAllAlerts } = useAlerts();
  const page = usePage();

  watch(
    () => page.props.flash,
    (flash) => {
      const message = flash?.success || flash?.error || flash?.warning;
      if (!message) return;

      clearAllAlerts();
      if (flash.success) success(flash.success);
      if (flash.error) error(flash.error);
      if (flash.warning) warning(flash.warning);
    },
    { immediate: true },
  );
}
```

Before this, exactly one page (`Settings/Layouts/Edit.vue`) manually read
`usePage().props.flash` and rendered a toast itself. Since a 419 can happen on
*any* of the ~30 authenticated pages, flash-to-toast needed to be global, not
per-page.

Mounted in:
- `AppLayout.vue` (all authenticated pages)
- `GuestLayout.vue` (used by `Error.vue` via `Layout.vue`)
- `Login.vue` **directly** — it turns out `Login.vue` doesn't use `GuestLayout`
  at all (it renders itself standalone with its own `<Alerts>`), so it needed
  its own explicit `useFlashToasts()` call. This matters because the logged-out
  419 branch's flash message is shown precisely on `/login`.

`clearAllAlerts()` before rendering: added after discovering that without it, a
transient toast already on screen (e.g. Record.vue's "Updating..." info toast
shown while a save is in flight) would sit alongside the new flash toast
indefinitely instead of being cleared out — see §5.3.

**Side effect discovered and fixed:** adding this global watcher meant
`Settings/Layouts/Edit.vue`'s own manual flash-reading code would now
double-fire a toast for the same `flash.success` value (once from its own
`onSuccess`, once from the new global watcher). Removed the redundant manual
code there since `LayoutManagerController::store` always flashes the exact same
message the page's fallback text used.

### 4.2 `useKeepAlive.js` (new) — heartbeat

```js
const INTERVAL_MS = 5 * 60 * 1000;

export function useKeepAlive() {
  let timer = null;

  const ping = () => {
    if (document.visibilityState === "visible") {
      axios.get("/keep-alive").catch(() => {});
    }
  };

  onMounted(() => { timer = setInterval(ping, INTERVAL_MS); });
  onUnmounted(() => { if (timer) clearInterval(timer); });
}
```

Mounted once in `AppLayout.vue`. Because every authenticated page assigns the
*same* `AppLayout` component reference via `defineOptions({ layout: AppLayout })`,
Inertia treats it as a persistent layout and doesn't remount it between page
navigations — so the interval genuinely runs once per authenticated session,
not once per page.

**Visibility semantics** (precise, not "user activity"): the check is
`document.visibilityState === 'visible'`, which is `false` only when the tab is
actually hidden — switched away to another tab, minimized, or the OS has it
backgrounded. A window that's on-screen but not OS-focused (e.g. side-by-side
with another app) still counts as visible. The heartbeat does **not** require
mouse movement or keystrokes — an open, visible, idle tab still pings.
Consequently: as long as a tab stays visible, the session persists indefinitely
(5-minute renewals against a 480-minute lifetime never let the idle clock get
anywhere close to expiring). There's no separate "absolute session age" cap in
this app beyond the rolling idle lifetime, so this really is "never dies while
visible," not just "dies less often."

There's no `visibilitychange` listener — the interval fires unconditionally
every 5 minutes and the visibility check happens only at that moment. A tab that
becomes visible again mid-interval does **not** get an immediate catch-up ping;
it waits for the next scheduled tick.

### 4.3 `Modules/Record.vue` — save-flow integration

This is the most involved piece, and where most of the debugging happened (see
§5). Three things layer on top of the existing `saveRecord()` → `form.put(url, {...})`
call:

**a) Detecting a 419 bounce vs. a real save**

```js
onSuccess: (page) => {
  if (page.props.flash?.warning) {
    if (page.component === "Login") {
      sessionStorage.setItem(draftStorageKey(), JSON.stringify(form.data()));
    }
    form.defaults({});
    return;
  }
  isEditing.value = false;
  clearAllAlerts();
  success(t("modules.actions.update_success"));
},
```

The check is `page.props.flash?.warning`, **not** `page.component === "Login"`.
A still-authenticated 419 bounce (`back(303)`) redirects back to the *same*
`Modules/Record` component — so component-name matching alone can't distinguish
"the write actually happened" from "we got bounced back to where we started." A
genuine save (`RecordController::update`) flashes `success`; a 419 bounce
flashes `warning`. That's the reliable signal.

**b) Draft preservation (logged-out branch only)**

```js
const draftStorageKey = () => {
  const moduleSlug = props.module.slug ?? props.module;
  return `cubrel:draft:${moduleSlug}:${props.record.id}`;
};
```

- **Stash**: only when `page.component === "Login"` (i.e. we're actually
  navigating away, not bouncing back to the same page) — `sessionStorage.setItem(draftStorageKey(), JSON.stringify(form.data()))`.
- **Restore**: on `onMounted`, check `sessionStorage` for a matching key,
  `Object.assign(form, JSON.parse(stashed))`, flip into edit mode, and remove
  the stashed entry.

Scoped deliberately to this one generic record editor (used for every module's
record page), not every `useForm`-based page in the app (PdfTemplates, Settings
forms, etc. would need the same treatment if this behavior is wanted there too
— noted as a follow-up, not built).

**c) Keeping the Save button honest — `form.defaults({})`**

Inertia's `useForm` (`node_modules/@inertiajs/vue3/dist/index.esm.js:196-200`)
always runs this after *any* successful visit, regardless of what a custom
`onSuccess` callback does:

```js
const onSuccess = options.onSuccess ? await options.onSuccess(page2) : null;
if (!defaultsCalledInOnSuccess) {
  defaults = cloneDeep2(this.data());
  this.isDirty = false;
}
```

Landing back on the record after a 419 bounce still counts as "a successful
visit" from Inertia's point of view — so without intervention, it would treat
the user's *unsaved* edit as the new clean baseline, silently disabling the
Save button (`:disabled="!isDirty"`) even though nothing was actually persisted.
Calling `form.defaults({})` (empty object — no actual field changes) inside
`onSuccess` sets Inertia's internal `defaultsCalledInOnSuccess` flag without
touching `defaults` or `isDirty`, suppressing the automatic reset. This was
found by a manual test that showed a real, reproducible symptom: after a 419
bounce, the field's edited value was still visibly showing, but Save was greyed
out and unclickable.

### 4.4 `useUnsavedChangesGuard.js` — teaching it about in-flight saves

The guard has two independent mechanisms:
1. `router.on("before", ...)` — intercepts Inertia SPA navigations (custom
   "Ungespeicherte Änderungen" confirm dialog).
2. `window.addEventListener("beforeunload", ...)` — intercepts real browser
   document unloads (native "leave site?" prompt).

Only mechanism 1 checked the composable's own `isActive` toggle. Mechanism 2
did not, at all — meaning there was no way to tell the guard "a save is
currently in flight, don't interrupt it," for the native prompt.

```js
const handleBeforeUnload = (event) => {
  if (!isActive.value) return;   // added
  if (getIsDirty()) { ... }
};
```

`Modules/Record.vue` now toggles this around every save:

```js
.put(url, {
  onBefore: () => { unsavedGuardActive.value = false; },
  onFinish: () => {
    setTimeout(() => { unsavedGuardActive.value = true; }, 100);
  },
  ...
});
```

The 100ms-delayed re-arm (rather than an immediate flip) mirrors a pattern
already used elsewhere in this same composable (after a *confirmed* "leave
page" action). It matters because `onFinish` resolves as a promise microtask,
while a browser's actual `beforeunload` processing (if Inertia falls back to a
hard `window.location` reload — see §5.4) happens on a later browser navigation
task. Re-enabling the guard synchronously in `onFinish` risked re-arming it
*before* that native prompt was ever evaluated, defeating the whole point.

## 5. Bugs found during manual testing (chronological)

Documented in detail because each one reflects a real, non-obvious mechanism
worth remembering if this code needs to change later.

### 5.1 False "success" toast + no warning shown (still-authenticated branch)

**Symptom:** corrupting `XSRF-TOKEN` and saving showed a normal "update
successful" toast; on page refresh, the value had *not* actually been saved.

**Root cause, two compounding bugs:**
1. `onSuccess`'s original check was `page.component === "Login"` — which only
   ever matches the *logged-out* branch. The still-authenticated branch's
   `back(303)` lands on the *same* component, so this check silently fell
   through to the "real save succeeded" branch every time.
2. That "real save succeeded" branch called `clearAllAlerts()` right before
   showing its own success toast — which would have also wiped out any warning
   toast the global flash watcher had added, even after fixing (1).

**Fix:** check `page.props.flash?.warning` instead of component name (§4.3a),
and don't touch alerts at all in the bounced branch — let the global
`useFlashToasts` watcher own it exclusively.

### 5.2 Save button disabled after a 419 bounce

**Symptom:** after the fix above, the warning toast displayed correctly, but
the Save button was greyed out — editing was effectively bricked, since the
field still showed the user's unsaved edit but there was no way to resubmit it.

**Root cause:** Inertia's own `useForm` internals unconditionally reset the
dirty-tracking baseline to current data after *any* successful visit (§4.3c).

**Fix:** `form.defaults({})` inside the bounced branch of `onSuccess`.

### 5.3 Stale "Updating..." toast lingering next to the warning

**Symptom:** the info toast shown at the start of `saveRecord()` ("Updating...")
stayed on screen indefinitely alongside the warning toast, rather than being
replaced.

**Fix:** `useFlashToasts` now calls `clearAllAlerts()` immediately before
rendering any flash-derived toast (§4.1), rather than leaving each caller to
manage clearing individually (which is what caused bug 5.1's second half in the
first place).

### 5.4 Native "leave page?" browser dialog interrupting the save

**Symptom:** after a 419 bounce, the browser's own (not the app's custom)
unsaved-changes confirmation appeared, sitting in front of the record.

**Root cause, layered:**
- Every `npx vite build` run during this debugging session changed
  `public/build/manifest.json`'s hash. Inertia-Laravel's default
  `version()` (`vendor/inertiajs/inertia-laravel/src/Middleware.php:29-44`)
  hashes exactly that file, regardless of whether Vite's dev server is
  running — so each rebuild silently invalidated the asset version the
  browser's already-loaded page was still using.
- The next GET in the 419 redirect-follow chain carried the browser's stale
  `X-Inertia-Version` header, which Laravel's `onVersionChange()` handling
  answers with `409 + X-Inertia-Location`.
- Inertia's client reacts to that specific combination
  (`node_modules/@inertiajs/core/dist/index.js` — `isLocationVisit()` /
  `locationVisit()`) with a genuine `window.location.href = ...` **hard**
  reload — a real document unload, which is exactly what triggers the
  browser's native `beforeunload` prompt if the form is dirty.
- This is a real production concern too, not just a dev-session artifact: any
  user mid-save across a deploy (which changes the asset hash for real) could
  hit the exact same collision.
- `useUnsavedChangesGuard`'s native listener had no way to be told "this
  particular navigation is expected, don't block it" (§4.4's root cause).

**Fix:** §4.4 — `isActive` now gates both of the guard's mechanisms, and
`Record.vue` disables the guard around the request lifecycle of every save,
re-arming after a short delay to survive the native-navigation timing gap.

**Process note:** repeatedly running `npx vite build` while the user was on
`npm run dev` (Vite HMR) was pure overhead *and* the direct cause of bug 5.4's
manifestation during testing — HMR already picks up changes; production builds
should only run right before something actually ships, not as a syntax-check
habit during iteration.

## 6. Files touched

**Backend**
- `bootstrap/app.php` — 419 branch
- `app/Http/Controllers/AuthController.php` — `redirect()->intended('/')`
- `app/Http/Middleware/HandleInertiaRequests.php` — `warning` flash key
- `app/Http/Controllers/SessionController.php` (new) — `keepAlive()`
- `routes/web.php` — `GET /keep-alive`
- `config/session.php` — default lifetime
- `.env.example` — `SESSION_LIFETIME`, `SESSION_EXPIRE_ON_CLOSE`
- `lang/de/globals.php`, `lang/en/globals.php` — `session.*` keys

**Frontend**
- `resources/js/Composables/useFlashToasts.js` (new)
- `resources/js/Composables/useKeepAlive.js` (new)
- `resources/js/Composables/useUnsavedChangesGuard.js` — `isActive` gate on
  `beforeunload`
- `resources/js/Layouts/AppLayout.vue` — mounts both new composables
- `resources/js/Layouts/GuestLayout.vue` — mounts `useFlashToasts`
- `resources/js/Pages/Login.vue` — mounts `useFlashToasts` directly (doesn't
  use `GuestLayout`)
- `resources/js/Pages/Modules/Record.vue` — bounce detection, draft
  stash/restore, `form.defaults({})`, guard coordination
- `resources/js/Pages/Settings/Layouts/Edit.vue` — removed now-redundant manual
  flash-reading code (duplicate-toast fix)

## 7. Manual verification

All three scenarios below were run against a real browser session (not just
build/lint checks) and confirmed working as of the state described in this
document.

### Scenario 1 — still authenticated, stale CSRF token

1. Open a record, edit a field, don't save yet.
2. DevTools → Application → Cookies → edit `XSRF-TOKEN`'s value to garbage
   (don't delete it — see note below on why deletion is unreliable).
3. Click Save immediately, no intervening requests.
4. **Expected:** warning toast ("Ihre Sitzung wurde aktualisiert..."), no
   "Updating..." toast lingering, stays on the record, Save button remains
   enabled, no native or custom "leave page" prompt.
5. Click Save again — should persist for real this time.

**Why "corrupt," not "delete," the cookie:** Laravel's CSRF middleware only
re-issues the `XSRF-TOKEN` cookie on requests that *pass* verification — GETs
pass trivially since they don't check CSRF at all. If anything triggers a GET
between deleting the cookie and clicking Save (a page refresh, background
reload), the cookie silently comes back valid and the mismatch never actually
happens. Corrupting the value to something wrong sidesteps this race entirely.

### Scenario 2 — logged out, session fully expired

Fast method: delete the *session* cookie (named like `<app-slug>-session`, not
`XSRF-TOKEN`) directly — this makes the very next request look completely
unauthenticated without waiting for real expiry. Realistic method: set
`SESSION_LIFETIME=1` in `.env`, restart, wait 2+ idle minutes.

1. Open a record, edit a field, don't save.
2. Kill the session (either method above).
3. Click Save.
4. **Expected:** redirect to `/login`, warning toast ("Ihre Sitzung ist
   abgelaufen..."), no unsaved-changes interruption.
5. `sessionStorage` should hold a `cubrel:draft:<module>:<id>` key with the
   edited field's value.
6. Log back in.
7. **Expected:** lands back on the *same record* (not the dashboard) —
   confirms `redirect()->intended()` is consuming the URL `redirect()->guest()`
   captured via the `Referer` header.
8. **Expected:** record opens already in edit mode with the field's value
   restored from the stashed draft; the `sessionStorage` key is now gone.
9. Save — should persist for real.

### Scenario 3 — keep-alive heartbeat

Tested with a temporarily shortened interval (30s instead of 5 min) to make
manual verification practical; reverted to `5 * 60 * 1000` afterward.

1. Set `SESSION_LIFETIME` to something short but longer than the heartbeat
   interval (otherwise the session dies before the first ping ever fires).
2. Log in, leave an authenticated tab open and **visible**, don't interact.
3. DevTools → Network, filter `keep-alive` — confirm periodic `GET /keep-alive`
   → `204` requests.
4. Wait past the original session lifetime without interacting, then perform a
   save — should succeed with no 419, proving the heartbeat kept the session
   alive on its own.
5. **Visibility check:** switch away to another tab/app for longer than one
   interval, then return — no `keep-alive` request should have fired while
   hidden (confirms a genuinely abandoned tab still expires as intended); no
   catch-up ping fires immediately on return either (interval isn't
   visibility-triggered, just visibility-*gated*).

## 8. Known limitations / explicit non-goals

- **No silent auto-retry.** A stale-token save currently always requires one
  manual re-click of Save; automatically replaying the original request behind
  the scenes was explicitly out of scope (seam left in `bootstrap/app.php`).
- **Draft preservation is reactive, not proactive.** The `sessionStorage`
  stash only happens at the moment a save attempt bounces off a 419. A tab left
  idle for hours with an unsaved edit that the user never attempts to save
  again has no background auto-save — same risk as any unsaved web form. The
  native `beforeunload` prompt (when the guard is active, i.e. not mid-save)
  is the only other safety net, and only fires on an actual attempt to close/
  navigate away.
- **Draft preservation is scoped to `Modules/Record.vue` only.** Other
  `useForm`-based pages (PdfTemplates, Settings forms, etc.) don't get the
  same stash/restore treatment. Would need the identical pattern applied
  per-page if wanted there.
- **No `visibilitychange`-triggered immediate heartbeat** on tab refocus —
  purely interval-based, gated by visibility at each tick.
- **The real `.env` was not modified by this work** — see §3.5 for the exact
  manual change still needed.

## 9. Planned follow-up (not built here)

Discussed alongside this work, but out of scope for `fix/419` and not
implemented:

- An admin setting to disable "remember me" entirely. Per plan, this hides the
  "keep me signed in" checkbox from the Login page for every user, not just a
  backend flag while the checkbox stays visible, so once built, everyone falls
  back to the plain 8-hour session with no way to opt into the ~400-day
  remember-me duration described in §2.1.
- An admin setting for password rules, letting admins require other users'
  passwords meet certain requirements (specific rules not yet defined).
- A full audit trail across all modules, plus a distinct impersonation audit
  (who did what *as who*, not just who did what), motivated directly by §2.2's
  finding.
- Conflict resolution on record save. `RecordController::update()` currently
  does a bare `$record->fill(...)->save()`, no optimistic locking or version
  check, whichever save lands second silently wins. Bundled alongside a
  separate, not-yet-scoped bulk-actions feature.

None of these have any code, migration, or Settings UI yet. See the
`project_auth_settings_roadmap`, `project_audit_trail_roadmap`, and
`project_conflict_resolution_roadmap` memory notes for status if picking any
of this up later.
