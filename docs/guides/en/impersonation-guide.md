# Impersonating a User in Cubrel

In this article we cover who can impersonate whom, how to start and stop a session, and where every impersonation session gets logged.

## What impersonation is for

Impersonation lets a super admin sign in *as* another user, seeing exactly what they see, useful for troubleshooting an issue from that person's own point of view without asking them to share their screen. It's restricted and fully logged, see [Who can impersonate](#who-can-impersonate) and [Impersonation is always transparent, never hidden](audit-trail-guide.md#impersonation-is-always-transparent-never-hidden) in the Audit Trail guide.

## Who can impersonate

Only **super admins** can start an impersonation session, and only on regular users:

- You can't impersonate yourself.
- You can't impersonate another admin or super admin.
- You can't impersonate a user whose status isn't **Active**.

## Starting a session

Open the target user's record (**Users**) and choose **Login as** from the action menu. You land on their Dashboard, seeing the app exactly as they would. They also get a notification that they're being impersonated, this never happens silently.

## While impersonating

A yellow banner stays fixed at the bottom of the screen the entire time, showing who's impersonating whom, so it's never ambiguous which account is actually acting.

## Ending a session

Click the exit button on the banner to return to your own account. If a session is somehow left open (for example, the browser closes without exiting), Cubrel automatically closes it out once the underlying session expires, so sessions don't linger open indefinitely.

## Where sessions are logged

**Settings > Impersonation Sessions** lists every session that's ever happened: who impersonated whom, from what IP address, when it started, when it ended (or whether it's still ongoing), and how long it lasted.

Any action taken while impersonating also shows up in that record's own Audit Trail, marked with a badge naming the real super admin behind it, so a record's history always shows who was really at the keyboard.

## In short

| Question | Answer |
| --- | --- |
| Who can impersonate someone? | Super admins only |
| Who can be impersonated? | Any active, non-admin user |
| How do I start impersonating someone? | Their record's action menu > **Login as** |
| Is the impersonated user notified? | Yes, always |
| How do I know I'm impersonating? | A persistent banner at the bottom of the screen |
| How do I stop? | Click exit on the banner |
| What if I forget to exit? | The session auto-closes once it goes stale |
| Where can I review past sessions? | **Settings > Impersonation Sessions** |
| Do impersonated actions show up in the Audit Trail? | Yes, marked with who was really impersonating |
