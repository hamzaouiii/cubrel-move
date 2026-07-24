# Layouts

A module's fields have to be arranged somewhere — on its list view, its
record page, its related-records panels. That arrangement is a **layout**.
This guide covers the different layouts a module has, what each one
controls, and how to edit them.

For what a field is and how to create one, see the [Fields guide](fields-guide.md).

## Where to find them

Layouts are edited at **Settings → Modules → [module] → Layouts**, which
opens a small hub linking to each layout type below. Every module has its own
set — there's exactly one of each layout type per module, shared by everyone
who can see that module (layouts aren't per-user or per-role).

Each editor works the same basic way: an "Available Fields" (or "Available
Relationships") sidebar on one side, and the layout itself on the other. Drag
an item in to add it, drag it back out to remove it, and drag within the
layout to reorder. Changes aren't saved until you click **Save Layout**, and
a **Reset** button reverts to the previously saved version.

## The layout types

### List

Controls the columns shown in the module's list/table view. There's no
grouping here — just a flat, ordered set of columns.

### Record

Controls the layout of the record detail page. Fields are organized into
named **sections** — think of a section as a labeled group of fields shown
together, like a card. You can add, rename, reorder, and remove sections, and
drag fields between them. Every field can only appear once across the whole
layout, so once you've placed a field, it drops off the "available" list
until you remove it from wherever you put it.

Two special sections are available on modules where they apply, and each can
only be added once:

- **Line Items** — on modules with line items enabled (quotes, orders,
  invoices, or any custom module you've turned this on for), this section
  pulls its own field list from the line item structure rather than the
  module's own fields.
- **Attendees** — on Meetings, this section is auto-generated with no
  configurable fields of its own.

A record layout always needs at least one section — the last remaining
section can't be removed.

### Related Panels

Controls which [relationships](relationships-guide.md) show up as panels on
the record page's Related tab, and in what order. Relationships are arranged
into up to two columns (side by side), each holding an ordered list of
relationship panels.

For each relationship in the layout, you can also choose a handful of extra
fields from the *related* module to display in that panel's header — for
example, showing a linked Contact's email and phone number right in the
Deals panel, without having to open the Contact.

If a module has no relationships defined yet, this editor shows a prompt to
create one instead of the usual drag-and-drop view — there's nothing to
arrange until at least one relationship exists.

### Linking Panel

Controls the columns shown in the record-search window you get when linking
an existing record into a relationship (see
[Customizing what shows up, and what columns you search by](relationships-guide.md#customizing-what-shows-up-and-what-columns-you-search-by)
in the Relationships guide). It works like the List layout, with one
addition: each column has its own **Sortable** checkbox, letting you decide
which of the visible columns can be used to sort that search window.

### Line Item Mapping

Only relevant for modules with line items enabled. Controls which fields
appear on the create/edit line-item form, in what order, and — for each one —
whether it's filled in manually or **auto-filled** from a field on the line
item source module (usually Products). For example, a line item's unit price
can auto-fill from the linked product's price the moment it's selected,
saving you from re-typing it.

## Fields removed from a module clean up their layouts automatically

If you delete a custom field (see the [Fields guide](fields-guide.md#deleting-a-field)),
Cubrel automatically removes it from any list, record, or linking-panel
layout it appeared in — you won't end up with a layout silently pointing at a
field that no longer exists.

## A related but separate feature: PDF Templates

Invoices, quotes, and orders can also be given a PDF layout, used when
generating a PDF for that record — but that's configured separately, under
**Settings → PDF Templates**, not from a module's Layouts tab.

## In short

| Question | Answer |
| --- | --- |
| How many layouts does a module have? | One of each type: List, Record, Related Panels, Linking Panel, and (if it has line items) Line Item Mapping. |
| Are layouts different per role or per user? | No — one layout per module per type, shared by everyone. |
| Can I group fields into sections? | Yes, on the Record layout. Other layouts are flat lists. |
| How many columns can a Related Panels layout have? | Up to two. |
| What happens to a layout when I delete a field it uses? | The field is removed from the layout automatically. |
| Can I control column widths? | No — layouts control which fields/relationships appear and their order, not sizing. |
