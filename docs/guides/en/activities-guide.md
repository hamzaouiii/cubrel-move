# Understanding Activities in Cubrel

In this article we cover what Tasks, Calls, Meetings, and Notes have in common, where their shared timeline shows up on other records, how to add and complete them from there, and what makes Meetings different from the rest.

## What is an activity

**Tasks**, **Calls**, **Meetings**, and **Notes** are modules like any other — each has its own list view and record page — but they also share a second job: showing up on the **Activity** timeline of the records they relate to. Cubrel calls this pair of behaviors "activity" and "has activity":

- An **activity module** (Tasks, Calls, Meetings, Notes) is something you *do* in relation to other records — call a Lead, schedule a Meeting with an Account, leave a Note on a Deal.
- A **has-activity module** (Leads, Accounts, Contacts, Deals, Support Cases, Quotes, Orders, Invoices) is a record that *collects* those activities, and gets a timeline panel to show them.

## The Activity timeline

Open any record from a has-activity module and you'll see a collapsible **Activity** panel beneath the record header. It has three tabs:

- **All** — everything, activities and field changes together.
- **Activity** — just the linked Tasks, Calls, Meetings, and Notes.
- **Changes** — just the field-level edit history (the same history you'd see from "View History" — see the [Audit Trail guide](audit-trail-guide.md)).

Whichever tab you leave it on is remembered the next time you open the panel.

Entries are shown newest-first on a connecting timeline, each with an icon for its type and a relative timestamp ("2h ago", "Today 14:30", "Yesterday 09:12", or a plain date once it's further back).

## Adding an activity to a record

Click **Add** at the top of the panel to open a dropdown of every activity type available (Task, Call, Meeting, Note). Pick one and it opens that module's normal create form — fill it in and save, and it's automatically linked to the record you started from. No separate "link" step needed.

## Completing a task right from the timeline

A Task entry in the timeline shows a live checkbox. Checking it off updates the Task immediately, without leaving the page or opening the Task itself.

## Linking an activity to more than one record

An activity isn't limited to a single parent. A Meeting about a deal, for example, can be linked to the Deal itself *and* to the Account it belongs to, and it'll show up on both of their timelines. Add more links the same way you would for any relationship, from the record's **Related** tab.

By default, these activity links don't also show up as their own panel on the Related tab, since the timeline already covers them, that would just be showing the same thing twice. If you'd rather see them there too, an admin can add that panel back in through the module's Layouts settings.

## Meetings: a special case

Everything above works the same for Tasks, Calls, Meetings, and Notes. Meetings additionally have their own **Attendees** list, tracking *who's* coming and how they responded, on top of (not instead of) the general linking described above.

### Every meeting starts with an organizer

The moment you create a Meeting, its owner is automatically added as an attendee with the role **Organizer**, already marked **Accepted**. Only one attendee can be the Organizer at a time; picking a new one automatically moves the previous one down to **Required**.

### Adding attendees

From a Meeting's record page, use **Add Attendee**. You can add:

- **Internal** attendees — search and pick from your team, or from Contacts or Leads. Their name and email come from the linked record.
- **External Guest** attendees — anyone outside your organization. Type in their name, email, and role directly; email is required, since it's the only way Cubrel has to reach them.

You can mix both in the same batch before saving, and each gets its own **Role**: Organizer, Required, or Optional.

### Tracking responses and attendance

Each attendee carries two independent statuses:

| RSVP | Attendance |
| --- | --- |
| Invited | *(not recorded)* |
| Accepted | Attended |
| Declined | No-show |
| Tentative | |

RSVP tracks whether they're coming; Attendance (filled in after the meeting) tracks whether they actually showed up. Both show as colored badges on the attendee list — hover a row to edit either one. Once you're done, **Mark all attended** sets everyone whose attendance hasn't been recorded yet to Attended in one click, so you're not clicking through the list one by one after a meeting with a full house.

Attendees are listed Organizer first, then Required, then Optional. Internal attendees show the color of the module they came from (Contact, Lead, or User); external guests show up in neutral gray.

## What's tracked automatically

Everything on the Activity timeline, and every attendee added, removed, or bulk-marked-attended on a Meeting, is captured the same way as any other change in Cubrel, no setup required. See the [Audit Trail guide](audit-trail-guide.md) for what that covers.

## In short

| Question | Answer |
| --- | --- |
| What counts as an activity? | Tasks, Calls, Meetings, and Notes |
| Where do I see a record's activities? | The Activity panel beneath its record header |
| How do I add one? | "Add" in the panel → pick a type → fill in and save |
| Can I complete a Task without opening it? | Yes, check it off right in the timeline |
| Can an activity relate to more than one record? | Yes, link it to as many as apply |
| What's different about Meetings? | They also have a dedicated Attendees list with RSVP and attendance tracking |
| Who's added to a Meeting automatically? | Its owner, as the Organizer |
| Can I add people outside my organization? | Yes, as external guests, by name and email |
| Is any of this logged? | Yes, automatically, same as everything else in Cubrel |
