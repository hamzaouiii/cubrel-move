# Cubrel Terminology

This page is a reference for the words Cubrel uses for its own building
blocks. Some of them (Module, Field, Layout) are things you'll touch often;
others (Label, Dropdown List) are quieter pieces you'll only run into when
you're customizing something. Where a topic has a full guide of its own,
this entry links to it — think of this page as the map, not the whole
territory.

## The building blocks

### Module

A module is a record type — Deals, Contacts, Accounts, Cases, and so on are
all modules. Each one has its own place in the sidebar, its own list view,
its own record page, and its own set of fields. If Cubrel doesn't already
have a module for something your business tracks, you can build one
yourself. See the [modules guide](modules.md) for the full picture, including
how to create one.

### Core module

A module that ships with Cubrel out of the box — Leads, Accounts, Contacts,
Deals, Quotes, Orders, Invoices, Products, and Cases, plus system areas like
Users. Core modules behave no differently from custom ones once they exist;
"core" just describes where they came from, not how they work.

### Custom module

A module you (or another admin) built yourself, using the Module Builder.
Once created, a custom module works exactly like a core module — same
record pages, same field and layout editors, same permissions. See
[Creating a module](modules.md#creating-a-module-the-module-builder).

### Field

A single piece of information a record holds — a text box, a date, a dropdown,
a currency amount, a link to another module's record, and so on. Every module
has its own set of fields, plus five that every module gets automatically:
Name, Description, Owner, Created At, and Updated At.

### Core field

One of the fields a module was built with — either the five automatic fields
every module has, or a field you defined while building a custom module (see
[the Fields step](modules.md#2-fields)). Core fields are structural: they're
part of the module's definition from the start.

### Custom field

A field added to a module *after* it already exists — through **Settings >
Modules > [module] > Fields**, with no need to touch anything else. You can
add, edit, or remove custom fields at any time. From your point of view on
the record page they look and behave exactly like any other field; "custom"
just describes when it was added relative to the module's creation.

### Layout

How a module's fields are arranged on screen. A module has several layouts,
each covering a different view:

- **List** — which columns show up in the module's list/table view.
- **Record** — how fields are grouped into sections on a record's own page.
- **Related** — which relationship panels appear on a record's Related tab,
  and in what order.
- **Linking Panel** — which extra columns show up in the search window you
  get when linking a record to another one, beyond just its name.
- **Line Items Snapshot** — for modules with line items, which columns show
  on the line-item table itself.

Layouts are edited per module under **Settings > Modules > [module] >
Layouts**. If a module has no custom layout configured for a given view,
Cubrel falls back to a sensible default automatically.

### Label

The human-readable name shown for something, as opposed to its internal
name. When you set a field's "Label" while creating it, that's the text
users actually see on forms, columns, and record pages, it can be changed at
any time without affecting the underlying field itself. Modules, fields, and
relationships all have their own labels, and they can differ per language if
your organization uses more than one.

### Dropdown List

A reusable set of options for a "select"-type field, for example a Deal's
"Sales Stage" or a Lead's "Source." Dropdown lists are managed centrally
under **Settings > Dropdowns**, and the same list can be reused across
multiple fields, so updating the options in one place updates every field
that uses it.

### Relationship

A link between two modules, describing how their records connect, for
example "an Account has many Contacts," or "a Deal belongs to one Account."
Cubrel ships with a set of relationships already built in between the
standard modules, and you can create your own between any two modules,
including modules you've built. See the [relationships
guide](relationships-guide.md) for the different shapes a relationship can
take and how linking/unlinking works.

### Line items

For modules like Quotes, Orders, and Invoices, "has line items" means
records don't just hold regular fields, they also hold a list of priced
items (usually pulled from your Products module), with quantities and
prices that automatically roll up into a Subtotal, Tax, Discount, and Total
on the record. You can enable this for a custom module too, and choose which
module its line items are picked from.

### Dashboard

The landing page you see after signing in — a customizable set of widgets
giving you an overview of your data (recent records, counts, and so on).
There's an organization-wide default dashboard, and each user can have their
own personal version of it.

## People and access

### Admin

A user with access to **Settings** — modules, fields, layouts, dropdowns,
company settings, and the rest of what's covered in this page. Regular
(non-admin) users only see the day-to-day CRM: their modules, records, and
personal preferences.

### Root user

The highest level of access in a Cubrel instance. In addition to everything
an admin can do, a root user can sign in *as* another user (impersonation)
to help troubleshoot something from that person's exact point of view.
Impersonation is always visible in the Audit Trail when it happens, never
hidden, see the [Audit Trail guide](audit-trail-guide.md#impersonation-is-always-transparent-never-hidden).

### Owner

The user a record is assigned to. Most modules track an owner for every
record (used for visibility, filtering, and "my records" views); a handful
of system modules that don't represent a person's work item (like Users
itself) don't have one.

## Records and data

### Record

A single entry within a module, one specific Deal, one specific Contact, and
so on. "Module" describes the type; "record" is an individual instance of
it.

### List View

The table of records you see when you open a module, one row per record,
with columns defined by that module's List layout. Supports searching,
sorting, filtering, and selecting multiple records at once for bulk actions.

### List Filter

A saved search you can reuse instead of re-entering the same criteria every
time. Filters can be kept private, shared with everyone who uses that
module, or (for a few built-in cases) provided by Cubrel itself as a system
filter that can't be edited or deleted.

### Bulk actions

Editing, deleting, or exporting more than one record at once from a List
View, either by selecting specific records individually, or by selecting
"everything matching the current filter" in one go, even across pages.

### Export

Downloading a record, or a bulk selection of records, as a JSON or CSV file
from its List View or record page.

### Search

Cubrel's global search (the search bar available from anywhere in the app)
looks across modules at once and jumps you straight to a matching record.
Each field can be marked searchable or not when it's configured.

## History and oversight

### Audit Trail

The automatic, always-on log of every create, update, and delete across
every module, no setup required. Viewable per record ("View History" from
that record's action menu) or in full under **Settings > Audit Trail** for
admins. See the [Audit Trail guide](audit-trail-guide.md).

### Impersonation Session

A record of a root user signing in as another user, listed under
**Settings > Impersonation Sessions**: who, from what IP address, and for
how long. Kept separately from the Audit Trail, which instead flags any
*changes* made during such a session.

### Session

Cubrel remembering that you're signed in. Sessions are per-device, and
normally expire after a period of inactivity unless you choose to stay
signed in for longer. See the [session guide](session-timeout-guide.md) for
the full behavior, including what happens to unsaved work if one expires.

## Documents

### PDF Template

A reusable layout for generating a PDF from a record, most commonly a Quote,
Order, or Invoice. Managed under **Settings > PDF Templates**, one module
can have several templates, with one marked as the default used when no
specific template is chosen.

## Everyday settings

A handful of other settings areas round out the app, without needing their
own concepts:

- **Company Info** — your organization's own details (used, among other
  places, on generated PDFs).
- **Locale** — date, time, and number formatting for your organization.
- **Style** — branding: primary/secondary colors, and whether each module
  uses its own accent color or a single shared one.
- **Preferences** — smaller personal/organizational defaults, like how many
  records show per page in a List View.
- **Invites** — how new users are added to your organization; an invited
  person sets their own password the first time they sign in.

## In short

| Term | One-line definition |
| --- | --- |
| Module | A record type, with its own list, record page, and fields |
| Core module | A module Cubrel ships with |
| Custom module | A module an admin built |
| Field | A single piece of information on a record |
| Core field | A field the module was built with |
| Custom field | A field added after the module already existed |
| Layout | How a module's fields are arranged on a given screen |
| Label | The human-readable name shown for something |
| Dropdown List | A reusable, centrally-managed set of options for select fields |
| Relationship | A link between two modules |
| Line items | A priced list within a record (Quotes/Orders/Invoices-style) |
| Dashboard | The customizable overview page you land on after signing in |
| Admin | A user with access to Settings |
| Root user | An admin who can also impersonate other users |
| Owner | The user a record is assigned to |
| Record | One individual entry within a module |
| List Filter | A saved, reusable search |
| Audit Trail | The automatic log of every change, everywhere |
| Impersonation Session | A record of a root user signing in as someone else |
| Session | Cubrel remembering you're signed in |
| PDF Template | A reusable layout for generating a record as a PDF |
