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

## 7. Manual verification

- Full test suite (273 tests) passing throughout — this feature added no new test files, so regressions were caught via `ModuleCrudTest`/`RelationshipManyToOneLinkingTest` (which exercise `AuditObserver`/`RelationshipService` generically) and the existing `InviteAcceptanceTest`/`ImpersonationAuditTest`/`PreferencesControllerTest` suites.
- `php artisan tinker` round-trips for each of the 7 types: confirmed a `notifications` row is created, the unread count increments, and mail is (or isn't) queued based on the recipient's `wantsEmailFor()` result.
- Reproduced the locale bug directly: dispatched a notification with the *actor's* locale set to German, then read it back via `NotificationPresenter::present()` in both English and German — confirmed a clean, single-language sentence either way, with no leftover translation baked into the stored `data`.
