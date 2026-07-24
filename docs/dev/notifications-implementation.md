# Notifications

Branch: `Notifications`

## 1. What this feature is

An in-app notification system (bell icon + dropdown in the top bar, polled) covering seven event types, backed by Laravel's standard `database` notification channel, plus a per-user, per-type email opt-in. There was no notifications infrastructure in this codebase before this feature — no `notifications` table, no bell, no `routes/console.php` (referenced by `bootstrap/app.php` but the file didn't exist).

## 2. Code structure

```
app/Notifications/
  BaseAppNotification.php        # shared via() — always 'database', + 'mail' if opted in
  RecordAssignedNotification.php
  RecordActivityNotification.php
  MeetingInviteNotification.php
  TaskDueSoonNotification.php
  UserInviteAcceptedNotification.php
  UserInviteExpiredNotification.php
  ImpersonationNotification.php

app/Support/
  NotificationPresenter.php      # renders title/body from raw data, at read time

app/Http/Controllers/
  NotificationController.php     # index/unreadCount/markRead/markAllRead

app/Console/Commands/
  NotifyTasksDueSoon.php          # tasks:notify-due-soon (hourly)
  NotifyExpiredInvites.php        # invites:notify-expired (hourly)

routes/console.php                # Schedule::command(...) entries

resources/js/
  Composables/useNotifications.js       # polling (unread count) + list/mark-read
  Pages/Components/Globals/NotificationBell.vue   # bell + dropdown, mounted in Topbar.vue

config/preferences.php            # 'notifications' tab — 7 email opt-in bool fields
config/default_notification_settings.php  # org-wide defaults, consumed by SettingValuesSeeder
lang/{en,de}/globals.php           # 'notifications' key — bell strings
lang/{en,de}/emails.php            # 'notifications' key — email strings (separate wording)
```

### Why a `BaseAppNotification` base class

All 7 notification types need the exact same channel logic: always write to the database, and additionally send mail only if the recipient opted in for that specific type. Rather than repeat that in each class, `BaseAppNotification` defines `via()` once and each subclass only implements `typeKey(): string`:

```php
abstract class BaseAppNotification extends Notification implements ShouldQueue
{
    abstract public function typeKey(): string;

    public function via($notifiable): array
    {
        $channels = ['database'];
        if ($notifiable->wantsEmailFor($this->typeKey())) {
            $channels[] = 'mail';
        }
        return $channels;
    }
}
```

### Why `toArray()` stores raw data, not rendered text

Earlier versions of this feature had `toArray()` call `__('globals.notifications.xxx.title', [...])` directly and store the resulting string. That caused two real bugs (found during review, both locale-related):

1. **Frozen language** — the notification's text was fixed forever at whatever locale was active when it was created; a user later switching their app language didn't change already-existing notifications.
2. **Mixed languages in one sentence** — module labels were resolved synchronously in the *acting* user's locale (inside the observer, during the web request), but the sentence template was resolved later, when the queued job actually ran `toArray()` on the queue worker — which has no per-request locale. A German module label ended up glued into an English sentence template.

The fix: `toArray()` now returns only plain, untranslated fields (ids, real names, a `module_slug`, an `action` key, ISO timestamps) — **no `__()` calls at all**. `NotificationPresenter::present($type, $data)` builds the actual `title`/`body` from that raw data, and is only ever called from `NotificationController::index()` — which always runs inside a real web request, so `app()->getLocale()` is reliably the *viewing* user's own current locale. This means the same stored notification renders correctly regardless of who the actor was, and stays live if the viewer changes their language later.

`toMail()` is different: an email is a one-shot artifact rendered once at send time, so it still calls `__('emails.notifications.xxx...')` directly — but correctness there comes from `User implements HasLocalePreference`. Laravel checks for this interface when dispatching a notification and wraps rendering in `App::setLocale($notifiable->preferredLocale())` — even when the send happens on a queue worker — so mail always renders in the *recipient's* saved language, never the actor's or the worker's default. Any date/time formatting inside `toMail()`/`NotificationPresenter` sets `->locale(app()->getLocale())` explicitly, since Carbon doesn't automatically follow Laravel's app locale.

