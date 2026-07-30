# Managing Dropdown Lists in Cubrel

In this article we cover what a dropdown list is, how to build one, and the extra styling options available for status-type lists.

## What a dropdown list is

A dropdown list is a reusable set of options, each with a label and value, that powers a module's select and status fields. Some lists ship with Cubrel out of the box (a Deal's Type, an Order's Status, a Case's Priority, and so on), and you can create your own from **Settings > Dropdowns**.

A list can be tied to one specific field, or shared and reused across several fields.

## Creating a list

From **Settings > Dropdowns > Create**, give the list a name (Cubrel generates its internal key from that automatically) and add options one at a time, each with a label. New lists start out as plain option lists, without color or icon styling.

## Editing a list

Open any list from the Dropdowns table to rename it, add or remove options, or (for status lists) restyle and reorder them. Save with the button, or **Ctrl+S**.

::: warning
Deleting an option removes it from the list immediately, Cubrel doesn't check whether any existing records are still using that value first. Removing an option only affects what shows up going forward, records that already have the old value keep it.
:::

## Status lists: color, icon, and order

A list used by a status field gets extra controls. Expand an option (the pencil icon) to set its:

- **Color** and **background color**, via a swatch picker.
- **Icon**, via an icon picker.

A live preview shows exactly how the badge will look as you adjust it. Status options can also be **reordered** by dragging them, this controls the order they appear in dropdowns and status pickers throughout the app. Plain (non-status) lists don't have color/icon controls or drag-to-reorder, their order is just the order you added them in.

## In short

| Question | Answer |
| --- | --- |
| Where do I manage dropdown lists? | **Settings > Dropdowns** |
| Can a list be reused across multiple fields? | Yes, or tied to one specific field |
| Do all lists support colors and icons? | No, only lists used by status-type fields |
| Can I reorder options? | Yes, for status lists, by dragging; plain lists keep add order |
| What happens if I delete an option already used on records? | Existing records keep their value, it just won't be selectable again going forward |
| Do system default lists (like Deal Type) look different from custom ones? | No, both work the same way once created |
