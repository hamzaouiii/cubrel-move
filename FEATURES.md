# Cubrel CRM — Feature Inventory

> Updated 2026-07-08 from routes, controllers, models, config, migrations, and Vue components.
> Half-built / incomplete items are marked **⚠ INCOMPLETE**.

---

## Table of Contents

1. [Data Modeling](#1-data-modeling)
2. [Field Types](#2-field-types)
3. [Module System](#3-module-system)
4. [Layouts](#4-layouts)
5. [Record CRUD & Bulk Operations](#5-record-crud--bulk-operations)
6. [Relationships](#6-relationships)
7. [Line Items](#7-line-items)
8. [Search](#8-search)
9. [Dashboard](#9-dashboard)
10. [Settings](#10-settings)
11. [Permissions & Roles](#11-permissions--roles)
12. [User Management & Invitations](#12-user-management--invitations)
13. [PDF Generation](#13-pdf-generation)
14. [Authentication & Security](#14-authentication--security)
15. [Onboarding & Setup](#15-onboarding--setup)
16. [Audit Trail & Impersonation Sessions](#16-audit-trail--impersonation-sessions)

---

## 1. Data Modeling

### Core Business Modules

Each business module is stored in its own table, extends `BaseModule`, and carries both stock fields (defined in `config/stock_fields.php`) and an arbitrary number of user-defined custom fields stored in a `custom_fields` JSON column.

| Module | Table | Notable fields | Line items | Owner |
|---|---|---|---|---|
| **Leads** | `leads` | first_name, last_name, email, phone, company, address (JSON) | No | Yes |
| **Accounts** | `accounts` | name, website, email, phone, billing_address (JSON), shipping_address (JSON) | No | Yes |
| **Contacts** | `contacts` | first_name, last_name, email, phone, position, notes | No | Yes |
| **Deals** | `deals` | amount, sales_stage, probability, expected_close_date, type | No | Yes |
| **Orders** | `orders` | order_number, status, order_date, due_date, subtotal, discount_amount, tax_amount, total | Yes | Yes |
| **Invoices** | `invoices` | number, status, issue_date, due_date, currency, subtotal, discount_amount, tax_amount, total | Yes | Yes |
| **Quotes** | `quotes` | number, status, valid_until, currency, subtotal, discount_amount, tax_amount, total | Yes | Yes |
| **Products** | `products` | sku, category, price, unit, tax_rate, is_active | No | Yes |
| **Support Cases** | `cases` | subject, status, priority, opened_at, closed_at | No | Yes |
| **Inquiries** | `contact_messages` | email, phone, message, status, ip, user_agent | No | No |

### Infrastructure Models

| Model | Purpose |
|---|---|
| `Module` | Registry entry for every CRM module (slug, icon, flags, model/handler class, etc.) |
| `Field` | Defines a field on a module — type, validation rules, display options |
| `Layout` | Stores JSON configuration for list / record / related views per module |
| `DropdownList` | Named lists of selectable option values used by select/status fields |
| `Relationship` | Declares how two modules relate (one-to-many, many-to-many, one-to-one) |
| `RelationshipLink` | Join record connecting two module records through a `Relationship` |
| `UserInvite` | Pending invitation token with expiry and status |
| `LineItem` | Child row on an Order, Invoice, or Quote (polymorphic `parent_type/parent_id`) |
| `Settings` / `SettingValue` | Key-value system settings with group and autoload support |
| `Dashboard` | Per-user dashboard widget configuration (JSON) |
| `PdfTemplate` | Module-scoped PDF template; owns its section `definition` JSON directly; `is_default` flag per module |
| `AuditLog` | Append-only event log — who changed what record, and who linked/unlinked which relationship (see [Audit Trail & Impersonation Sessions](#16-audit-trail--impersonation-sessions)) |
| `ImpersonationSession` | One row per impersonation session — who impersonated whom, from what IP, start/end (see [Audit Trail & Impersonation Sessions](#16-audit-trail--impersonation-sessions)) |

---

## 2. Field Types

### Supported Types

Defined in `config/default_field_types.php` and mapped to database column types in `config/default_field_types_mapper.php`. Each type has a Vue component registered in `resources/js/Registries/fieldRegistry.js`.

| Type | DB column | Vue component | Validation |
|---|---|---|---|
| `text` | `text` | `Text.vue` | none (accepts anything) |
| `longtext` | `longText` | `LongText.vue` | none |
| `email` | `string` | `Email.vue` | regex email pattern |
| `phone` | `string` | `PhoneField.vue` | libphonenumber-js (DE locale default) |
| `url` | `string` | `UrlField.vue` | must include protocol |
| `select` | `string` | `Select.vue` | none |
| `status` | `string` | `StatusField.vue` | none |
| `checkbox` | `boolean` | `Checkbox.vue` | none |
| `date` | `date` | `DateTime.vue` | none |
| `datetime` | `dateTime` | `DateTime.vue` | none |
| `integer` | `integer` | `IntegerField.vue` | integer only, optional range |
| `decimal` | `decimal` | `DecimalField.vue` | float, optional precision + bounds |
| `percentage` | `decimal` | `PercentageField.vue` | 0–100 range |
| `currency` | `decimal` | `CurrencyField.vue` | non-negative number |
| `address` | `json` | `AddressField.vue` | all sub-fields non-empty |
| `record` | `string` | `RelatedRecord.vue` | UUID format |
| `number` | `integer` | (same as integer) | — |

`CurrencyField.vue` and `AddressField.vue` are fully shipped: committed to git (`git log` shows real history for both, not untracked files), registered in `fieldRegistry.js` with validators, and handled by `PdfValueRenderer` (`address` → `formatAddress()`, `currency` → `PdfNumberFormatter::format()`) for PDF/export output.

### Composite Field: Address

The `address` type stores a JSON object with sub-fields (street, city, postal_code, country, etc.). It is flagged `isComposite: true` in the registry, meaning the field renderer handles it differently from flat types.

### Custom Field Storage

All module tables carry a `custom_fields` JSON column (added via migration `2026_01_19`). Fields created through the Field Manager are stored here rather than as dedicated columns, unless they are stock (seed-time) fields.

---

## 3. Module System

### Static Modules

17 built-in modules are defined in `config/modules.php` and seeded via `ModulesTableSeeder`. Each module declares:

- `slug`, `name`, `label`, `single_label`, `icon`, `color`, `category`
- `model_class` and `handler_class` (PHP class names)
- `table_name`, `sort_order`, `is_active`, `show_in_sidebar`
- Capability flags: `can_view`, `can_create`, `can_edit`, `can_delete`
- Feature flags: `has_line_items`, `has_owner`, `is_product_like`, `line_item_source_module`

**Categories:** `sales` (Leads, Accounts, Contacts, Deals) · `revenue` (Quotes, Orders, Invoices, Products, LineItems) · `support` (Cases) · `communication` (Inquiries) · `system` (Users, User Invites, Settings)

### Line Items Are Configurable Per Module

`has_line_items` is no longer hard-wired to Orders/Invoices/Quotes and no longer implies "snapshot from Products." Any module — standard or custom, built through the Module Builder — can enable line items:

- `is_product_like` flags a module (e.g. Products) as eligible to be picked as a **line-item source** by other modules.
- `line_item_source_module` on the host module (Orders, Invoices, Quotes, or any custom module) picks which product-like module its line items search and snapshot from. Defaults to `products` for modules that predate this setting (backfilled by migration `2026_07_03_000000_add_line_item_config_to_modules_table`).
- `Module::canChangeLineItemSourceModule()` locks the source picker in the UI once at least one line item row exists for that module — changing the source afterward would orphan existing `product_id` references and invalidate the field mapping below. `ModuleManagerController@update` also rejects the change server-side with `settings.modules.line_item_source_locked`.
- `Module::lineItemSourceModuleSlug()` resolves the effective source (explicit value, or `'products'` fallback).
- The Module Builder (`Settings/Modules/Create.vue`, `Builder/EditModule.vue`) and the Module Manager (`Settings/Modules/Record.vue`) both expose a source-module picker populated from `ModuleBuilderController::lineItemSourceOptions()` / the equivalent query in `ModuleManagerController@show` (any active `is_product_like` module other than itself).

### Module Builder — Custom Modules

Admins can build entirely new modules without writing code. The flow has dedicated routes and a multi-step UI:

1. **Create** — `ModuleBuilderController@create` / `Settings/Modules/Create.vue` — define name, slug, icon, category, color, and (if line items are enabled) a line-item source module
2. **Add fields** — `ModuleBuilderController@saveDraftField` — add fields in draft state; `deleteDraftField` removes them
3. **Edit module** — `ModuleBuilderController@update` / `Builder/EditModule.vue`
4. **Deploy** — `ModuleDeploymentController` exposes six sequential steps:
   - `initialize` — allocates the module record
   - `generate-files` — scaffolds handler and model PHP files
   - `create-labels` — writes translation keys
   - `activate-fields` — marks draft fields as active
   - `create-table` — runs the migration
   - `rollback` — undoes deployment
5. Progress is shown in `Builder/DeployProgressModal.vue`

Covered by an automated end-to-end test: `tests/Feature/Modules/ModuleBuilderWorkflowTest.php` drives the real deploy pipeline over HTTP (`generate-files` → `create-labels` → `create-table` → `activate-fields`), asserts the generated PHP files actually land on disk (model + handler), and verifies the resulting module supports full CRUD.

### Handler Pattern

Every module has a handler class (extends `BaseModuleHandler`) responsible for:

- `query()` — base Eloquent query with scope/filter application
- `getListData()` — data for list views (pagination, sorting, column selection)
- `getRecordData()` — data for single-record views (field values, related panels)
- Declaring searchable columns and relationship eager-loads

---

## 4. Layouts

### Layout Types

Each module can have up to six layout types stored in the `layouts` table as JSON blobs. Default layouts are defined in `config/module_layouts/{module}.php` (and, for the new type below, `config/default_layouts.php`).

| Type | Purpose | UI editor |
|---|---|---|
| `list` | Columns shown in the module list table | `LayoutListEditor.vue` |
| `record` | Sections and fields shown on a single record page | `LayoutRecordEditor.vue` |
| `related` | Columns shown in related-record subpanels | `LayoutRelatedEditor.vue` |
| `linkingPanel` | Columns shown in the record-selector overlay | `LayoutLinkingPanelEditor.vue` |
| `pdf` | Fields rendered into PDF output | `LayoutPdfEditor.vue` |
| `lineItemsSnapshot` | Which line-item fields appear on the create/edit sheet, and which field on the configured line-item source module autofills each one (only relevant when `has_line_items` is true) | `LayoutLineItemMappingEditor.vue` |

### Layout Editor

Routes under `/settings/modules/{module}/layouts` let admins view and update layouts.

- `LayoutManagerController@show` — lists layout types for a module
- `LayoutManagerController@edit` — loads current layout JSON
- `LayoutManagerController@store` — persists updated layout JSON

The Vue editors (`Settings/Layouts/Edit.vue`, `Settings/Layouts/Record.vue`) provide a drag-and-drop interface for reordering fields and toggling per-field options (`readonly`, `required`, `sortable`, `hidden`).

Layout config per module specifies fields by name and includes display metadata (label translation key, readonly flag, required flag, sortable flag).

Within the `record` layout editor, a module's "Line Items" placeholder section (`has_line_items: true`) previously just displayed a static message. It now embeds its own `LayoutListEditor` instance, letting admins pick and reorder which `line_items` module fields appear as table columns — a separate field pool from the host module's own fields, so it's excluded from the regular "used fields" tracking (`availableRecordFields`) to avoid the columns being mistaken for unknown/used host-module fields.

---

## 5. Record CRUD & Bulk Operations

### Single Record Operations

| Action | Route | Controller method |
|---|---|---|
| Show list | `GET /{module}` | `ListController@__invoke` |
| Show record | `GET /{module}/{recordId}` | `RecordController@__invoke` |
| Create form | `GET /{module}/create` | `RecordController@create` |
| Store | `POST /{module}` | `RecordController@store` |
| Update | `PUT /{module}/{record}` | `RecordController@update` |
| Delete | `DELETE /{module}/{record}` | `RecordController@destroy` |

### Bulk Operations

Both bulk delete and bulk update support three selection modes passed in the request body:

- **Explicit list** — `selectedIds[]` array of UUIDs
- **All matching** — `allMatchingSelected=true` with current filter state
- **All except** — `allMatchingSelected=true` + `excludedIds[]`

| Action | Route | Component |
|---|---|---|
| Bulk delete | `DELETE /{module}` | `ListActions/ListDeleteZone.vue` |
| Bulk field update | `PUT /{module}` | `ListActions/MassUpdateZone.vue` |

**⚠ INCOMPLETE — Bulk field update has no required-field validation**: `RecordController@updateMany` writes straight to the DB via query-builder (`whereIn(...)->update([$column => $value])`), bypassing Eloquent entirely — no `required`/`nullable` check against the target `Field` definition, unlike single-record create/update which goes through form validation. A user can mass-clear a required field (e.g. blank out every selected record's `name`) across an arbitrary number of records with no error, in both "explicit selection" and "all matching filter" modes.

### Export

`ExportController` provides CSV and JSON export, both single-record and bulk:

| Action | Route | Controller method |
|---|---|---|
| Export one record | `GET /{module}/{recordId}/export?format=json\|csv` | `ExportController@export` |
| Export many | `POST /{module}/export` | `ExportController@exportMany` |

- **Bulk export reuses the same three selection modes as bulk delete/update** (`selectedIds[]`, `allMatchingSelected` + current filter state, or `allMatchingSelected` + `excludedIds[]`), so an export can target an explicit selection, "all matching the current search/filter," or "all except."
- Every create/update/delete above (single-record and bulk) is logged automatically — see [Audit Trail & Impersonation Sessions](#16-audit-trail--impersonation-sessions).
- Only `json` and `csv` are supported — no Excel/XLSX.
- A single record's export includes a "line items" section appended after the main row when `module.has_line_items` is true; bulk export omits line items entirely (rows would have inconsistent shape across records).
- Field values are formatted for export the same way they're formatted for PDFs — `ExportController` reuses `PdfValueRenderer` (see [PDF Generation](#13-pdf-generation)) for dates, selects/status labels, decimals/currency, and addresses, so export output and PDF output stay consistent.
- Frontend: `ExportModal.vue` (format picker, single vs. bulk mode, downloads via blob response) opened from `ListActions/ExportZone.vue` on the list view.

---

## 6. Relationships

### Definition

Relationships between modules are declared in the `relationships` table and seeded by `RelationshipSeeder`. Supported types: `one-to-many`, `many-to-many`, `one-to-one`. System relationships (`is_system=true`) are non-deletable.

### Linking / Unlinking

| Action | Route |
|---|---|
| Get available records to link | `GET /modules/{module}/{record}/relationships/{relationship}/available` |
| Get records for single-link update | `GET /modules/{module}/{record}/relationships/{relationship}/single-link` |
| Link records | `POST /modules/{module}/{record}/relationships/{relationship}` |
| Unlink records | `DELETE /modules/{module}/{record}/relationships/{relationship}/{relatedId}` |

The `RelatedLinksOverlay.vue` and `RecordSelectorDrawer.vue` components handle the UI for selecting and linking records. Linking and unlinking are both logged — on both sides of the relationship — see [Audit Trail & Impersonation Sessions](#16-audit-trail--impersonation-sessions).

### Relationship Manager (Settings)

Admins can view, create, and delete relationship definitions at `/settings/modules/{module}/relationships`. UI: `Settings/Relationships/List.vue` and `Settings/Relationships/Create.vue`.

**⚠ INCOMPLETE** — The resource route exists for `update` but no edit UI was found for modifying an existing relationship definition after creation.

---

## 7. Line Items

Any module can support child line item rows — not just Orders, Invoices, and Quotes. A line item is a polymorphic child (`parent_type` / `parent_id`) that optionally references a record (`product_id`) in whichever module is configured as the host module's **line-item source** (see [Module System](#3-module-system)). Because the source module is now configurable rather than fixed, `LineItem` no longer declares `parent()` / `product()` Eloquent relations — there is no single related model class to bind to; resolution happens dynamically through the module/handler lookup instead.

### Per-item calculations (server-side, `LineItem::calculateTotals()`)

```
subtotal        = unit_price × quantity
discount_amount = subtotal × (discount / 100)
tax_amount      = (subtotal − discount_amount) × (tax_rate / 100)
total           = subtotal − discount_amount + tax_amount
```

`discount` is percentage-only — the client-side sheet (`LineItemsPanel.vue`) used to let a user edit either the discount percent or the flat discount amount and back-compute the other (`lastEdited` toggle); that dual-entry mode was removed, and `discount_amount` is now always derived from `discount`.

### Configurable snapshot mapping

When a line item's source-module record is picked (e.g. a Product on an Invoice line), which of its fields autofill which line-item fields is configurable per module via a new **`lineItemsSnapshot`** layout type:

- Defined per module in the `layouts` table (falls back to `config/default_layouts.php`'s `lineItemsSnapshot` entry, e.g. `unit_price` ← source module's `price` field).
- Edited through `LayoutLineItemMappingEditor.vue`, a drag-and-drop editor reached the same way as other layout types (`/settings/modules/{module}/layouts`).
- `LayoutManagerController@store` validates it as `definition.fields[].{name, source_field}`; `@edit` additionally resolves and passes `sourceModuleFields` (the field list of whichever module is currently configured as the source) so the editor can offer a per-field autofill picker.
- On the record page, `LineItemsPanel.vue`'s `onProductSelect` walks `snapshotLayout.fields` generically and copies `product[source_field]` into `row[name]` for every mapped field, instead of hardcoding `name`/`unit_price`/`unit`/`tax_rate`.

The line-items **table's visible columns** are likewise no longer hardcoded — they come from the host module's record layout, inside the "Line Items" section's own `layout` array (`has_line_items: true`), configured per module in `config/module_layouts/{invoices,orders,quotes}.php` (previously an empty array with columns implied by the Vue template).

### API

| Action | Route |
|---|---|
| List | `GET /line-items?parent_type=X&parent_id=Y` |
| Create | `POST /line-items` |
| Update | `PUT /line-items/{lineItem}` |
| Delete | `DELETE /line-items/{lineItem}` |
| Reorder | `POST /line-items/reorder` (updates `sort_order`) |

The record page shows line items when `module.has_line_items` is true. `RecordController` now only resolves line-item fields, the source module, its fields, list columns, and the snapshot layout when that flag is set — the previous implementation did an unconditional `firstOrFail()` lookup of the `products` module on **every** record page regardless of module, which could 500 if `products` were ever deactivated.

### Parent total roll-up (server-side, `LineItemTotalsObserver`)

`app/Observers/LineItemTotalsObserver.php`, registered on `LineItem` from `AppServiceProvider::boot()`, recomputes the parent record's `subtotal`/`discount_amount`/`tax_amount`/`total` by summing all its line items on every `saved`/`deleted` event — not just when `LineItemController@store/update` runs, and not dependent on anyone having that record's page open. Resolves the parent via `parent_type` (a module slug) → `Module::model_class`, the same polymorphic-by-convention pattern `AuditObserver` uses, and saves quietly (no audit-log noise, on top of `subtotal`/`discount_amount`/`tax_amount`/`total` already being flagged `is_calculated`). `Record.vue`'s `handleTotalsUpdated` now only mirrors the recalculated totals into the open record's form for immediate display — it no longer PUTs them back itself, since the server already persisted the correct values the moment the line item changed.

---

## 8. Search

### Global Search

Route `GET /search` (name: `search`) handled by `SearchController`. `BaseModule` uses Laravel Scout's own `Searchable` trait (`composer.json`: `laravel/scout ^11.2`), backed by the `database` driver (`config/scout.php`: `'driver' => env('SCOUT_DRIVER', 'database')`) — this is a real, configured Scout driver, not a custom trait of the same name. `app/Search/Searchers/UniversalSearcher.php` calls `$modelClass::search($query)->get()`, Scout's actual search method. The `GlobalSearch.vue` overlay and `SearchOverlay.vue` component manage the front-end.

### Related Field Search

Route `GET /relatedfield/search/{related_module}` (name: `records.search`) handled by `RelatedFieldController`. This is the typeahead endpoint used by the `RelatedRecord.vue` field component when the user types to find a record to link.

### Advanced List Filtering

List views have a full condition-based filter builder, not just column-level equality:

- **Filterable fields**: a field is filterable if it's one of the `default.*` stock keys or has `Field::filterable === true`; `owner_id` is excluded on modules without `has_owner` (`app/Support/Filters/FilterQueryBuilder.php::allowedFieldsMap()`).
- **Operators per field type** are declared in `config/filter_operators.php` — e.g. `equals`/`not_equals`/`contains`/`starts_with` for text, `greater_than`/`less_than`/`between` for numbers, `before`/`after`/`between` for dates, `is_empty`/`is_not_empty` for any type — enforced both server-side (`FilterOperators::isAllowed()`) and in the picker UI.
- **Conditions** are an array of `{field, operator, value}` combined with a `match_type` of `all` (AND) or `any` (OR), applied to the query in `FilterQueryBuilder::apply()`. A condition's value can be the literal `@current_user`, substituted with the logged-in user's ID — this is how "my records" style filters work.
- **Saved / shared / system filters** are a real model (`app/Models/ListFilter.php`), not just query-string state: a filter has `is_shared` (visible to the whole team), `is_system` (seeded, e.g. `my_records`, non-deletable), `is_global`, `conditions` (JSON), `match_type`, and `last_used`. Full CRUD at `POST|PUT|DELETE /{module}/filters[/{filter}]` (`ListFilterController`), seeded defaults via `DefaultFiltersSeeder` / `config/default_filters.php`.
- **Frontend**: `ListActions/FilterZone.vue` — multi-condition builder (field/operator/value pickers, `between`/`in` handled specially), save/edit/delete named filters, a "share with team" toggle, an AND/OR switch, and an overflow panel separating private vs. shared filters sorted by last-used.
- **Selecting a saved filter** via `?filter=<slug|uuid>` in the URL applies its conditions and bumps `last_used` (`ListController`) — so links stay shareable, but what's shared is a reference to a saved condition-set rather than raw column=value pairs.

### Sorting

Sorting remains simple: `BaseModuleHandler::getListData()` validates the requested `sort` column against the model's `getFillable()` list (not an explicit per-field `sortable` flag) and defaults to `created_at desc`.

---

## 9. Dashboard

Route `GET /` → `DashboardController@index`. Dashboards are now fully personalizable per user, not a fixed widget set.

### Personalized, persisted layout

- `Dashboard` model (`app/Models/Dashboard.php`): fillable `user_id, name, slug, layout, sort_order`; `layout` is cast to `array` and holds the full widget configuration as JSON — **not** a `configuration` column as previously documented; the real migration (`2026_05_04_121726_create_dashboards_table.php`) names it `layout`.
- `DashboardController@index` loads `Dashboard::where('user_id', $user->id)->first()` and only falls back to a role-based default (`DashboardPresets::layout()`, sourced from `config/dashboard_presets.php`, keyed by user type with `admin`/`read_only` variants) if the user has never saved their own layout.
- `DashboardController@saveLayout` (`POST /dashboard/layout`) persists via `Dashboard::updateOrCreate(['user_id' => ...], ['layout' => ...])`.
- `DashboardController@widgetData` (`POST /dashboard/widget-data`) resolves live data per widget type (`time-series`, `metric`, `breakdown`, `record-list`, `people`) through `AggregationService`, automatically owner-scoped for non-admin users. Companion endpoints `GET /dashboard/module-fields/{slug}`, `/dashboard/module-relationships/{slug}`, and `/dashboard/filterable-fields/{slug}` let each widget's config form populate its field/relationship/filter pickers per module.

### Editing UI

`resources/js/Pages/Dashboard/Index.vue` provides an explicit edit mode (`enterEdit` / `saveLayout` / `cancelEdit`):

- HTML5 drag-and-drop reordering of widgets with a ghost placeholder and a masonry-style row-span layout.
- Add/remove/reconfigure widgets (`addWidget`, `removeWidget`, `updateInstance`) — auto-persists immediately outside of bulk-edit mode, or via explicit Save/Cancel while editing.
- Two widget kinds coexist: legacy fixed string-id widgets (e.g. `my-records`, not configurable) and typed, configurable instances (generated `instanceId`, `type`, `config`, `cols` width) added through `AddWidgetPanel.vue` / `WidgetRegistry.js`.
- Five configurable widget types, each with its own config form: `MetricConfigForm.vue`, `TimeSeriesConfigForm.vue`, `BreakdownConfigForm.vue`, `RecordListConfigForm.vue`, `PeopleConfigForm.vue` — plus `WidthPicker.vue` for column span and `DashboardFilterBuilder.vue`, which reuses the same condition/operator system as list filters (see [Search](#8-search)) to scope a widget's data.

### Legacy fixed widgets (still present, non-configurable)

| Widget | Data source | Vue component |
|---|---|---|
| Recent leads | Last N leads where `owner_id = user` | `NewRecords.vue` |
| Owned records | Cross-module records owned by user | `MyRecords.vue` |
| Recent orders | Last 5 orders | `RecentOrders.vue` |
| Deals over time | Monthly deal amounts, 12-month window | `DealsOverTime.vue` (line chart) |
| Deal stages | Won / lost / open deal counts | `DealStages.vue` (doughnut chart) |
| Invoice overview | Invoice status breakdown | (inline in `Index.vue`) |

**Minor cleanup item (not user-facing)** — `Dashboard::scopeGlobal()` / `scopeForUser()` reference a non-existent `owner_id` column and aren't called anywhere in the app; they appear to be dead code left over from an earlier design.

---

## 10. Settings

Settings are stored as key-value rows in `setting_values`. The system is grouped by category. Most settings are shown on a dedicated page rendered by `Settings/Page.vue`.

### Categories & Items

| Category | Setting | Type |
|---|---|---|
| **System** | `timezone` | Dropdown (IANA zones) |
| | `date_format` | Dropdown (locale-aware examples) |
| | `datetime_format` | Dropdown |
| | `first_day_of_week` | Dropdown |
| | `list_view_limit` | Integer |
| | `linking_panel_limit` | Integer |
| | `related_panel_limit` | Integer |
| **Customisations** | `primary_color`, `secondary_color`, `danger_color`, `success_color` | Color pickers |
| | `theme` | Dropdown (light/dark/system) |
| | `border_radius` | Text/slider |
| | `table_striped_rows` | Checkbox |
| **Users** | `multi_currency_mode` | Checkbox |
| | `default_currency` | Dropdown |
| | `enabled_languages`, `default_language`, `fallback_language` | Dropdowns |
| | `show_language_switcher` | Checkbox |
| **Email** | Outbound/inbound mail configuration | Text fields |
| **Company** | Organisation identity fields | Text fields |

The `SettingsController` provides live preview options for date/datetime formats based on the selected timezone.

### Field Manager

Admins manage per-module field definitions at `/settings/modules/{module}/fields`:

- **List** — shows editable fields with type and status
- **Create** — `FieldsManagerController@store` — creates a new field (label, type, validation options, dropdown list)
- **Edit** — `FieldsManagerController@update` — update label, required flag, validation rules, etc.

### Dropdown Manager

Named dropdown option lists live at `/settings/dropdowns`. Lists can be created, edited inline, or created-and-immediately-attached to a field (`storeAndAttach` endpoint). Components: `DropdownSelector.vue`, `CreateNewDropdownListModal.vue`, `EditDropdownListModal.vue`.

---

## 11. Permissions & Roles

### Role Model

The system has two role levels:

| Flag | Meaning |
|---|---|
| `is_root = true` | Superuser — cannot be demoted; bypasses all checks |
| `is_admin = true` | Admin — accesses settings, user management, impersonation |
| neither | Regular user |

`AdminMiddleware` protects all routes under `/settings`, `/users`, and `/invites`. It calls `user()->isAdmin()` and aborts with 403 if false.

### Module-Level Capability Flags

Each module row in the `modules` table carries `can_view`, `can_create`, `can_edit`, `can_delete` boolean flags. These are surfaced to the front end via `HandleInertiaRequests` shared props and used to show/hide UI actions.

**⚠ INCOMPLETE — regressed, not just unaudited**: the capability flags (`can_view`, `can_create`, etc.) are stored per module globally, not per user or per role — there is no RBAC system; all non-admin users share the same permissions, and `users` has no roles/permissions schema beyond `type` and `is_admin`. `ModulePolicy` no longer exists at all: it was deliberately deleted (commit `d03f11d`, "Cleanup fro module policy"), along with the `authorize()` calls that used it in `ModuleBuilderController`/`ModuleManagerController`. There is now zero policy/gate enforcement anywhere in the app (`app/Policies/` is empty; no `authorize()`/`Gate::`/`->can()` calls outside unrelated Form Request boilerplate).

### Impersonation

Admins can impersonate any user via `POST /users/{user}/impersonate`. A persistent banner (`ImpersonationBanner.vue`) is shown while impersonating. `POST /leaveimpersonate` returns to the original admin session. Every impersonation session (who, whom, from what IP, start/end) and every action taken while impersonating is now logged — see [Audit Trail & Impersonation Sessions](#16-audit-trail--impersonation-sessions).

### Record Ownership

`has_owner = true` modules attach `owner_id` (FK → users) to each record. Dashboard widgets and list queries can be scoped to the authenticated user's owned records via `OwnershipService`.

---

## 12. User Management & Invitations

### User CRUD (Admin-only)

Routes under `/users` cover full CRUD for user accounts. Admins can set `is_admin`, assign `title`, `phone`, `mobile`, `locale`, `timezone`, `avatar`. Password is hashed on save.

| Route | Purpose |
|---|---|
| `GET /users` | Paginated user list |
| `GET /users/create` | Create form |
| `POST /users` | Store user |
| `GET /users/{id}` | Show user record |
| `PUT /users/{id}` | Update user |
| `GET /users-linking-list` | Paginated users for record-linking overlay |

### Invitation System

Admins can invite users by email. Accepted invites create a new User record.

| Route | Purpose |
|---|---|
| `POST /invites` | Create single invite |
| `POST /invites/bulk` | Create multiple invites at once |
| `GET /invites/{token}` | Show acceptance form (guest) |
| `POST /invites/{token}/accept` | Accept invite, set password, create account |
| `POST /invites/{invite}/resend` | Resend invitation email |
| `PATCH /invites/{invite}/revoke` | Revoke without deleting |
| `DELETE /invites/{invite}` | Delete invite record |
| `GET /users/invites` | Pending invites list |

`UserInvite::isPending()` checks `status = pending` and `expires_at > now()`.

### User Profile

`GET /profile` and `PUT /profile` let the authenticated user update their own name, email, avatar, locale, timezone, date/time formats, and theme preference.

---

## 13. PDF Generation

### PDF Templates

Templates are stored in the `pdf_templates` table (migration `2026_05_21`). Each template is fully standalone — it owns its layout `definition` (JSON) directly rather than depending on a shared Layout row. An optional `layout_id` FK is retained for backward compatibility with templates created through the old embedded-in-layout flow.

| Column | Type | Purpose |
|---|---|---|
| `id` | UUID | PK |
| `module_slug` | string | Which module the template belongs to |
| `name` | string | Human-readable name |
| `blade_view` | string | Blade view path (always `pdf.layout-driven`) |
| `layout_id` | string (nullable) | Legacy FK to `layouts` table |
| `description` | string (nullable) | Optional admin description |
| `is_default` | boolean | Only one default per module at a time |
| `definition` | JSON | Section tree that drives the rendered output |

`PdfTemplate::defaultFor($moduleSlug)` fetches the default template for a module; `existsFor()` checks existence.

### Template Manager (Settings)

Full CRUD at `/settings/pdf-templates` (route group `settings.pdf-templates.*`):

| Route | Method | Action |
|---|---|---|
| `/settings/pdf-templates` | GET | `index` — paginated list; searchable by name or module label |
| `/settings/pdf-templates/create?module=X` | GET | `create` — pre-loads fields, relationships, line-item fields for selected module |
| `/settings/pdf-templates` | POST | `store` — validates `definition.sections`, ensures single default per module |
| `/settings/pdf-templates/preview` | POST | `preview` — server-renders the Blade view with fake data; returns HTML for the live preview panel |
| `/settings/pdf-templates/{id}` | GET | `edit` |
| `/settings/pdf-templates/{id}` | PUT | `update` |
| `/settings/pdf-templates/{id}` | DELETE | `destroy` |
| `/settings/pdf-templates/{id}/default` | POST | `setDefault` — atomically sets one default, clears others |

### Section-Based Definition Format

A template `definition` is a JSON object `{ "sections": [...] }`. Each section has a `type` that controls how it is rendered:

| Section type | Description |
|---|---|
| `header` | Two-column header row grid; left/right slots each hold a `kind` item |
| `footer` | Fixed-to-bottom footer (static in preview mode); same two-column row format |
| `fields` | Horizontal row of labelled field values; supports `full` or `half` width |
| `text` | Static text/notes block with optional title |
| `divider` | Horizontal rule |
| `relationship` | Table of related records with configurable columns |
| `line_items` | Full line-items table with subtotal / tax / discount / total summary |

**Slot `kind` values** (used inside `header`/`footer` rows and `fields` sections):

| Kind | Renders |
|---|---|
| `logo` | Company logo image, or initials fallback box |
| `meta` | Company name, address, phone, email |
| `title` | Document title + record number |
| `field` | A single record field value, with optional label |
| `page_number` | DomPDF `counter(page) / counter(pages)` |
| `date` | Today's date |

**Field `displayStyle` variants**: `title`, `subtitle`, `bold`, `small`, `label`, `status` (colored pill badge), `address` (multi-line), `highlight`, `muted`.

**Half-width pairing**: consecutive `fields` or `relationship` sections with `width: "half"` are automatically paired into a two-column table row.

### Rendering Engine

- **Engine**: [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf) (DomPDF).
- **Blade view**: `resources/views/pdf/layout-driven.blade.php` — a single template that handles all section types.
- **Fonts**: Fira Sans and Heebo TTF files in `resources/fonts/` are registered directly with `FontMetrics` before rendering so CSS can reference them by name. For browser preview, WOFF2 variants are embedded as Base64 `@font-face` data URIs (cached 30 days).
- **Value rendering**: `PdfValueRenderer::render($type, $value, $dropdownValues)` normalises all field types:
  - `date` / `datetime` — formatted via the system `date_format` / `datetime_format` setting
  - `select` / `status` / `dropdown` — resolved to the human-readable label; i18n keys resolved automatically
  - `decimal` / `currency` / `number` — formatted by `PdfNumberFormatter::format()`, which uses PHP `NumberFormatter` (ext-intl) when available, falling back to locale-aware `number_format`
  - `address` — formatted as `street / postal city / state, country` multi-line
  - `checkbox` / `bool` — `globals.pdf.yes` / `globals.pdf.no` translations
  - `PdfValueRenderer` is no longer PDF-exclusive: `ExportController` (see [Record CRUD & Bulk Operations](#5-record-crud--bulk-operations)) reuses it to format the same field types for CSV/JSON export, so exported values match what a generated PDF/preview shows.
- **Currency symbols**: `PdfNumberFormatter::symbol($code)` maps ISO 4217 codes to glyphs for ~25 currencies, falls back to the raw code.
- **Company branding**: logo URL is converted to a Base64 data URI (cached 6 h) so DomPDF can embed it without HTTP round-trips. Local URLs are read from disk; remote URLs fetched with a 2 s timeout.
- **Relationship data**: all relationships referenced by field items across sections are pre-loaded via `RelationshipService::getRelatedRecords()` before rendering; failures are silenced.
- **Record data**: `custom_fields` JSON is merged into a flat array so all field values are accessible via uniform `$record['key']`.
- **Output**: `stream()` — displays inline in the browser.
- **Filename**: `{module-slug}-{record.number|recordId}.pdf`
- **Template selection**: caller may pass `?template={id}` to request a specific template; otherwise the module's default is used.

### Live Preview Panel

`PdfPreviewPanel.vue` POSTs the current section definition to `/settings/pdf-templates/preview`. The controller builds fake data matching each field type (including 3 sample line-items and sample relationship rows), renders the Blade view with `isPreview: true`, and returns HTML. The panel displays this in a scaled `<iframe>` (A4 width 794 px, auto-height).

### Record-Level PDF Modal

`PdfModal.vue` is mounted on the record view when templates exist for the module. It shows a picker of all available templates for the module (default pre-highlighted). Selecting a template:

1. Shows an animated progress bar + spinner while fetching.
2. Fetches `GET /{module}/{recordId}/pdf?template={id}` as a blob.
3. Creates an object URL and triggers a download automatically.
4. Shows a "ready" state with a "Download again" button.
5. On failure, shows error text with Retry and "Choose template" options.

Auto-generates immediately if there is only one template (skips the picker).

### Field Type Coverage in PDFs

All currently supported field types render correctly in PDFs: `text`, `longtext`, `email`, `phone`, `url`, `select`, `status`, `checkbox`, `date`, `datetime`, `integer`, `decimal`, `percentage`, `currency`, `address`, `record`.

---

## 14. Authentication & Security

### Login & Password Reset

Standard Laravel Auth flow:

- `GET /login` — username + password form
- `POST /login` — validates, sets session
- `POST /forgot-password` — sends reset link to email
- `GET /reset-password/{token}` — reset form
- `POST /reset-password` — validates token, updates hashed password

### Session Management

- **Storage & lifetime**: `database` session driver (`sessions` table), 8-hour lifetime (`SESSION_LIFETIME=480`), not expired on browser close, unencrypted payload. Cookie `secure`/`same_site` flags are left at env-driven defaults (no `SESSION_SECURE_COOKIE`/`SESSION_SAME_SITE` set) — `same_site` defaults to `lax`.
- **"Remember me"**: working checkbox on `Login.vue`, passed through to `Auth::attempt($credentials, $remember)` in `AuthController::login()`. Grants a ~400-day `remember_web_...` cookie independent of the 8-hour session (Laravel's default `SessionGuard::$rememberDuration`). Logging out clears it for that device; since the underlying `remember_token` is per-user (not per-device) and non-rotating, logging out on one device also invalidates the remember-me token value on every other device sharing it, even though it doesn't touch their live session cookies.
- **Idle timeout**: there's no separate idle-tracking mechanism — the 8-hour idle expiry *is* the rolling session lifetime (any authenticated request refreshes `last_activity`). A keep-alive heartbeat (`useKeepAlive.js`, mounted globally in `AppLayout.vue`) pings `GET /keep-alive` every 5 minutes while the tab is visible, so an open, visible tab effectively never times out; a backgrounded or closed tab expires after 8 hours idle.
- **419 (expired session) recovery**: a custom exception-render branch (`bootstrap/app.php`) gives a soft recovery instead of Inertia's default full-screen 419 error. It distinguishes, at the moment the exception is thrown, "CSRF token stale but session still alive" (flash a toast, redirect back to the same page — the Vue component never unmounted, so in-progress form edits are untouched) from "session actually died" (redirect to `/login` with the original URL as the intended destination; the in-flight form draft is stashed to `sessionStorage` and restored after re-login, currently wired up only for the record edit page). See `docs/419-session-recovery.md` for the full design.
- **Impersonation and sessions**: `Auth::login()` internally regenerates the session (destroying the old session row, issuing a new ID/CSRF token) — since browser tabs share cookies, any other open tab in the same browser silently picks up the impersonated identity on its next request, with no warning in that tab.
- **Concurrent sessions / multi-device**: fully independent by design — `sessions.user_id` is indexed but not unique, and no single-session-guard middleware exists, so the same user can be logged in on unlimited devices simultaneously with no way to see or revoke another device's session.
- **Logout**: `POST /logout` invalidates only the current session (destroys that session row, regenerates ID/token) and does not affect other devices' live sessions.

**⚠ INCOMPLETE / DOC-CODE MISMATCH** — `docs/session-timeout-guide.md` (a user-facing help article) describes two admin settings as if already shipped: an admin-configurable idle window (30 min–24h) and an admin toggle to hide the "remember me" checkbox entirely. **Neither exists in code** — `docs/419-session-recovery.md` §9 explicitly lists both as "planned follow-up, not built." There is also no active-session listing or "log out other devices" UI. (Impersonation *is* now audited — see [Audit Trail & Impersonation Sessions](#16-audit-trail--impersonation-sessions) — this note is scoped to session management specifically.)

### User Security Fields

The `users` table includes `two_factor_secret`, `failed_login_attempts`, `locked_until`, `last_login_at`, `last_login_ip`, `password_changed_at`. These columns are present but **⚠ INCOMPLETE** — two-factor authentication UI and brute-force lockout logic were not found in controllers or middleware.

### IP Whitelist — Removed

The `ip_whitelists` table and `IpWhitelist` model were added (`96dd5e8`) and then deleted outright two days later (`eab2507`, "cleaned up migration to avoid 500 bugs"). Neither the table, the model, nor any reference to `ip_whitelist(s)` exists anywhere in the codebase today — this isn't an unused-but-present feature, it was built and then removed.

### Locale & Timezone

`SetLocaleFromSettings` middleware reads user preferences on each request and sets the application locale accordingly.

### Local Auto-Login

`LocalAutoLogin` middleware bypasses authentication in local environments. Should be disabled for staging/production.

---

## 15. Onboarding & Setup

Two distinct, sequential flows take a fresh install from zero users to a working CRM.

### A. Instance setup — first admin account

- `SetupController` (`GET|POST /setup/{token}`) is only reachable while `User::count() === 0` — it's a one-time install gate, not reusable after the first user exists.
- The `{token}` is validated against `SetupToken` (`app/Services/Users/SetupTokenService.php`); `app/Mail/SetupInstanceMail.php` sends the link containing it.
- Submitting the form creates the first user with `is_admin: true, is_root: true` (see [Permissions & Roles](#11-permissions--roles)), optionally sets the app locale, logs the user in, and redirects into onboarding below.
- Frontend: `resources/js/Pages/Setup.vue`.

### B. Post-login onboarding wizard

- Gated by a settings flag, not a route guard the user can skip: `app/Http/Middleware/EnsureOnboardingComplete.php` forces **every** request to `onboarding.show` until `Settings::bool('onboarding_completed')` is true (except the `onboarding.*` routes themselves and logout).
- `OnboardingController@show` renders `Inertia::render('Onboarding', ['steps' => ['organisation', 'demo-data', 'invite']])`.
- Step 1, **organisation** — company logo, name, address, phone, email, website, saved via `PUT /settings/company-info`.
- Step 2, **demo-data** — optional toggle; if enabled, `OnboardingController@seedDemoData` runs `DevSeeder`, `RelationshipPopulationSeeder`, `OwnerAssignmentSeeder`, and `DashboardPresetSeeder` (with a bumped memory limit) to populate sample records, relationships, ownership, and default dashboard layouts.
- Step 3, **invite** — delegates to `InviteTeamForm.vue` (see [User Management & Invitations](#12-user-management--invitations)); can be skipped.
- `OnboardingController@finish` (`POST /onboarding/finish`) sets `onboarding_completed = '1'` via `SettingValue`, clears the settings cache, and redirects to the dashboard (or the users index).
- Frontend: `resources/js/Pages/Onboarding.vue` — a 3-step wizard with step-dot progress; each step's completion calls `advance()`, and the final step calls the finish endpoint above.

---

## 16. Audit Trail & Impersonation Sessions

Every create/update/delete on any module record, every relationship link/unlink, and every impersonation session (the login-as itself, not just individual actions taken during it) is logged automatically — no per-module setup required.

### Two tables, two different shapes

| Table | Shape | Purpose |
|---|---|---|
| `audit_logs` | Append-only, one row per event | `created`/`updated`/`deleted`/`linked`/`unlinked` — `module_slug`, `record_id` (both nullable, for batch/system events), `user_id`, `impersonator_id`, `action`, `diff` (JSON), `created_at` |
| `impersonation_sessions` | Mutable, one row per session | Who impersonated whom — `impersonator_id`, `target_user_id`, `ip_address`, `started_at`, `ended_at` (null while ongoing) |

Deliberately schema-generic (the JSON column is named `diff`, not `changes` — naming it `changes` collided with a `protected $changes` property Eloquent's own base `Model` class already declares internally for dirty-tracking, silently breaking reads) so a planned future **Activities** feature can read/write the same `audit_logs` table with a broader `action` vocabulary without a rename.

### Write paths

- **`AuditObserver`** (`app/Observers/AuditObserver.php`) — the primary hook, registered from `BaseModule::booted()` rather than `AppServiceProvider`, since Eloquent keys observer bindings to the literal class passed to `observe()`; late static binding inside `booted()` is what lets one registration correctly self-attach for every concrete module class (`Deal`, `Contact`, etc.). Fires on `created`/`updated`/`deleted` for every `BaseModule` subclass, including `User`.
- **`RecordController`'s bulk `updateMany`/`destroyMany`** — these use query-builder writes (`whereIn(...)->update()`, `chunkById(...)->delete()`) which never fire Eloquent events, so they call the audit write path explicitly, logging one row per batch (not per affected record). `affected_ids` is only captured in explicit-selection mode, not "all matching a filter," to avoid an unbounded array on a large bulk edit (see the incomplete-areas note below).
- **`RelationshipService::link()`/`unlink()`** — logs on **both sides** of the relationship, so either record's own history shows the connection regardless of which side the action was performed from.

### Actor resolution and transparency

Every write auto-resolves `user_id` (the current session identity — the impersonated user's id while impersonating) and `impersonator_id` (the real actor, set only while an impersonation session is active). **The impersonator's identity is always shown, unconditionally, to anyone who can see the row — there is no masking, no Gate, no visibility setting.**

The write path also no-ops entirely when there's no authenticated actor at all (console commands, seeders, queued jobs with no user context) — an enforced invariant of the write path itself, not something each caller opts into. Added after demo-data relationship seeding (`RelationshipPopulationSeeder`) was found logging real audit rows attributed to no one, since `Model::withoutEvents()` (used by `DatabaseSeeder` to suppress factory-driven audit noise) only suppresses Eloquent's event dispatcher — it has no effect on the relationship seeder's direct, non-event write calls.

### `is_calculated` field flag

Fields flagged `is_calculated = true` (`total`/`subtotal`/`tax_amount`/`discount_amount` on any has-line-items module, and on `LineItem` itself — set in `config/default_line_item_fields.php` and `config/stock_fields.php`'s `line_items` section) are excluded from the `updated` diff, since they're recalculated automatically by the line-items panel rather than directly edited by a user. Driven by the flag, not a hardcoded field-name list — any field an admin later marks calculated via the Fields Manager is excluded automatically, no app code change needed.

### Frontend

| Surface | Route | Gate |
|---|---|---|
| Per-record history | Record page action menu → "View History" (modal) | Same visibility as the record itself (no additional per-record ACL exists in the app today) |
| Global audit log | `/settings/audit-trail` | Admin (`AdminMiddleware`) |
| Impersonation sessions | `/settings/impersonation-sessions` | Admin — **deliberately not root-only** |

Both Settings pages filter using the app's real field components (searchable `Select`, `DateTime`) rather than native HTML inputs. Clicking a row in the global audit log (when it has a specific record behind it — bulk-batch rows don't) opens the same per-record History modal, giving full field-aware old→new rendering (resolved field labels, dropdown option labels, `record`-type field names resolved to the related record's own name, locale-aware date formatting) instead of just the list of which fields changed.

### Reference

- `docs/audit-trail-implementation.md` — full technical writeup: schema decisions, every write path, and every bug found and fixed during implementation (including a latent `AdminOnlyModuleScope` bug in `BaseModule::getModuleSlug()`/`moduleDefinition()` that this feature was the first thing to actually exercise, and an `Eloquent Collection::merge()` dedupe-by-primary-key gotcha that silently collapsed per-module field lists when `id` wasn't selected).
- `docs/audit-trail-guide.md` — plain-language, user-facing guide.
- `tests/Feature/Audit/` — automated test coverage (33 tests as of this writing) for every behavior and regression above.

---

## Summary of Incomplete / Half-Built Areas

| Area | Issue |
|---|---|
| `Dashboard::scopeGlobal()` / `scopeForUser()` | Dead code — references a non-existent `owner_id` column and is never called; real per-user scoping is just `where('user_id', ...)` in the controller |
| Module permissions (RBAC) | `can_view/create/edit/delete` flags are global, not per-user/role; no roles/permissions schema exists |
| `ModulePolicy` | **Deleted entirely** (commit `d03f11d`), along with its `authorize()` call sites — not merely unaudited. Zero policy/gate enforcement exists anywhere in the app today |
| Relationship edit UI | No route or component to edit an existing relationship definition (only create/list/delete) |
| Two-factor authentication | DB columns present, no UI or enforcement middleware found |
| IP whitelist | **Removed entirely** (added in `96dd5e8`, deleted in `eab2507` two days later) — not merely present-but-unused |
| Admin-configurable idle session window | Described as shipped in `docs/session-timeout-guide.md`; does not exist — 8h idle window is a static `.env` value |
| Admin toggle to hide "remember me" | Described as shipped in `docs/session-timeout-guide.md`; does not exist — checkbox is always shown |
| Active session listing / revoke-a-device | No UI or backend to view or end sessions on other devices; no single-session-per-user enforcement |
| Audit trail: "all matching" bulk edits | Only explicit-selection bulk edits/deletes capture `affected_ids`; a filtered "select all matching" bulk action only appears as one summary row in the global audit log, not inside any individual record's own history |
| Audit trail: record restore | A deleted record's audit entry keeps its display label, not a full attribute snapshot — restoring a deleted record from the audit trail isn't possible yet |
| Bulk field update: no required-field validation | `RecordController@updateMany` bypasses Eloquent validation entirely — a required field can be mass-cleared across any number of records with no error |