## 3. What triggers each type

| Type key | Triggered from | Notifiable |
| --- | --- | --- |
| `record_assigned` | `app/Observers/AuditObserver.php` — `created()`/`updated()`, when `owner_id` is set/changed to someone other than the acting user | new owner |
| `record_activity` | `app/Observers/AuditObserver.php` — `updated()`/`deleted()` (any other field change, or a delete), and `app/Services/Relationships/RelationshipService.php`'s `link()` (an activity module linked to a `has_activity` parent) | record's `owner_id`, unless they're the actor |
| `meeting_invite` | `app/Http/Controllers/MeetingAttendeeController.php::store()`, when `source_type === 'user'` | the invited user |
| `task_due_soon` | `app/Console/Commands/NotifyTasksDueSoon.php`, scheduled hourly, tasks due within 24h not yet flagged (`due_soon_notified_at`) | task's `owner_id` |
| `invite_accepted` | `app/Services/Users/InviteService.php::accept()` | invite's `invited_by` |
| `invite_expired` | `app/Console/Commands/NotifyExpiredInvites.php`, scheduled hourly, invites past `expires_at` not yet flagged (`expired_notified_at`) | invite's `invited_by` |
| `impersonated` | `app/Http/Controllers/UserController.php::impersonate()` | the impersonated (target) user |

`AuditObserver` is the single choke point for `record_assigned`/`record_activity` because it already observes every `BaseModule` subclass (registered from each model's `booted()`, per late static binding — see `docs/dev/audit-trail-implementation.md` §4.1 for why it can't be registered from `AppServiceProvider` instead). It excludes the `userinvites` module (`AuditObserver::NOTIFICATION_EXCLUDED_MODULES`) since `UserInvite` already has its own purpose-built notifications — without that exclusion, accepting an invite fired both `UserInviteAcceptedNotification` *and* a generic "activity on your record" notification for the same event.

## 4. How to add a new notification type

1. **Create the notification class** in `app/Notifications/`, extending `BaseAppNotification`:
   ```php
   class MyNewNotification extends BaseAppNotification
   {
       public function __construct(protected string $someId, protected ?string $someLabel) {}

       public function typeKey(): string { return 'my_new_type'; }

       public function toArray($notifiable): array
       {
           return [
               'type' => $this->typeKey(),
               'some_id' => $this->someId,
               'some_label' => $this->someLabel,
               'url' => "/wherever/{$this->someId}",
               'icon' => 'fa-solid fa-bell',
           ];
       }

       public function toMail($notifiable): MailMessage
       {
           return (new MailMessage)
               ->subject(__('emails.notifications.my_new_type.subject'))
               ->line(__('emails.notifications.my_new_type.body', ['label' => $this->someLabel]));
       }
   }
   ```
   Keep `toArray()` to plain, untranslated data only — no `__()` calls there.

2. **Add a case to `NotificationPresenter::present()`** (`app/Support/NotificationPresenter.php`) building `title`/`body` from that raw data via `__('globals.notifications.my_new_type...', [...])`.

3. **Add lang keys** to `globals.notifications.my_new_type` (bell) and `emails.notifications.my_new_type` (mail) in both `lang/en/` and `lang/de/`.

4. **Add the email default** to `config/default_notification_settings.php` (`notify_email_my_new_type` → `'0'` or `'1'`), and a matching field in `config/preferences.php`'s `notifications` tab so users can override it. Add the field's label to `settings.fields.notify_email_my_new_type` in both lang files.

5. **Fire it** from wherever the triggering event actually happens — `$user->notify(new MyNewNotification(...))`. Prefer hooking into an existing choke point (an observer, a service method already called from every relevant path) over a controller, so you don't miss a call site.

