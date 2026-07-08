# What Happens to Your Session in Cubrel?

In this article we discuss how sessions work in Cubrel, what you'll see if one runs out, and why your work is safe either way.

## What is a session

A session is simply Cubrel remembering that it's you. When you log in, Cubrel starts a session and keeps you signed in as you move between pages, so you don't have to login every time you open a record or run a search.

## Across different devices or browsers

Using Cubrel on your tablet and your laptop at the same time, for example, is completely safe. Each device keeps its own separate session, so what happens on one has no effect on the other. If your laptop's session refreshes or expires, your tablet stays exactly as it was, and the other way around. You can sign in and out of any device independently without worrying about it disrupting your work anywhere else.

## Across tabs in the same browse

Tabs aren't as independent as devices are. Every tab you have open in the same browser shares a single sign-in, so if you sign out in one tab, every other tab open in that same browser is signed out too, even one that's sitting on a page you haven't touched in a while. If you go back to one of those other tabs afterward and try to do something, it'll simply ask you to sign in again.

This only applies within one browser on one device. Signing out on your laptop doesn't touch your phone, or any other device you're signed in on, that's the independence described above. It's specifically multiple tabs sharing the same browser that share the same sign-in.

## Why sessions expire at all ?

Automatically signing out idle sessions is a the technical safeguard that helps us protect your data in line with the GDPR.
So after a period of no activity, Cubrel automatically signs you out.

Right now, that idle window is set by default to **8 hours**. That's long enough to cover a full workday without ever noticing it, as long as you keep coming back to the tab.

## Can you override the default session behavior?

Yes. When you log in, check the **"Keep me signed in"** box and Cubrel will remember you on that device, separately from the default 8-hour session. Instead of being signed out after a period of inactivity, you'll stay signed in for a long time, around 400 days. You can close the browser completely, come back weeks later, and land straight back in Cubrel without logging in again.

#### Few notes

- **It's not truly permanent.** It's a very long window (about 400 days) that resets every time you use Cubrel, so in practice it rarely runs out on its own.
- **Logging out clears it.** If you sign out explicitly, this is wiped and you'll need your password again next time, just like a normal session.
- **Only use it on your own device.** Because it keeps you signed in for so long, anyone who uses that browser afterward could open Cubrel as you. Skip it on shared or public computers.

#### Admin controls two things from Settings:

- **The idle window.** The default is 8 hours, but an admin can shorten or lengthen it (min: 30 minutes and max: 24 hours) to match your organization's security needs. Whatever they set applies to everyone and becomes the standard timeout your sessions fall back to.
- **The "Keep me signed in" option.** An admin can turn this off entirely for the whole organization. When it's off, the checkbox disappears from the login screen for everyone, and all users fall back to the idle window above, with no way to opt into staying signed in longer.

## As long as you're working, you'll never see it

While you have Cubrel open in a visible browser tab, it quietly checks in with the server every few minutes in the background, you won't see or feel this happening at all. As long as the tab stays open and visible on your screen, this keeps resetting the 8-hour clock, so **a session you're actively using essentially never expires**, no matter how long you've had it open. This only stops if you switch away to a different tab or minimize the window for an extended period, in other words, if you genuinely walk away.

## If you come back after being away for a while

Say you've had Cubrel open on a record, stepped away for a while, and come back later to finish editing and hit Save. Depending on what happened while you were away, one of two things can happen:

### 1. "Your session was refreshed, please save again"

Occasionally, a security check on your session can fall out of sync even though you're still fully signed in. This isn't really about how long you were away, it can happen if something changes about your session in the background, for example, if an administrator briefly used your account for support purposes while you had a page open elsewhere. In this case:

- You'll see a small notification banner saying your session was refreshed.
- You stay exactly where you were, same record, same edits still sitting in the fields, nothing is cleared or reloaded.
- Just click Save one more time and it goes through normally.

### 2. "Your session has expired, please sign in again"

If you were away long enough that you were actually signed out (past the idle window described above), Cubrel will take you to the login page. This is exactly like normal, but two things make it painless:

- Your unsaved edit isn't thrown away. Cubrel remembers what you were in the middle of typing.
- After you log back in, you're taken straight back to the exact record you were on, not the dashboard, and it reopens already in edit mode with your change filled back in, ready to save.

So even in the "you got logged out" case, you don't lose your work or have to go hunting for the record again. Just log in and pick up where you left off.

## What isn't covered

- If you close the browser tab (or your laptop dies, etc.) **before** ever clicking Save, that unsaved edit is gone, same as it would be for any unsaved form on any website. The recovery above only kicks in at the moment you actually try to save.
- If your browser itself warns you "you have unsaved changes, are you sure you want to leave this page?", that's a separate, standard safety check. It's just Cubrel double-checking before you navigate away with unsaved edits still on screen; it's not related to your session expiring.

## In short

| Situation                                  | What you'll see                             | Your work                                                            |
| ------------------------------------------ | ------------------------------------------- | -------------------------------------------------------------------- |
| Actively using Cubrel (tab open, visible)  | Nothing, session just stays alive           | Never at risk                                                        |
| Session refreshed in the background (rare) | Quick "please save again" notice, same page | Fully intact, still on screen                                        |
| Away long enough to be fully signed out    | Sent to login                               | Restored automatically once you log back in and return to the record |
| Tab closed / browser crashed before saving | N/A                                         | Lost, like any unsaved form                                          |
