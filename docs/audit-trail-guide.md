# Understanding the Audit Trail in Cubrel

In this article we cover what Cubrel's Audit Trail actually records, where to find it, what impersonation transparency means in practice, and what still falls outside of it today.

## What is the Audit Trail

Every time a record is created, updated, or deleted anywhere in Cubrel, that change is logged automatically, no setup required. Each entry captures **who** made the change, **what** changed, and **when**, so you always have a record of how your data got to its current state.

## Where to find it

There are two ways to look at this history, depending on whether you're looking at one record or everything at once.

### On a single record

Open any record and use the action menu (the small dropdown next to Edit/Save) to pick **"View History"**. This opens a window showing every change ever made to that specific record, most recent first, with each changed field shown as its old value and its new value side by side.

### Across your whole organization

Admins can go to **Settings > Audit Trail** for the full, unfiltered picture, every change to every record, across every module, in one searchable list. You can filter it by module, by user, by the type of action (created/updated/deleted), and by date range.

## What each entry shows you

- **When** it happened, shown in your own configured date/time format.
- **Who** did it.
- **What** changed, using the same field names and labels you see on the record itself, not raw internal field names.
- For dropdown fields, the actual option label (e.g. "Closed Lost"), not the internal value behind it.
- For fields that reference another record (like a record's owner), the other record's name, not its internal ID.

## Impersonation is always transparent, never hidden

Occasionally, a root user may need to sign in *as* another user, for example, to help troubleshoot something from that person's exact point of view. Whenever that happens and a change is made during that session, the Audit Trail always shows it plainly: the entry is marked with a small badge reading **"as [Root's name]"**, right next to the action. Nobody looking at a record's history is ever left wondering whether the person shown is really who made the change.

Separately from the Audit Trail, **Settings > Impersonation Sessions** (visible to all admins) lists every impersonation session on its own: who signed in as whom, from what IP address, when it started and ended, and how long it lasted. An ongoing session (one that hasn't ended yet) is clearly marked as such.

## A few things are deliberately left out

- **Auto-calculated totals aren't logged as edits.** Fields like Total, Subtotal, Tax, and Discount Amount are recalculated automatically whenever line items change, they aren't something anyone directly typed in, so they're excluded to keep the history focused on changes a person actually made.
- **Large bulk edits are summarized, not itemized.** If you bulk-edit records you explicitly selected, that batch is still traceable back to each individual record's own history. But if you instead used a "select everything matching this filter" bulk action without picking specific records, that batch only shows up as one summary entry in the global Audit Trail; it won't appear inside any single record's own history.

## When a record is deleted

The Audit Trail keeps a record of *what* was deleted (its name, so the entry stays meaningful even after the record itself is gone), along with who deleted it and when. What it doesn't do yet is let you bring the record back. Restoring deleted records from this history is on the roadmap, but isn't available today, once deleted, a record is gone for good.

## Who can see what

- **A record's own history** is visible to anyone who can already open that record, same as viewing the record itself.
- **The full Audit Trail** and **Impersonation Sessions** pages, the unfiltered view across everything, live under Settings and are limited to admins.

## In short

| Question | Answer |
| --- | --- |
| Do I need to turn this on? | No, every module is tracked automatically. |
| Where do I see one record's history? | Its action menu > "View History" |
| Where do I see everything? | Settings > Audit Trail (admins) |
| Where do I see impersonation sessions themselves? | Settings > Impersonation Sessions (admins) |
| Is impersonation ever hidden? | No, it's always flagged, to anyone who can see that entry |
| Are recalculated totals logged? | No, only fields a person actually changed |
| Can I restore a deleted record from here? | Not yet, its name is kept, but the record itself is gone |
