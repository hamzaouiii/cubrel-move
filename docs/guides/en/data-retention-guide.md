# Understanding Data Retention in Cubrel

Cubrel cleans up after itself. Old notifications, stale audit log entries, expired invites, that kind of thing don't pile up forever. This article covers what gets cleaned up automatically, how long each type of data sticks around by default, and where to change those windows.

## Where to find it

Admins can go to **Settings > System > Data Retention** to see and change every retention window in one place. Nothing here needs a deploy or a restart, a change takes effect the next time the relevant cleanup job runs.

## What gets cleaned up

Seven kinds of data have their own configurable retention window, each measured in days from when the record was created (or, for invites, from when it was resolved):

| Setting | What it covers | Default |
| --- | --- | --- |
| Notification Retention | In-app notifications | 180 days |
| Audit Log Retention | Every logged create, update, and delete across the app | 730 days |
| User Invite Retention | Invites that have already been accepted or revoked | 365 days |
| Failed Job Retention | Background jobs that failed and were logged for debugging | 30 days |
| Setup Token Retention | One-time tokens used during first-time account setup | 90 days |
| Import History Retention | Completed or failed import runs, including the uploaded file | 90 days |
| Abandoned Draft Module Retention | Custom modules left unfinished in the Module Builder | 7 days |

Deleting a record or replacing an image field's value also cleans up the file behind it right away, that one isn't on a timer, it happens the moment it becomes unused.

## Setting a window to 0 or a very low number

A shorter window means Cubrel forgets that data sooner. There's no toggle to turn a category off entirely, the lowest meaningful setting is whatever number of days you're comfortable with. Audit logs in particular are worth thinking about before shortening: once an entry ages out, that history is gone for good.

## Draft modules can also be discarded manually

You don't have to wait out the retention window for an abandoned draft. From the Module Builder, an admin can click **Discard Draft** on an unfinished module to delete it immediately, unsaved fields and all. The 7-day retention setting is just the backstop for drafts nobody remembered to clean up.

## What isn't on a configurable timer

A couple of housekeeping tasks run on a fixed schedule rather than a setting you can change: orphaned image uploads are swept up weekly, and impersonation sessions that got stuck open (say, a browser tab closed mid-session) are reconciled hourly. These aren't user data in the same sense as the table above, so there's nothing to configure.

## In short

| Question | Answer |
| --- | --- |
| Where do I change retention windows? | **Settings > System > Data Retention** |
| Do changes need a deploy? | No, they apply the next time the cleanup job runs |
| What happens when data passes its retention window? | It's permanently deleted, there's no recovery |
| Can I turn off retention for a category? | No, but you can set a long window |
| What cleans up immediately instead of on a schedule? | Files behind a deleted record or a replaced image field |
| Can I discard an abandoned draft module myself? | Yes, with **Discard Draft** in the Module Builder, without waiting for retention to catch it |
