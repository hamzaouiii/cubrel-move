# Understanding List Filters in Cubrel

Every list view in Cubrel, Deals, Contacts, Invoices, any module, can be narrowed down with a filter. This article covers how to build one, the difference between a private filter and one shared with your whole team, the built-in filters Cubrel ships with, and who is allowed to edit or delete a saved filter.

## Opening the filter builder

At the top of any list, click **Add Filter** (or the filter dropdown if one is already applied) to open the **Filter Builder**. From here you can either pick a filter someone already saved, or build a new one from scratch.

## Building conditions

A filter is one or more conditions, each a **field**, an **operator**, and a **value**. Click **Add condition** to add a row, and pick whether the filter needs to match **ALL** of the conditions or **ANY** of them.

The operators available depend on the field's type. Text fields offer things like *contains* and *starts with*. Numbers and dates offer *greater than*, *less than*, *before*, *after*, and *between* (which asks for a From and a To value). Dropdown and multi-select fields offer *is any of*. You can't pick an operator that doesn't make sense for the field, the invalid ones simply aren't shown.

This is the same condition-builder used for automatic conversion rules. See the [Converting Records guide](conversion-guide.md) if you've used it there already.

## Saving a filter

Give it a name and click **Save filter**. By default a filter you save is private, only you can see and apply it. Turn on **Share with everyone** before saving to make it available to the whole team instead. Anyone can then apply it, though only you (or an admin) can still edit or delete it.

Applying a filter narrows the list immediately. Clicking **Clear Filter** removes it and shows every record again.

## Editing and deleting

Open a filter from the dropdown and use **Edit filter** to change its name, sharing, or conditions, or **Delete filter** to remove it for good. Cubrel asks you to confirm first, since deleting a filter can't be undone. You can manage any filter you created yourself, and admins can manage anyone's. A filter someone else made and shared can be applied by you, but not changed or deleted.

## Built-in filters

Some modules come with a handful of filters already set up: **My Records** on most modules, plus module-specific ones like **Open Orders**, **Unpaid Invoices**, **Won Deals**, or **Active Products**. These are marked as system filters, every user can see and apply them, but nobody, not even an admin, can edit or delete them. **My Records** is pinned separately from the rest, so it's always the fastest way to get back to just what you own.

## Using a filter outside the list view

A saved filter isn't only for the list screen. If you're using the [REST API](rest-api-guide.md), the `filter` parameter on a list endpoint takes the slug or ID of a filter you already saved in the app, not a raw filter expression. Anything you've built and saved here becomes reusable from outside Cubrel too.

## In short

| Question | Answer |
| --- | --- |
| Where do I build a filter? | Click **Add Filter** on any list to open the Filter Builder |
| Can I filter on any field? | Only fields the condition builder allows for that module, with operators matched to the field's type |
| What's the difference between a private and a shared filter? | Private is visible only to you; shared is visible and applicable to everyone, though still only editable by its owner or an admin |
| Can I edit someone else's shared filter? | No, only its owner or an admin can |
| What are the built-in filters like "My Records"? | System filters Cubrel ships with, usable by everyone, editable by no one |
| Can I use a saved filter from the REST API? | Yes, pass its slug or ID as the `filter` parameter |
