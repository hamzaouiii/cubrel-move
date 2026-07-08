# The Cubrel Guide

This article is a tour of Cubrel from a user's point of view: what you can do with it, day to day, from your first login to running an established team on it. It's written for anyone using Cubrel, not just admins, though a few sections (marked as such) only apply if you have admin access. Where a topic has a full guide of its own, this article gives you the short version and links to the deep dive.

## Getting started

The very first time anyone opens a brand-new Cubrel instance, there's a short one-time setup: the person installing Cubrel gets an emailed link to create the first account, which becomes the first admin.

From there, every new instance walks through a short onboarding wizard right after that first login:

1. **Your organization** — company name, logo, address, phone, email, website. This is the information that shows up later on generated PDFs.
2. **Sample data** (optional) — Cubrel can populate some example records so you can click around and see how things fit together before you add your own real data.
3. **Invite your team** — bring in the people who'll be using Cubrel with you. You can skip this and invite people later.

After that, you land on your dashboard, and Cubrel behaves the same way from then on for everyone who logs in.

## Modules: what Cubrel is built out of

Everything in Cubrel — Leads, Accounts, Contacts, Deals, Quotes, Orders, Invoices, Products, Support Cases — is a **module**: a record type with its own place in the sidebar, its own list of entries, and its own record page. Cubrel ships with all of the above built in, organized into a few groups (Sales, Revenue, Support), plus system areas like Users and Settings.

If your business tracks something Cubrel doesn't already have a module for — equipment, projects, warranties, whatever it is — an admin can build a new module for it without writing any code, using the **Module Builder**. Once built, a custom module works exactly like a built-in one: same record pages, same layouts, same permissions. See the [modules guide](modules.md) for the full walkthrough of building and maintaining one.

## Fields: what a record holds

Every record is made up of **fields** — individual pieces of information like a name, an email address, a date, or an amount. Cubrel supports a wide range of field types, so the right kind of input is used for the right kind of data:

- Plain text and longer text notes
- Email, phone, and web address, each with their own validation so obviously malformed entries get caught
- Dropdown ("select") and colored status fields
- Yes/no checkboxes
- Dates and date-with-time
- Whole numbers, decimals, percentages, and currency amounts
- Multi-part addresses (street, city, postal code, country, and so on)
- Links to a record in another module
- Image uploads

Every module comes with a handful of fields automatically — Name, Description, Owner, Created At, and Updated At — and an admin can add as many additional fields as the module needs, at any time, without any downtime or technical work. Fields added after a module already exists are called **custom fields**, and from your point of view they look and behave exactly like any other field on the record.

## Layouts: how screens are arranged

Which fields you actually see, and where, is controlled by **layouts** — and every module has a few of them, each covering a different screen:

- The **list view** — which columns appear in the table when you open a module.
- The **record page** — how fields are grouped into sections when you open one record.
- The **related panels** — which linked-record panels show up on a record's Related tab, and in what order.
- The **linking window** — which extra columns help you tell records apart when searching for one to link.
- The **line items table** — for modules that use line items, which columns show on that table.

Admins can rearrange any of these with a drag-and-drop editor under **Settings → Modules → [module] → Layouts**, without touching anything else about the module.

## Working with records

### Creating, editing, and deleting

Every module gives you the familiar set of actions: open the list, create a new record, open an existing one to view or edit it, or delete it. Every change you make — create, edit, or delete — is automatically kept in that record's history; see [the Audit Trail guide](audit-trail-guide.md).

### Bulk actions

You don't have to work one record at a time. From any list view, you can select multiple records — either individually, or "everything matching my current filter," even across pages — and:

- **Delete** all of them at once.
- **Update** one field on all of them at once, in a single step (for example, reassigning the owner of 40 leads in one go).

If the field you're bulk-updating is required, Cubrel checks up front that the new value wouldn't leave any record blank, before making any changes.

### Exporting

You can export a single record, or a bulk selection, as **CSV** or **JSON** from the list view or the record page itself. A single record's export also includes its line items (if the module has any) as a separate section; bulk exports of many records at once don't, since line items would make each row a different shape. Exported values are formatted the same way they'd appear on a generated PDF, so what you download matches what you'd see printed.

## Relationships: connecting records to each other

Records in different modules can be linked together — an Account linked to its Contacts, a Deal linked to its Account, and so on. Once two modules have a relationship between them, you'll see it as a panel on the **Related** tab of any record on either side.

Cubrel ships with relationships already set up between the standard modules, and an admin can create new ones between any two modules, including modules you've built yourself. See the [relationships guide](relationships-guide.md) for the different shapes a relationship can take (one-to-one, one-to-many, many-to-many) and how linking and unlinking records works.

## Line items: itemized quotes, orders, and invoices

Quotes, Orders, and Invoices (and any custom module set up the same way) don't just hold plain fields — they hold a list of priced **line items**, usually pulled from your Products catalog. For each line item, Cubrel automatically works out its subtotal, discount, and tax, and rolls all of the line items up into the record's own Subtotal, Discount, Tax, and Total, kept in sync automatically any time a line item changes.

