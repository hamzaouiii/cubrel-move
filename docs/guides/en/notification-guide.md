# Notifications

In this article we cover where notifications live in Cubrel, what actions trigger them, and how to control which ones also get emailed to you.

## Where to find them

A bell icon sits in the top bar, next to your profile menu. A small red badge on it shows how many notifications you haven't read yet. Clicking it opens a dropdown with your most recent notifications, newest first — each one shows an icon, a short description, and how long ago it happened.

New notifications arrive live while you have the app open — the bell's badge updates the moment something happens, no reload needed, and a small popup also appears in the bottom-left corner of the screen for a few seconds so you don't have to be looking at the bell to notice it.

## What triggers a notification

| Event | When you're notified |
| --- | --- |
| **A record is assigned to you** | Someone changes a record's Owner field to you (on any module — Leads, Deals, Accounts, etc.), or creates a new record with you set as the owner. |
| **You're invited to a meeting** | Someone adds you as an attendee on a Meeting record. |
| **A task is due soon** | One of your tasks is due within the next 24 hours and isn't marked completed yet. Checked automatically once an hour. |
| **A user invite you sent was accepted** | Someone you invited finishes creating their account. |
| **A user invite you sent expired** | An invite you sent goes unanswered past its expiry date. Checked automatically once an hour. |
| **Activity on a record you own** | Someone else edits, deletes, or links a new activity (a Task, Call, Meeting, or Note) to a record you own. You won't be notified about your own changes to your own records. |
| **Your account was accessed** | A super admin impersonates your account (signs in as you) to help troubleshoot something. |
| **A record you own was converted** | Someone runs a conversion rule (see [Conversion Rules](conversion-guide.md)) on a record you own — manually or automatically — turning it into a new record in another module. You won't be notified if you're the one who ran it yourself. |
| **Your change triggered an automatic conversion** | You edit a record in a way that satisfies an automatic conversion rule's conditions, and it fires on its own. Since this happens silently in the background, this is how you find out. |

Clicking a notification (where applicable) takes you straight to the relevant record or page, and marks it as read. "Mark all as read" at the top of the dropdown clears every unread notification at once. The bottom-left popup works the same way — click it to jump to the record and mark it read, or just hover over it for a couple of seconds and it'll mark itself as read without navigating anywhere.

## Notification channels

Each of the nine event types has two independent on/off switches: whether it shows up **in-app** (the bell and the popup) and whether it's **also emailed** to you. Turning off in-app for a type doesn't just hide it — it stops being delivered there entirely, the same as turning off email stops it being sent.

Go to **Preferences > Notifications** to see both toggles for each event type, side by side. By default:

- **Emailed by default**: your account was accessed (impersonation), an invite you sent was accepted, an invite you sent expired.
- **Email off by default**: record assigned, meeting invite, task due soon, activity on your records, a record you own was converted, your change triggered an automatic conversion.
- **In-app on by default**: all nine types.

Turn any of them on or off to suit how closely you want to follow along — for example, if you own a lot of records and don't want an inbox full of "activity on your record" emails, leave that one off and just check the bell when convenient. If you'd rather never miss a task deadline even when you're not logged in, turn on email for task due soon.

These are personal to your account — changing them for yourself doesn't change what anyone else sees or receives. If you leave a toggle exactly as shown, it follows your organization's default setting instead, which an admin controls separately (Settings > Notifications) — so your organization can set sensible defaults for everyone, and you can still fine-tune any of them for yourself.

## In short

| Question | Answer |
| --- | --- |
| Where do notifications show up? | The bell icon in the top bar, on every page, plus a popup in the bottom-left corner as they happen. |
| Do I need to turn notifications on? | No — all nine types are on in-app by default; you can turn any of them off. |
| How often does the unread count refresh? | Instantly, as each notification happens. |
| Can I get these by email too? | Yes, per type, in Preferences > Notifications. |
| Which are emailed by default? | Impersonation, invite accepted, invite expired. |
| Can my organization set different defaults? | Yes — an admin sets organization-wide defaults in Settings > Notifications; your personal Preferences override those for your own account. |
| Will I be notified about my own actions? | No — actions you take on your own records never notify you. |
| How do I clear my notifications? | Click one (in the bell or the popup) to mark it read, hover over a popup for a couple seconds, or use "Mark all as read" for everything at once. |