6. **If it needs scheduling** (a "due soon"/"expired"-style periodic check), add a new Artisan command under `app/Console/Commands/` and register it in `routes/console.php` via `Schedule::command(...)`.

No frontend changes are needed for a new type — `NotificationBell.vue` renders whatever `title`/`body`/`icon`/`url` the controller returns, generically.

## 5. Scheduling infrastructure

`routes/console.php` didn't exist before this feature, even though `bootstrap/app.php` already pointed `commands:` at it. It now registers:
```php
Schedule::command('tasks:notify-due-soon')->hourly();
Schedule::command('invites:notify-expired')->hourly();
```
This requires the server's OS cron to actually run `php artisan schedule:run` every minute (Laravel's scheduler dispatches due commands from there) — that crontab entry is a manual, one-time server setup step, not something a migration or command can do.

## 6. Files touched

**Backend**
- `database/migrations/2026_07_23_120000_create_notifications_table.php`
- `database/migrations/2026_07_23_120001_add_due_soon_notified_at_to_tasks_table.php`
- `database/migrations/2026_07_23_120002_add_expired_notified_at_to_userinvites_table.php`
- `app/Notifications/*` (8 files — base + 7 types)
- `app/Support/NotificationPresenter.php`
- `app/Http/Controllers/NotificationController.php`
- `app/Console/Commands/NotifyTasksDueSoon.php`, `NotifyExpiredInvites.php`
- `routes/console.php` (new)
- `routes/web.php` — `notifications.*` routes
- `app/Observers/AuditObserver.php` — assignment/activity hooks + `userinvites` exclusion
- `app/Services/Relationships/RelationshipService.php` — activity-link notify
- `app/Http/Controllers/MeetingAttendeeController.php` — meeting invite hook
- `app/Services/Users/InviteService.php` — invite accepted hook
- `app/Http/Controllers/UserController.php` — impersonation hook
- `app/Models/User.php` — `wantsEmailFor()`, `HasLocalePreference`/`preferredLocale()`
- `config/preferences.php`, `config/default_notification_settings.php`
- `database/seeders/SettingValuesSeeder.php` — consumes the config above

**Frontend**
- `resources/js/Composables/useNotifications.js`
- `resources/js/Pages/Components/Globals/NotificationBell.vue`
- `resources/js/Pages/Components/Globals/Topbar.vue` — mounts the bell
- `resources/scss/globals.scss` — bell/badge/dropdown styling

**Lang** — `globals.notifications`, `emails.notifications`, `settings.fields.notify_email_*`, `preferences.tabs.notifications` in both `lang/en/` and `lang/de/`.

## 7. Live delivery (WebSocket toast + live bell) via Reverb

Branch: `Notifications` (same branch, later addition)

### 7.1 Why

The original feature (§1-§6) was 60s-poll only: the bell badge updated up to a minute late, and there was no toast — nothing appeared until the user opened the dropdown. This addition pushes notifications to the browser the moment they're created, via a private WebSocket channel per user, and adds a bottom-left toast alongside the existing bell.

### 7.2 The three new packages — crash course

**Backend**

- **`laravel/reverb`** — Laravel's own first-party WebSocket server. It's a long-running PHP process (`php artisan reverb:start`) that speaks the Pusher protocol: browsers connect to it directly over `ws://`/`wss://`, and the Laravel app (web requests or queue workers) pushes events *into* it over a small HTTP API whenever something calls `event()->broadcast()` or a notification implements `ShouldBroadcast`. Chosen over Pusher/Ably because it's free and self-hosted, fitting this app's single-VPS bare-metal deploy with no new vendor bill. It needs its own persistent process, separate from PHP-FPM and the queue worker.

**Frontend**