Which of a product's fields autofill which line-item fields (for example, its price filling in the line item's unit price) is configurable per module, as is which columns show up on the line-items table itself.

## Search and filters

### Finding anything, from anywhere

The global search bar, available from anywhere in Cubrel, looks across every module at once and takes you straight to a matching record.

### Narrowing down a list

Every list view also has its own filter builder, letting you combine multiple conditions (for example, "Status is Open" AND "Owner is me") rather than just searching by keyword. Available conditions depend on the field's type — text fields support things like "contains" or "starts with," numbers and dates support "greater than," "before/after," or "between," and any field supports "is empty"/"is not empty."

You can save a filter you use often instead of rebuilding it every time. A saved filter can be kept private, shared with your whole team, or — for a couple of built-in ones like "My Records" — provided by Cubrel itself and always available.

## Dashboard: your home page

The page you land on after signing in is your **dashboard** — and it's fully personal. Cubrel starts everyone off with a sensible default, but you can rearrange it, add widgets, remove ones you don't need, and resize them, and your layout is remembered from then on, separately from everyone else's.

Widgets available include metrics, breakdowns, time-series charts, record lists, and people/team widgets, each configurable to pull from whichever module, fields, and filters you choose — for example, "open deals by sales stage" or "invoices due this month."

## PDF documents

Any record from a module set up for it — most commonly Quotes, Orders, and Invoices — can be generated as a polished PDF, using your company's own branding (logo, name, address) automatically pulled in. If a module has more than one PDF template, you'll be asked to pick one when generating; if it only has one, Cubrel generates and downloads it immediately.

**Admins** design these templates under **Settings → PDF Templates**, using a section-based editor (header, footer, field rows, notes, related-record tables, a line-items table with its own totals) with a live preview that shows exactly what a generated PDF will look like using sample data, before it's ever used on a real record.

## Settings (admin)

Settings bring together everything that shapes how Cubrel behaves for your whole organization:

- **System** — language, date/time/number formatting, timezone, and your organization's default currency.
- **Style** — your brand colors, corner roundness, and whether each module keeps its own accent color or shares one.
- **Company** — your organization's name, address, logo, and contact details.
- **Field Manager** — add, edit, or remove fields on any module.
- **Dropdown Manager** — manage the reusable option lists behind your dropdown and status fields, so updating a list in one place updates every field that uses it.
- **PDF Templates** — covered above.
- **Modules** — the Module Builder and Module Manager, covered earlier.
- **Audit Trail** — covered below.

## Permissions, roles, and ownership

Cubrel keeps access simple, with two levels above a regular user:

- **Regular users** see whichever modules are turned on for everyone, and have full create/edit/delete access on records within them, including linking and unlinking related records. Settings, Users, and other admin-only areas stay out of their way entirely.
- **Admins** additionally get access to Settings — modules, fields, layouts, dropdowns, company info, PDF templates, and user management.
- **Root users** can do everything an admin can, plus one extra thing: sign in *as* another user (impersonation) to help troubleshoot something from their exact point of view. This is always fully visible afterward, never hidden — see [the Audit Trail guide](audit-trail-guide.md#impersonation-is-always-transparent-never-hidden).

Most modules also track an **owner** — the user a record is assigned to — which is what powers "my records" filters and owner-scoped dashboard widgets.

## Users and invitations (admin)

Admins manage the team from **Settings → Users**: creating accounts directly, or inviting people by email — single invites or several at once. An invited person gets a link to set their own password and create their account; admins can resend or revoke a pending invite at any time. Every user also has their own profile page to update their own name, contact details, title, and avatar.

## Signing in and staying signed in

Signing in is a standard email-and-password login, with a "forgot password" flow if needed. A couple of things are worth knowing:

- **"Keep me signed in"** on the login screen keeps you signed in on that device for a long time (roughly a year), instead of the normal several-hour session — handy on your own computer, best avoided on a shared one.
- **As long as you're actively using Cubrel**, your session simply doesn't expire, no matter how long you've had the tab open.
- **If you do get signed out mid-edit**, Cubrel doesn't throw away what you were typing — you're taken right back to it after logging in again.

See the [session guide](session-timeout-guide.md) for the full detail on how this works across multiple devices and browser tabs.

## Audit trail: a record of every change

Every create, update, and delete on any record, and every link or unlink between records, is logged automatically — no setup required, nothing to turn on. You can see any record's own history from its action menu ("View History"), and admins can see the full picture across the whole organization under **Settings → Audit Trail**. See the [Audit Trail guide](audit-trail-guide.md) for exactly what's captured, and [Cubrel Terminology](terminology.md) for a quick-reference glossary of everything covered in this guide.

## Where to go next

- New to Cubrel's vocabulary? Start with [Cubrel Terminology](terminology.md).
- Building your own module? See the [modules guide](modules.md).
- Connecting modules together? See the [relationships guide](relationships-guide.md).
- Curious what's tracked automatically? See the [Audit Trail guide](audit-trail-guide.md).
- Wondering how sign-in and sessions behave? See the [session guide](session-timeout-guide.md).
