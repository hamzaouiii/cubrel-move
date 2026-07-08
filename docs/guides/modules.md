# Modules

Modules are Cubrel's unit of "a thing you manage records of" — Deals, Contacts,
Accounts, Cases, and so on. Everything the CRM shell knows how to do generically
(list records, show a record page, add custom fields, build layouts, wire up
relationships, search, audit) is driven by a module's configuration rather than
hard-coded per screen. This guide covers what a module actually is, how one gets
created (both built-in and admin-authored), and the three pieces of machinery
that make it work: the module's **attributes**, the **Module Builder**, and the
**Module Manager**.

For the conceptual split between the `Module` registry row and the `BaseModule`
business record classes, see
[Module vs BaseModule: A Developer's Guide](module-basemodule-guide.md) and its
companion [implementation notes](../dev/module-basemodule-implementation.md).
This document assumes that split and focuses on the module as a whole unit —
how it's declared, built, and managed.

## What a module actually is

A module is one row in the `modules` table (`App\Models\Module`), plus:

- a **business model** (`App\Models\Modules\{Name}`, extending `BaseModule`) that
  owns the module's own database table and represents its records,
- a **handler** (`App\Handlers\Modules\{Name}ModuleHandler`, implementing
  `App\Contracts\ModuleHandler`) that answers "give me this module's list/record
  data",
- a set of **fields** (`fields` table rows, scoped to this module by `module_id`,
  plus any globally-shared fields),
- a set of **layouts** (`layouts` table rows, one per layout type: `list`,
  `record`, `related`, `linkingPanel`, `lineItemsSnapshot`), and
- optionally, **relationships** to other modules (see
  [relationships guide](relationships-guide.md)).

There are two ways a module comes into existence: it's **built-in** (shipped in
`config/modules.php`, code written by hand, seeded into the `modules` table), or
it's **custom** (created by an admin at runtime through the Module Builder, with
its model/handler files and database table generated on the fly).

## Built-in modules

Built-in modules — Leads, Accounts, Contacts, Deals, Quotes, Orders, Invoices,
Products, Cases, plus the system modules Settings/Users/User Invites/Pdf
Templates — are declared as plain arrays in
[`config/modules.php`](../../config/modules.php). `ModulesTableSeeder` deletes
and re-inserts the `modules` table from that config on every seed run:

```php
foreach (config('modules') as $module) {
    Module::create($module);
}
```

For a built-in module you hand-write all the supporting code yourself:

