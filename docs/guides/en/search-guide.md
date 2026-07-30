# Searching in Cubrel

In this article we cover how to open global search, what it looks through, how results are grouped, and a couple of things it deliberately doesn't do yet.

## Opening search

Click the search box in the topbar from anywhere in the app, or press **Ctrl+K** (**Cmd+K** on a Mac). Press **Esc**, or click away, to close it again.

## What gets searched

Search only looks at fields an admin has explicitly marked **Searchable** in that module's field settings (**Settings > Fields**) — not every field on a record is included by default. It searches the record's own fields only: it doesn't look inside notes, attachments, generated PDFs, or fields on related records.

Every active module is searched, so results can come from anywhere in the system you have access to. **Users** and **Settings** are excluded from search results unless you're an admin.

::: warning
Search currently doesn't filter by record ownership the way list views do — a result can surface a record even if you wouldn't otherwise be able to open it from that module's list. Treat this as a known gap rather than a permission boundary.
:::

## Typing a query

Start typing and results appear automatically once you've entered 4 or more characters, there's a short pause after you stop typing before it searches. For 1–3 characters, press **Enter** to search manually. A search needs at least 2 characters, anything shorter is rejected.

## Reading the results

Results are grouped by module, each group labeled with that module's name, icon, and color, and each result shows the record's label plus a short snippet pulled from a description-like field. Results aren't ranked by relevance beyond a basic text match, and there's no cap on how many results a single module can return.

Clicking a result takes you straight to that record. Closing or clearing the search box resets it, there's no recent-searches list to come back to later.

## In short

| Question | Answer |
| --- | --- |
| How do I open search? | Click the topbar search box, or press **Ctrl+K** / **Cmd+K** |
| What fields does it search? | Only fields marked **Searchable** on each module |
| Does it search notes, attachments, or PDFs? | No, record fields only |
| Does it search related records? | No, only the record's own fields |
| Are results filtered by what I'm allowed to see? | Module-level yes (Users/Settings hidden from non-admins); record-level ownership, not currently |
| How many characters do I need to type? | 4+ searches automatically; 2–3 needs Enter; under 2 is rejected |
| Is there a search history? | Not currently |