- **`pusher-js`** — the actual WebSocket client library. Reverb deliberately speaks the same wire protocol as Pusher's hosted service, so this is the "dumb" transport: opens the socket, handles ping/pong keepalive and reconnect, sends `pusher:subscribe` frames. It's a dependency of `laravel-echo`, not something this app calls directly.
- **`laravel-echo`** — a thin wrapper around `pusher-js` (or other transports) that adds Laravel-specific conventions: private/presence channel auth against `/broadcasting/auth`, and a `.notification()` helper that listens for the specific event Laravel's notification system broadcasts (`.Illuminate\Notifications\Events\BroadcastNotificationCreated`) without needing to know Pusher's raw event-naming rules.
- **`@laravel/echo-vue`** — a small Vue-specific layer on top of `laravel-echo`: `configureEcho()` sets up one shared Echo instance app-wide from `VITE_REVERB_*` env vars (called once in `resources/js/bootstrap.js`), and `echo()` returns that singleton so any component can attach `.private(channel).notification(callback)` without re-instantiating Pusher/Echo itself. Used here instead of hand-rolling an Echo composable since it ships with the framework's own conventions already correct.

### 7.3 How a notification reaches the browser

1. `$user->notify(new XxxNotification(...))` is called from the same choke points as §3 — unchanged.
2. `BaseAppNotification::via()` now includes `'broadcast'` alongside `'database'`/`'mail'`, so every one of the 7 types broadcasts automatically — no per-subclass change needed.
3. `BaseAppNotification::toBroadcast($notifiable)` builds the payload. This is the one place broadcasting couldn't just reuse the existing read-time rendering: `NotificationController::index()` renders `title`/`body` inside a real request, in the *viewer's* locale, but a broadcast fires once, on the queue worker, with no such request. So `toBroadcast()` temporarily swaps `App::setLocale($notifiable->preferredLocale())`, calls the same `NotificationPresenter::present()` used for the database list, then restores the previous locale — the same `HasLocalePreference` mechanism `toMail()` already relies on (§2), applied at broadcast time instead of read time.
4. Laravel dispatches this on a **private channel named `App.Models.User.{id}`** (`routes/channels.php`), authorized by session auth via `/broadcasting/auth` — no Sanctum/API tokens needed since this is a same-origin Inertia session app. Note `User`'s primary key is a UUID (`HasUuids`), so `{id}` in the channel name and in `routes/channels.php`'s authorization check is a UUID string, not an integer.
5. The queue worker (`ShouldQueue`, same `database` queue connection as everything else) processes the notification, then a second small queued job (`BroadcastNotificationCreated`) makes the actual HTTP call into Reverb, which pushes the frame down the matching browser socket(s).
6. `AppLayout.vue` subscribes once per session (`onMounted`, via `echo().private(...).notification(callback)`) and fans the payload out to two places: `useNotifications.js`'s `applyLiveNotification()` (increments `unreadCount`, prepends to the list — the same shared singleton `NotificationBell.vue` reads) and `useLiveToasts.js`'s `pushToast()` (renders in `NotificationToasts.vue`, bottom-left, auto-dismissing after 6s).
7. The old 60s poll (`useNotifications.js`) still runs, just slowed to 5 minutes — a fallback for a dropped socket, not the primary path anymore.

### 7.4 Highlighted text in notification bodies

`NotificationPresenter::present()` wraps each dynamic value (record name, actor name, module label, etc.) in `<span class="notification-highlight">…</span>` via a `highlight()` helper, escaping the value first with `e()`. The frontend (`NotificationBell.vue`, `NotificationToasts.vue`) renders `title`/`body` with `v-html` instead of text interpolation so that span actually renders. **Any future addition to these translation strings that isn't already escaped through `highlight()` must not be interpolated raw** — `v-html` means an unescaped value here is a real stored-XSS path, not just a display bug.

### 7.5 Mark-as-read from the toast