1. Add the module's array entry to `config/modules.php` (see [Module
   attributes](#module-attributes) below for what goes in it).
2. Create the business model in `app/Models/Modules/{Name}.php`, extending
   `BaseModule`, with `$table` and `$fillable` set.
3. Create the handler in `app/Handlers/Modules/{Name}ModuleHandler.php`,
   extending `BaseModuleHandler` and implementing `query()`.
4. Write a migration for the module's table.
5. Add `fields` rows (via migration/seeder) for anything beyond the five
   universal default fields (see below).
6. Add a layout — either a DB `Layout` row, or a `config/module_layouts/{slug}.php`
   file (see the `deals.php` example in that directory) — or let it fall back to
   `Layout::getDefaultLayout()`.
7. Add translation keys under `lang/{locale}/modules.php` for the `label` /
   `single_label` / field label keys referenced in the config entry.
8. Re-run `ModulesTableSeeder` (or a fresh `php artisan migrate:fresh --seed`).

This is manual, code-level work — there's no UI for editing a built-in module's
definition; `modules.php` is the source of truth and the DB row is just a cache
of it, rewritten on every seed.

## Custom modules — the Module Builder

Admins create custom modules at runtime through the **Module Builder**
(`/settings/modulebuilder`, `App\Http\Controllers\ModuleBuilderController` +
`App\Http\Controllers\ModuleDeploymentController` + `App\Services\ModuleScaffolder`).
Unlike built-in modules, nothing is hand-written — the model file, handler file,
labels, and database table are generated for you.

### The draft mechanism

A custom module starts life as a **draft row** in `modules` (`is_draft = true`).
`ModuleBuilderController::getOrCreateDraftModule()` finds-or-creates this row per
user, using `locked_by` / `locked_until` as a soft lock (2-hour TTL) so two admins
don't collide on the same in-progress draft:

1. If the current user already holds a draft, reuse it and extend the lock.
2. Otherwise, look for an unlocked/expired draft to reclaim.
3. Otherwise, create a new one (`slug` = a `uniqid('draft_')` placeholder,
   `table_name = 'draft_cstm'`, hidden from sidebar and module manager, random
   icon/color).

While in draft, fields the admin adds via **saveDraftField** are stored as
`fields` rows with `is_draft = true` — they exist in the DB (so the builder UI
can list/edit them) but have no backing column yet and aren't visible anywhere
else. Field `name` is validated against the reserved default-field keys
(`config('default_fields')`, plus `config('default_line_item_fields')` when the
module has line items) so a custom field can't collide with `name`,
`description`, `owner_id`, etc.

### The builder wizard (Create.vue)

The builder UI (`resources/js/Pages/Settings/Modules/Create.vue`) is a two-tab
wizard over the same draft module:

1. **Edit tab** (`EditModule.vue`) — module attributes: display label, single
   label, slug (auto-derived from the label, read-only), icon, color, category,
   description, show-in-sidebar, has-line-items, is-product-like, and (if
   has-line-items) the line-item source module. Saving this tab issues
   `PUT /settings/modulebuilder/{module}` (`ModuleBuilderController::update()`),
   which re-derives `handler_class` / `model_class` / `table_name` / `path` from
   the slug/label and persists them onto the still-draft row.
2. **Fields tab** (`FieldSettings.vue`) — add/edit/delete draft fields via
   `POST/DELETE /settings/modulebuilder/{module}/field[/{field}]`.

Pressing "Deploy" on the last tab opens `DeployProgressModal.vue`, which drives
the five-call deployment sequence below and shows live progress per step.

### Deployment: five calls, one scaffolder

`ModuleDeploymentController` exposes deployment as five discrete steps under
`/settings/modulebuilder/{module}/deploy/*`, each delegating to
`App\Services\ModuleScaffolder`. Splitting it into steps (rather than one
request) is what lets the UI show a per-step progress list and retry/rollback on
partial failure:

| # | Endpoint | Scaffolder method | What happens |
| --- | --- | --- | --- |
| 1 | `initialize` | — | Re-validates the final attributes (slug uniqueness, reserved words, etc.), writes `handler_class`/`model_class`/`table_name` derived from the slug, module stays a draft |
| 2 | `generate-files` | `createModelFile()`, `createHandlerFile()` | Writes `app/Models/Modules/Custom/{Name}.php` and `app/Handlers/Modules/Custom/{Name}ModuleHandler.php` from string templates (no-op if the file already exists) |
| 3 | `create-labels` | `createModuleLabels()` | Upserts `Label` rows for the module's `label`/`single_label` translation keys |
| 4 | `create-table` | `createTable()` | Creates the module's database table from its draft fields (see below) |
| 5 | `activate-fields` | `activateFields()` | Flips every draft field to `is_draft = false, is_active = true`, rewrites each field's `key` to `{slug}_{name}`, creates its `Label` row, and finally flips the module itself to `is_draft = false, is_active = true` with `deployed_at = now()` |

`ModuleScaffolder::scaffold()` also exists as a single method that runs all of
the above in sequence — it's what the (currently unused) one-shot
`ModuleBuilderController::deploy()` action calls, but the live UI path is the
granular `ModuleDeploymentController` sequence above.

**Table generation (`createTable()`):** for each draft field, the field's `type`
is mapped to an Eloquent Blueprint method via `config('default_field_types_mapper')`
(e.g. `currency` → `decimal`, `select` → `string`, `record` → `string`,
`longtext` → `longText`), falling back to `string` for unmapped types. Every
custom module table always gets, regardless of configured fields: a UUID `id`
primary key, `name`, `description`, a `custom_fields` JSON column, a nullable
`owner_id` FK to `users`, timestamps, and soft-deletes. If `has_line_items` is
set, `subtotal`/`discount_amount`/`tax_amount`/`total` decimal columns are added
too. Fields whose `key` starts with `default.` (i.e. the five universal fields)
are skipped — they're not real columns on a custom table, only DB-defined
fields get their own column.

**If a step fails partway**, the UI's retry/rollback controls call
`POST .../deploy/rollback`, which runs `ModuleScaffolder::rollback()`: deletes
the generated model/handler files, drops the table if it exists, deletes the
module's `Label` rows, and resets the module row back to
`is_draft = true, is_active = false, table_name = null`. The draft's fields are
left alone (they're still `is_draft = true`), so the admin can fix the problem
and redeploy from the same draft.

### After deployment: adding fields later

Fields added to an already-deployed module (via `FieldsManagerController`,
outside the Module Builder) are **not** scaffolded a real column. They're
created with `is_custom = true` and, at the record level, `HasCustomFields`
(`app/Concerns/HasCustomFields.php`) routes their value into the module's
`custom_fields` JSON blob instead — `isCustomField()` checks the field's name
against the module's known custom-field list, and get/set/toArray transparently
merge that JSON in and out. This is why the builder's draft-time fields (which
get real columns) and post-deployment fields (which don't) are handled by two
different code paths even though they end up looking the same in the UI.

## Module attributes

These are the columns on the `modules` table (see
[`create_modules_table` migration](../../database/migrations/2025_12_04_115225_create_modules_table.php)),
i.e. what every module — built-in or custom — is described by:

| Attribute | Purpose |
| --- | --- |
| `name` | Internal display name (what the Edit tab calls "display label") |
| `slug` | URL/route/lookup key; unique; reserved words blocked (`config('reserved_keywords.slugs')`: `fields`, `modules`, `labels`, `settings`, `users`, `roles`, `permissions`, `relationships`, `layouts`, `dropdowns`) |
| `label` / `single_label` | Translation keys (or, for custom modules pre-deploy, the raw label) resolved via `HasTranslatableLabel` — plural/list heading vs. singular/record heading |
| `category` | Groups modules in the sidebar (`sales`, `revenue`, `support`, `system`, ...) |
| `icon` / `color` | FontAwesome class + hex color used across sidebar, headers, module cards |
| `path` | Frontend route prefix, `/{slug}` |
| `sort_order` | Sidebar/list ordering |
| `is_active` | Module is live and usable |
| `is_draft` | Module is mid-creation in the Module Builder, not yet deployed |
| `show_in_sidebar` | Appears in the main nav (`Module::forSidebar()`) |
| `show_in_module_manager` | Appears in the settings → Modules list (system modules like Settings/Users hide themselves here) |
| `handler_class` | FQCN of the `ModuleHandler` implementation |
| `model_class` | FQCN of the `BaseModule` subclass; `Module::getInstance()` instantiates it |
| `table_name` | Business table backing the model |
| `has_owner` | Whether records get an `owner_id` (false for `users`/`settings`/line items) |
| `is_custom` | Admin-created vs. built-in |
| `locked_by` / `locked_until` | Draft editing lock (Module Builder only) |
| `has_line_items` | Module supports line items (Quotes/Orders/Invoices) |
| `is_product_like` | Module is eligible as a *line-item source* for other modules (Products) |
| `line_item_source_module` | Which `is_product_like` module this module's line items snapshot/search from; defaults to `products`; locked once the module has any line items (`canChangeLineItemSourceModule()`) so existing `product_id` references can't be silently orphaned |
| `description` | Free text shown in the module manager |

Two request-lifetime static caches live on `Module` itself —
`$staticFieldCache` (per module ID) and `$staticLayoutCache` (per
`"{id}:{layout type}"`) — see the [implementation
notes](../dev/module-basemodule-implementation.md#12-two-static-in-process-caches)
for the batching helper (`warmFieldsCache()`) and its N+1 footgun.

### Field attributes

Fields (`fields` table) belong to a module via `module_id`, or apply to every
module via `is_global`. Key attributes: `name`/`key` (key is the globally-unique
storage key, `{module_slug}_{name}` once activated), `type` (one of
`config('default_field_types')`: `text`, `longtext`, `select`, `date`,
`datetime`, `number`, `integer`, `decimal`, `currency`, `percentage`, `email`,
`phone`, `url`, `checkbox`, `record`, `relationship`, `status`, `address`,
`image`), `label` (translation key), `readonly`, `required`, `sortable`,
`searchable`, `filterable`, `is_calculated`, `is_default` (one of the five
universal fields), `is_global`, `is_custom` (post-deployment field routed to
`custom_fields` JSON), `is_draft`, `is_default_for_line_items`,
`related_module`/`dropdown_list_id` (for `record`/`select` types),
`min_length`/`max_length`/`regex`/`default_value`.

Every module, built-in or custom, implicitly has five fields it didn't have to
define — `name`, `description`, `owner_id`, `created_at`, `updated_at` — from
`config('default_fields')`. These are "critical to system health," not stored as
per-module `fields` rows, always present, and only hideable (not deletable) from
layouts.

## Module Builder vs. Module Manager

These names are easy to conflate; they operate on different lifecycle stages of
a module:

| | **Module Builder** | **Module Manager** |
| --- | --- | --- |
| Controller | `ModuleBuilderController` / `ModuleDeploymentController` | `ModuleManagerController` |
| Routes | `/settings/modulebuilder*` | `/settings/modules*` |
| Operates on | A **draft** module, not yet real | An **already-deployed** module (built-in or custom) |
| Can change | Everything, including `slug`, `has_line_items`, code generation | Everything *except* structural identity (slug, table, model/handler class, `has_line_items` are effectively frozen post-deploy) |
| Side effects | Generates PHP files, creates a DB table, activates fields | Plain attribute update (`$module->fill($data)->save()`) — no file/table generation |
| Where it lives | `Settings/Modules/Create.vue` | `Settings/Modules/List.vue` (`ModuleManager.vue` card grid) → `Settings/Modules/Record.vue` |

In short: **Module Builder creates**, **Module Manager edits what already
exists**. Once a module is deployed, you go through the Module Manager (and its
scoped sub-resources — Fields, Layouts, Relationships, all under
`/settings/modules/{module}/...`) for everyday admin changes. You only go back
through the Module Builder if you're standing up a brand-new module.

One attribute worth calling out on the Manager side: `line_item_source_module`
can still be changed post-deploy through `ModuleManagerController::update()`,
but only while `canChangeLineItemSourceModule()` is true (no line items created
yet for that module) — otherwise the request is rejected with a validation
error, since repointing the source after records exist would leave existing
`product_id` references dangling.

## Cleanup tooling

- `php artisan modules:clean-files` (`App\Console\Commands\CleanCustomModules`)
  deletes every generated file under `app/Models/Modules/Custom` and
  `app/Handlers/Modules/Custom` — useful after resetting the database in
  development, since those files would otherwise reference module rows that no
  longer exist.
- `php artisan layouts:fix` (`App\Console\Commands\FixModuleLayouts`) is a
  one-off migration helper that rewrites `config/module_layouts/*.php` files,
  stripping a legacy `key` field from layout definitions.
