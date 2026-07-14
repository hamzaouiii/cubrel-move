# Importing Records into Cubrel

In this article we cover how to bring outside data into Cubrel with the Import wizard, what file formats it accepts, how to match your columns to Cubrel's fields, and what happens when a row doesn't quite fit.

## What is the Import wizard

The Import wizard lets you create or update many records at once from a CSV or JSON file, instead of entering them one by one. It walks you through uploading a file, matching its columns to the right fields, and reviewing what happened once it's done.

## Where to find it

Open any module's list view (Contacts, Deals, whichever module you want to import into) and click the small dropdown arrow next to **Create**. **Import** is one of the options in that menu, alongside Export and the other list actions.

## Supported files

- **CSV**, comma or semicolon separated. Cubrel detects which one your file uses automatically, but you can override it during mapping if it guesses wrong.
- **JSON**, as a list of records, for example `[{"name": "Acme Corp", "email": "info@acme.com"}, ...]`. A single JSON object isn't accepted, it needs to be a list, even if it only contains one record.
- The file's extension has to actually be `.csv` or `.json`, renaming a file doesn't change what's really inside it.
- Up to **10MB** and **50,000 rows** per file. Larger exports should be split into smaller batches.

## Step by step

### 1. Upload

Drag and drop your file onto the wizard, or click it to browse for one instead.

### 2. Map your columns

You'll see every column detected in your file, with a sample value from the first row, next to a dropdown where you choose which Cubrel field it should fill in, or **"Don't import"** to skip that column entirely. Any field that's required in Cubrel has to be mapped to something before you can continue.

### 3. Avoid creating duplicates (optional)

If you want the import to update existing records instead of creating duplicates, pick a field under **"Match existing records on"**, Email is a common choice. Any row whose value matches an existing record updates that record instead of creating a new one. If you leave this unset, every row in your file becomes a brand new record.

### 4. Confirm and start

Review the summary, how many columns are mapped and what you're matching on, then start the import.

- **Small files (200 rows or fewer)** finish immediately, you'll land straight on the results.
- **Larger files** process in the background with a progress bar. You won't be able to close the wizard until it finishes, so keep the tab open while it runs.

### 5. Review the results

You'll see how many records were created, how many were updated, and how many were skipped. If anything was skipped, the reason is listed right there, for example a required field that was empty, or a value that didn't match anything expected, no separate download needed.

Once you close the wizard, your list refreshes automatically so newly imported records show up right away.

## Getting your columns to match

- **Dropdown and status fields** (things like Deal Stage or Lead Status) understand the text you see on screen, in any of Cubrel's supported languages, not just the raw internal value. So a column full of "Closed Won" works, and so would its translated equivalent in another language.
- **Yes/No fields** accept common words like `yes`, `no`, `true`, `false`, `1`, `0`, as well as Cubrel's own Yes/No wording in any supported language.
- **Dates** are read flexibly (most common formats work), but when in doubt, a clear format like `2026-07-15` is always safe.
- **Numbers** should be plain digits, strip out currency symbols like `$` or `€` before importing, commas as thousand separators are fine.

## What can't be imported yet

- **Linked record fields**, things like a Deal's Company or a record's Owner, since matching a plain text value to the right linked record reliably isn't built yet.
- **Address fields**, since they're made up of several parts (street, city, state, etc.) that don't map cleanly from a single column.

Both are simply left off the list of fields you can map to for now, everything else on a module is fair game.

## In short

| Question | Answer |
| --- | --- |
| Where do I start an import? | List view > dropdown next to Create > Import |
| What files can I use? | CSV (comma or semicolon) or JSON (a list of records) |
| What's the size limit? | 10MB, 50,000 rows |
| Can I avoid creating duplicates? | Yes, pick a "match on" field like Email during mapping |
| What happens to unmapped required fields? | You can't start the import until they're mapped |
| What happens to bad rows? | They're skipped and listed with a reason, the rest still import |
| Can I import linked records or addresses? | Not yet |
| Does the list update after importing? | Yes, automatically once you close the wizard |