Independent of the bell dropdown's existing click-to-read (§1-§6), the toast (`NotificationToasts.vue`) now also calls `markRead()` on click (item body or the × close button), and on a **1.8s continuous hover** (a per-toast `setTimeout`, cleared on `mouseleave` so a quick pass-by doesn't count). Both go through the same `useNotifications.js` singleton `markRead()` already used by the bell, so bell/toast state never drifts apart — there's no separate "toast read" state to keep in sync.

### 7.6 Local dev environment

Two more long-running processes are required beyond `serve`/`vite`: a queue worker (`php artisan queue:listen`) and Reverb itself (`php artisan reverb:start --debug`). `composer.json`'s `dev` script now starts all of them together via `concurrently`. Env vars added to `.env`/`.env.example`: `BROADCAST_CONNECTION=reverb`, `REVERB_APP_ID`/`REVERB_APP_KEY`/`REVERB_APP_SECRET` (generated once, arbitrary values — not secrets shared with any third party since Reverb is self-hosted), `REVERB_HOST`/`REVERB_PORT`/`REVERB_SCHEME` (what the server binds to — loopback locally), and `VITE_REVERB_*` (what the browser connects to — baked into the frontend bundle at build time, so **changing them requires restarting `npm run dev`**, and additionally clearing Vite's dependency pre-bundle cache, `node_modules/.vite`, if `@laravel/echo-vue`/`laravel-echo`/`pusher-js` were already optimized before the env vars existed — Vite doesn't auto-invalidate that cache on `.env` changes).

### 7.7 Not yet done — production deployment

Reverb needs a persistent process in production (a systemd unit, `Restart=always`), a reverse-proxy path exposing it as `wss://` on the public domain (Nginx/Apache config outside this repo), and a `deploy.sh` step to restart that process on deploy (`systemctl restart`, since unlike the queue worker there's no graceful `artisan reverb:restart` signal). None of this is wired up yet — deliberately deferred pending the actual server's reverse-proxy config.

### 7.8 Files touched (this addition)

**Backend**
- `app/Notifications/BaseAppNotification.php` — `ShouldBroadcast` + `toBroadcast()`
- `app/Support/NotificationPresenter.php` — `highlight()` wrapping
- `config/broadcasting.php`, `routes/channels.php` (new)
- `bootstrap/app.php` — `channels:` wired into `withRouting()`
- `composer.json` — `laravel/reverb` dependency, `dev` script gains `reverb:start`
- `.env`, `.env.example` — Reverb/broadcast env vars

**Frontend**
- `resources/js/bootstrap.js` — `configureEcho()`
- `resources/js/Layouts/AppLayout.vue` — subscribes to the user's private channel, fans out to bell + toast
- `resources/js/Composables/useNotifications.js` — `applyLiveNotification()`, slower fallback poll
- `resources/js/Composables/useLiveToasts.js` (new)
- `resources/js/Pages/Components/Globals/NotificationToasts.vue` (new)
- `resources/js/Pages/Components/Globals/NotificationBell.vue` — `v-html` for title/body
- `resources/scss/globals.scss` — `.notification-toasts`, `.notification-highlight`
- `package.json` — `laravel-echo`, `pusher-js`, `@laravel/echo-vue`

## 8. Manual verification

- Full test suite (273 tests) passing throughout — this feature added no new test files, so regressions were caught via `ModuleCrudTest`/`RelationshipManyToOneLinkingTest` (which exercise `AuditObserver`/`RelationshipService` generically) and the existing `InviteAcceptanceTest`/`ImpersonationAuditTest`/`PreferencesControllerTest` suites.
- `php artisan tinker` round-trips for each of the 7 types: confirmed a `notifications` row is created, the unread count increments, and mail is (or isn't) queued based on the recipient's `wantsEmailFor()` result.
- Reproduced the locale bug directly: dispatched a notification with the *actor's* locale set to German, then read it back via `NotificationPresenter::present()` in both English and German — confirmed a clean, single-language sentence either way, with no leftover translation baked into the stored `data`.
