# Modules

Modules are what Cubrel is built out of. Deals, Contacts, Accounts, Cases,
Quotes, Orders, Invoices, Products — each of these is a module: a record type
with its own list view, its own record page, its own fields, and its own place
in the sidebar. When you need to track something Cubrel doesn't already cover
(equipment, projects, warranties, whatever your business runs on), you don't
wait for a new release — you build a module for it yourself.

This guide explains what a module is made of, how to create one with the
Module Builder, and how to maintain one afterward with the Module Manager.

## What a module is

Every module is made up of the same pieces, whether it shipped with Cubrel or
you built it yourself:

- **Attributes** — its name, icon, color, category, and a handful of behavior
  switches (covered below).
- **Fields** — the pieces of information a record holds (a text field, a date,
  a dropdown, a link to another module, and so on).
- **Layouts** — how those fields are arranged on the list view, the record
  page, and related-records panels.
- **Relationships** — optional links to other modules (a Deal related to an
  Account, an Invoice related to a Contact). See the
  [relationships guide](relationships-guide.md) for that part.

Built-in modules (Leads, Accounts, Contacts, Deals, Quotes, Orders, Invoices,
Products, Cases, plus system areas like Settings and Users) come preconfigured.
Custom modules are ones an admin creates through the interface — once
published, they work exactly the same way as a built-in module: same sidebar
placement, same record pages, same field and layout editors, same permissions.
There's no functional difference between a "built-in" and a "custom" module
from that point on.

## Module attributes

These are the settings that describe a module. You'll set most of these when
creating a module, and can revisit all of them later from the module's
settings page.

| Attribute | What it controls |
| --- | --- |
| **Display name** | The plural name shown in menus and headings (e.g. "Deals"). |
| **Singular name** | The name used when referring to one record (e.g. "Deal"). |
| **Slug** | The module's internal identifier, generated automatically from the display name. It shows up in the URL and can't be changed once the module is created. A handful of words are reserved and can't be used (`fields`, `modules`, `labels`, `settings`, `users`, `roles`, `permissions`, `relationships`, `layouts`, `dropdowns`). |
| **Description** | A short explanation shown to other admins in the module list — not shown to regular users. |
| **Icon & color** | Used in the sidebar, page headers, and record cards. |
| **Category** | Groups the module under a sidebar section (Sales, Revenue, Support, etc.). |
| **Show in sidebar** | Whether the module gets its own entry in the navigation. |
| **Has line items** | Turns the module into a quote/order/invoice-style module, where records contain a list of priced line items rather than (or in addition to) regular fields. |
| **Line item source module** | Only relevant when "Has line items" is on. This is the catalog module (usually Products) that line items are picked from. It can only be changed while the module has no records with line items yet — once real quotes/orders/invoices exist, the source is locked in, since switching it afterward would break the link between existing line items and their catalog entries. |

Every module also automatically has five fields you don't need to add
yourself: **Name**, **Description**, **Owner**, **Created At**, and
**Updated At**. These exist on every module for consistency and can't be
removed, though you can hide them from a layout if you don't want them shown.

## Creating a module: the Module Builder

New modules are created with the **Module Builder**, found under
**Settings → Modules → Create**. It's a short wizard:

### 1. Basics

Fill in the module's attributes — display name, singular name, icon, color,
category, description, sidebar visibility, and whether it has line items. The
slug is generated for you from the display name and updates live as you type,
until you move on.

Your progress is saved as a private draft as you go, so you can leave and come
back to it. Only one admin can work on a given draft module at a time — if
someone else starts building the same module, they'll pick up a fresh draft
of their own rather than colliding with yours. An untouched draft frees itself
up automatically after a couple of hours of inactivity, so it doesn't stay
locked forever if someone starts a module and never finishes it.

### 2. Fields

Add the fields your module needs — text, numbers, dates, dropdowns, currency,
links to other modules, and so on. Fields you add at this stage become
permanent, built-in fields of the module, on equal footing with any built-in
module's fields.

Field names can't collide with the five automatic fields every module already
has (Name, Description, Owner, Created At, Updated At), or with the line-item
fields if the module has line items enabled.

### 3. Deploy

Deploying is the step that turns the draft into a real, usable module. You'll
see a short progress screen as it works through setting the module up,
preparing its fields, creating its storage, and switching it on. This
typically takes just a few seconds.

If a step fails partway through, you'll be offered the choice to **retry** the
same deployment, or **abort and clean up** — which discards everything that
step-by-step process had started, resets the module back to a draft, and
leaves the fields you'd already defined intact so you can fix the problem and
try again without starting over.

Once deployment finishes, the module is live: it appears in the sidebar (if
you chose to show it there), shows up in the module list, and behaves exactly
like any other module in Cubrel.

## Maintaining a module: the Module Manager

Once a module exists — built-in or custom — day-to-day changes go through the
**Module Manager**, at **Settings → Modules**. This is a list of every module
in the system; opening one takes you to its settings page, with tabs for:

- **Module settings** — the attributes described above (name, icon, color,
  category, description, sidebar visibility). Changes here take effect
  immediately, with no deployment step.
- **Fields** — add, edit, or remove fields at any point after the module is
  live, without needing to redeploy anything.
- **Layouts** — arrange fields on the list view, record page, and related
  panels.
- **Relationships** — connect the module to others.

A few things are intentionally locked once a module has been deployed and
can't be changed from the Module Manager: the slug, whether the module has
line items, and (once any line-item records exist) its line-item source
module. Everything else — name, icon, color, category, description, sidebar
visibility, fields, and layouts — can be adjusted freely at any time.

### Module Builder vs. Module Manager, in short

- **Module Builder** is only for creating a brand-new module. You use it once
  per module, from draft through deployment.
- **Module Manager** is for everything after that — the place you'll actually
  spend time in day-to-day, editing an existing module's settings, fields, and
  layouts.
