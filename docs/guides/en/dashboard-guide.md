# Understanding the Dashboard in Cubrel

In this article we cover what the Dashboard shows, the kinds of widgets you can add, and how layout, filtering, and refreshing work.

## Your own dashboard

Every user has their own **Dashboard**, there's no separate shared or team dashboard. Changes you make to your layout only affect what you see. New users start out with a sensible default layout based on their role (an admin's starting layout differs from a read-only user's), but it's yours to rearrange from there.

## Widget types

Click **Add Widget** to see what's available:

- **Metric** — a single number for a module: a count, sum, or average of a field.
- **Records over time** — a line or bar chart of a field's values over time.
- **Breakdown** — a donut or bar chart grouping records by a field (e.g. Deals by Stage).
- **Record list** — a compact table of records from a module.
- **People** — a leaderboard ranking people by a summed, counted, or averaged field.
- **My records** — a simple widget showing records assigned to you across every module.

The first five are **configurable**: you pick the module and, optionally, filters (for example, "open Deals only"). **My records** isn't configurable, it's a fixed widget you can add or remove but not tune.

By default, a widget only shows records you own. If you're an admin, some widgets can be set to show every record in the module instead of just yours.

## Editing your layout

Click the pencil icon to enter edit mode. From there you can:

- **Drag widgets to reorder** them.
- **Edit** a widget's configuration (module, filters, chart type where relevant).
- **Remove** a widget.
- Choose a **width** for a widget when you add it (it stays a fixed width from then on, there's no free resizing later, just reordering).

Widgets flow into a grid automatically, similar to a masonry layout, there's no manual row/column placement. Save your changes when you're done, or cancel to discard them.

## Refreshing data

Nothing on the Dashboard auto-refreshes in the background. Click **Refresh all** to re-fetch every widget's data on demand.

## In short

| Question | Answer |
| --- | --- |
| Is there one dashboard per user, or a shared one? | One per user, personal only |
| What widget types exist? | Metric, Records over time, Breakdown, Record list, People, and the fixed My Records widget |
| Can a widget be scoped to a module and filtered? | Yes, for every type except My Records |
| Does a widget show all records or just mine? | Just yours by default; some widgets can be switched to show everyone's, for admins |
| Can I reorder widgets? | Yes, drag-and-drop in edit mode |
| Can I resize a widget after adding it? | No, width is set when you add it |
| Does the dashboard auto-refresh? | No, use **Refresh all** |
