# Fields

Fields are what a record is actually made of. A Deal's amount, a Contact's
email, a Case's priority — each of those is a field. This guide covers the
different kinds of fields Cubrel supports, what you can configure on each one,
and how to create, edit, and remove them.

For how modules and fields fit together, see the [Modules guide](modules.md).

## The fields every module already has

Every module — built-in or custom — comes with five fields you never need to
add yourself: **Name**, **Description**, **Owner**, **Created At**, and
**Updated At**. These can be hidden from a layout if you don't want them
shown, but they can't be deleted — they exist on every record for
consistency.

Modules with line items (quotes, orders, invoices, and any custom module
you've turned line items on for) also get four extra fields you don't create
yourself: **Subtotal**, **Discount Amount**, **Tax Amount**, and **Total**.
These are calculated automatically from the line items on the record, so
they're always read-only.

## Field types

When you create a field, you choose one of these types:

| Type | What it holds |
| --- | --- |
| **Text** | A single line of text. |
| **Long Text** | A multi-line block of text. |
| **Number**, **Integer**, **Decimal** | Numeric values. |
| **Currency** | A monetary amount, shown formatted using your workspace's currency setting. |
| **Percentage** | A number between 0 and 100. |
| **Email** | An email address. |
| **Phone** | A phone number. |
| **URL** | A web address. |
| **Date** | A calendar date. |
| **Date & Time** | A calendar date with a time of day. |
| **Checkbox** | A simple yes/no toggle. |
| **Select** | A single choice from a plain list of options you define. |
| **Status** | A single choice from a list of options, each with its own color and optional icon — shown as a colored badge rather than plain text. |
| **Related Record** | A link to a record in another module (for example, a Case field that points at a Contact). |
| **Address** | A structured address made up of street, postal code, city, state, and country. |
| **Image** | An uploaded image (JPEG, PNG, WEBP, or GIF, up to 2 MB). |

### Select vs. Status

These two look similar — both give you a dropdown of choices — but Status is
the richer of the two. A Status field's options each get their own text
color, background color, and (optionally) an icon, so the value renders as a
colored badge on the record. A Select field's options are plain text with no
color or icon. Use Status for things like a pipeline stage or a
priority level, where color-coding helps at a glance; use Select for a plain
list of choices where that isn't needed.

### Related Record fields

A Related Record field links to another module — you pick which module it
points to when you create the field. This is different from a
[relationship](relationships-guide.md): a relationship connects two modules
generally and shows up as a panel on the Related tab, while a Related Record
field is a single field on a record pointing at one specific linked record
(for example, a Task's "Related To" field).

### Address fields

An address field stores five pieces of information together as one field:
street, postal code, city, state, and country (country is picked from a
searchable list of countries). On the record page it displays as a
multi-line formatted address with a button to copy it to the clipboard; in
list and table views it condenses to a single line.

## What you can configure on a field

Every field has a few settings you can turn on or off, regardless of its
type:

| Setting | What it does |
| --- | --- |
| **Required** | The field must have a value before the record can be saved. (Not available on Checkbox fields.) |
| **Readonly** | The field can't be edited from the record form — useful for fields you want to fill in some other way. |
| **Hidden** | The field is hidden from layouts, but its data is preserved. |
| **Searchable** | The field is included when searching within the module. |
| **Filterable** | The field can be used to filter a list view. |
| **Sortable** | The field can be used to sort a list column. |
| **Default value** | A value that's pre-filled when someone creates a new record. |

Text-like fields (Text, Long Text, Email, Phone, URL) also let you set a
minimum/maximum length and a custom validation pattern, if you want to
document expectations for the field beyond its basic type.

## Creating and editing fields

Where you manage fields depends on whether the module is brand new or already
live:

- **While building a new module**, fields are added on the "Fields" step of
  the [Module Builder](modules.md#creating-a-module-the-module-builder).
  These become permanent, built-in fields of the module once it's deployed.
- **On a module that's already live**, fields are managed from
  **Settings → Modules → [module] → Fields**. Fields you add here work
  exactly like any other field — they show up in layouts, are searchable and
  filterable if you mark them so, and behave identically to a field that
  shipped with the module.

New field names can't collide with the five universal fields, or with the
line-item fields if the module has line items enabled.

### What can't be changed after creation

A field's **name** and **type** are locked in permanently once the field is
created — there's no way to rename a field's internal identifier or convert
it from, say, Text to Number after the fact. If you need a different type,
create a new field and remove the old one.

Everything else can be changed at any time: the display label, all of the
settings listed above (required, readonly, hidden, searchable, filterable,
sortable, default value), and — for Select and Status fields — the list of
options itself, including a Status option's color and icon.

## Deleting a field

Only fields you added yourself can be deleted. Fields that shipped with a
module (the five universal fields, and any other built-in field) can't be
deleted — only hidden from layouts.

Before deleting a custom field, Cubrel tells you how many existing records
currently have data in it. Deleting the field doesn't erase that data from
the database, but it does mean the data is no longer visible or editable
anywhere — this can't be undone. Any layout referencing the field
(list, record, or linking-panel layouts) has it removed automatically, so you
won't end up with a layout pointing at a field that no longer exists.

## In short

| Question | Answer |
| --- | --- |
| Can I remove one of the five universal fields? | No — hide it from a layout instead. |
| What's the difference between Select and Status? | Status options carry a color and icon and render as a badge; Select is plain text. |
| Can I change a field's type after creating it? | No — create a new field instead. |
| Can I rename a field's label after creating it? | Yes, any time. |
| Can I delete a built-in field? | No, only custom fields can be deleted. |
| What happens to a record's data when I delete its custom field? | It's kept in the database but no longer shown or editable — deletion of the field can't be undone. |
| Are readonly/hidden per-user or per-role? | No — they apply to everyone who can see the module. |
