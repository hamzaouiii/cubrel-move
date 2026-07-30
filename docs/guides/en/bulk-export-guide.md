# Bulk Actions and Exporting in Cubrel

In this article we cover selecting multiple records at once, the three bulk actions available from a list view, and how exporting works.

## Selecting records

Tick individual checkboxes on a list, or use the header checkbox to select every record on the current page. Once you've selected a full page, a **Select all N records** link appears, letting you extend the selection to every record matching your current search and filters, across every page, not just the one you're looking at.

While "all matching" is selected, you can still deselect specific records you don't want included, they're excluded individually rather than losing the whole selection.

## Mass Update

Update one field at a time across every selected record: pick the field (read-only fields aren't offered), enter the new value, and confirm. Cubrel shows you exactly how many records will be affected before applying the change, and validates the value the same way a normal edit would (a required field still can't be left blank).

## Mass Delete

Delete every selected record in one action. You're shown a confirmation with the record count before anything happens.

::: warning
Deletion is permanent. There's no undo and no recovery once you confirm, review your selection carefully first, especially if you've used "select all matching" rather than picking records by hand.
:::

## Export

Export your selection, or every record matching your current filter, as **CSV** or **JSON**. There's no column picker, exports include every visible field on the module. Exporting a single record also includes its line items, a bulk export of many records doesn't.

Export runs immediately and downloads as a file, there's no waiting for an email or a background job, even for a large selection.

## In short

| Question | Answer |
| --- | --- |
| Can I select records across multiple pages? | Yes, via **Select all N records**, based on your current filter |
| Can I exclude a few records from an "all matching" selection? | Yes, deselect them individually |
| What bulk actions are available? | Mass Update, Mass Delete, Export |
| Can Mass Update change more than one field at once? | No, one field per bulk update |
| Is Mass Delete reversible? | No, it's permanent |
| What export formats are supported? | CSV and JSON |
| Can I choose which columns to export? | No, all visible fields are included |
| Is export immediate or queued? | Immediate download, no queue or email |
