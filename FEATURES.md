# Cubrel CRM — Feature Inventory

> Verified 2026-07-24 against the current codebase — routes, controllers, models, config, migrations, and Vue components.

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
17. [Bulk Import](#17-bulk-import)
18. [Activities](#18-activities)
19. [Notifications](#19-notifications)
20. [User Preferences](#20-user-preferences)

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
| **Invoices** | `invoices` | number, status, issue_date, due_date, subtotal, discount_amount, tax_amount, total | Yes | Yes |
| **Quotes** | `quotes` | number, status, valid_until, subtotal, discount_amount, tax_amount, total | Yes | Yes |
| **Products** | `products` | sku, category, price, unit, tax_rate, is_active | No | Yes |
| **Support Cases** | `cases` | subject, status, priority, opened_at, closed_at | No | Yes |
| **Tasks** | `tasks` | due_at, status, priority, completed_at (auto-set on completion) | No | Yes |
| **Calls** | `calls` | direction, call_at, duration_minutes, status, outcome | No | Yes |
| **Meetings** | `meetings` | location (address), start_at, end_at, duration (auto-computed), status | No | Yes |
| **Notes** | `notes` | (default fields only) | No | Yes |

Tasks, Calls, Meetings, and Notes are the **activity modules** — see [Activities](#18-activities) for the timeline sidebar, linking, and completion behavior built around them.

### Infrastructure Models

| Model | Purpose |
|---|---|
| `Module` | Registry entry for every CRM module (slug, icon, flags, model/handler class, etc.) |
| `Field` | Defines a field on a module — type, validation rules, display options |
| `Layout` | Stores JSON configuration for list / record / related / linking-panel / line-item views per module |
| `DropdownList` | Named lists of selectable option values used by select/status fields |
| `Relationship` | Declares how two modules relate (one-to-many, many-to-many, one-to-one) |
| `RelationshipLink` | Join record connecting two module records through a `Relationship` |
| `UserInvite` | Pending invitation token with expiry and status |
| `LineItem` | Child row on any module with line items enabled (polymorphic `parent_type`/`parent_id`) |
| `MeetingAttendee` | Person (linked Contact/Lead/User, or external guest) attending a Meeting, with role/RSVP/attendance (see [Activities](#18-activities)) |
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
| `image` | `string` | `ImageField.vue` | image upload validation |
| `duration` | `integer` | `DurationField.vue` | read-only — no edit-mode input at all |

### Composite Field: Address

The `address` type stores a JSON object with sub-fields (street, city, postal_code, country, etc.). It is flagged `isComposite: true` in the registry, meaning the field renderer handles it differently from flat types.

### Computed Field: Duration

The `duration` type stores an integer number of minutes and is read-only in every mode — `DurationField.vue` has no edit-mode input branch at all, so it can only ever be set by the model itself, never typed in directly. On Meetings, `Meeting`'s `saving` hook recomputes it automatically from `start_at`/`end_at` (`start_at->diffInMinutes(end_at)`) every time either changes. Display formats the stored minutes as `1d 2h 30m`-style shorthand, omitting any zero-valued unit.

### Custom Field Storage

All module tables carry a `custom_fields` JSON column. Fields created through the Field Manager are stored here rather than as dedicated columns, unless they are stock (seed-time) fields.

---

## 3. Module System

### Static Modules

18 built-in modules are defined in `config/modules.php` and seeded via `ModulesTableSeeder`. Each module declares:

- `slug`, `name`, `label`, `single_label`, `icon`, `color`, `category`
- `model_class` and `handler_class` (PHP class names)
- `table_name`, `sort_order`
- A set of boolean feature flags — see [Module Flags Reference](#module-flags-reference) below.

**Categories:** `sales` (Leads, Accounts, Contacts, Deals) · `revenue` (Quotes, Orders, Invoices, Products, LineItems) · `activities` (Tasks, Calls, Meetings, Notes) · `support` (Cases) · `system` (Users, User Invites, Settings, Pdf Template)

### Module Flags Reference

Every module — stock or custom — carries the same set of boolean flags on its `Module` record. This is the single source of truth for what each one means and where it can be changed; other sections link back here instead of redefining them.

| Flag | Meaning | Where it's set |
|---|---|---|
| `is_active` | Module is live and queryable — nearly every controller gates on it. A module built through the Module Builder starts `false` and only flips to `true` once the deploy pipeline's final step completes. | Deployment pipeline only — no manual toggle |
| `is_custom` | Distinguishes a Module Builder-created module from a static, built-in one. | Set once at creation, never edited afterward |
| `show_in_sidebar` | Module appears in the left-nav sidebar. | Module Builder (create/edit) and Module Manager |
| `show_in_module_manager` | Module is listed in the admin Module Manager at all. Always `true` for modules created through the Module Builder. | Not user-editable |
| `is_relatable` | Other modules can link to this module's records through relationships, and its records can be picked in `record`-type fields. | Module Builder only (defaults to `true`) |
| `has_owner` | Attaches an `owner_id` (FK → users) to each record for ownership scoping (see [Permissions & Roles](#11-permissions--roles)). Forced `true` for every module created through the Module Builder — not currently configurable. | Not user-editable |
| `has_line_items` | Module supports child line-item rows (see [Line Items](#7-line-items)). | Module Builder (create/edit, before deploy) |
| `is_product_like` | Module is eligible to be picked as a line-item source by other modules (e.g. Products). | Module Builder only |
| `line_item_source_module` | Which `is_product_like` module this module's line items search and snapshot from; falls back to `products`. | Module Builder (before deploy) or Module Manager (after — until `canChangeLineItemSourceModule()` locks it once a line item exists) |
| `is_activity` | Records of this module are activity items — linkable to other records, appear in their timelines (see [Activities](#18-activities)). | Module Builder only, not editable after creation |
| `has_activity` | Records of this module get the activity timeline sidebar (see [Activities](#18-activities)). | Module Builder only, not editable after creation |

While a module is mid-creation in the Module Builder (`is_draft = true`), it's soft-locked to the editing admin (`locked_by`/`locked_until`, a rolling 2-hour window) so two admins can't clobber the same draft concurrently.

### Line Items Are Configurable Per Module

`has_line_items` is not hard-wired to Orders/Invoices/Quotes and does not imply "snapshot from Products." Any module — standard or custom, built through the Module Builder — can enable line items:

- `is_product_like` flags a module (e.g. Products) as eligible to be picked as a **line-item source** by other modules.
- `line_item_source_module` on the host module (Orders, Invoices, Quotes, or any custom module) picks which product-like module its line items search and snapshot from, defaulting to `products`.
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
   - `create-table` — runs the migration
   - `activate-fields` — marks draft fields as active
   - `rollback` — undoes deployment
5. Progress is shown in `Builder/DeployProgressModal.vue`

Covered by an automated end-to-end test: `tests/Feature/Modules/ModuleBuilderWorkflowTest.php` drives the real deploy pipeline over HTTP, asserts the generated PHP files actually land on disk (model + handler), and verifies the resulting module supports full CRUD.

### Handler Pattern

Every module has a handler class (extends `BaseModuleHandler`) responsible for:

- `query()` — base Eloquent query with scope/filter application
- `getListData()` — data for list views (pagination, sorting, column selection)
- `getRecordData()` — data for single-record views (field values, related panels)
- Declaring searchable columns and relationship eager-loads

---

## 4. Layouts

### Layout Types

Each module can have up to five layout types stored in the `layouts` table as JSON blobs. Default layouts are defined in `config/module_layouts/{module}.php` (and, for the newest type below, `config/default_layouts.php`). PDF output layout is a separate, standalone concern — see [PDF Generation](#13-pdf-generation).

| Type | Purpose | UI editor |
|---|---|---|
| `list` | Columns shown in the module list table | `LayoutListEditor.vue` |
| `record` | Sections and fields shown on a single record page | `LayoutRecordEditor.vue` |
| `related` | Columns shown in related-record subpanels | `LayoutRelatedEditor.vue` |
| `linkingPanel` | Columns shown in the record-selector overlay | `LayoutLinkingPanelEditor.vue` |
| `lineItemsSnapshot` | Which line-item fields appear on the create/edit sheet, and which field on the configured line-item source module autofills each one (only relevant when `has_line_items` is true) | `LayoutLineItemMappingEditor.vue` |

### Layout Editor

Routes under `/settings/modules/{module}/layouts` let admins view and update layouts.

- `LayoutManagerController@show` — lists layout types for a module
- `LayoutManagerController@edit` — loads current layout JSON
- `LayoutManagerController@store` — persists updated layout JSON

The Vue editors (`Settings/Layouts/Edit.vue`, `Settings/Layouts/Record.vue`) provide a drag-and-drop interface for reordering fields and toggling per-field options (`readonly`, `required`, `sortable`, `hidden`).

Layout config per module specifies fields by name and includes display metadata (label translation key, readonly flag, required flag, sortable flag).

Within the `record` layout editor, a module's "Line Items" section (`has_line_items: true`) embeds its own `LayoutListEditor` instance, letting admins pick and reorder which `line_items` module fields appear as table columns — a separate field pool from the host module's own fields.

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

A bulk field update applies one field/value pair uniformly to every selected record. If the target field is required, the whole request is rejected up front when the new value would be empty (`MassUpdateZone.vue` shows the same validation client-side that single-record editing uses). The value field also supports `record`-type targets (e.g. bulk-reassigning "Owned by") via the same `RecordSelectorDrawer` picker used on the record page.

### Export

`ExportController` provides CSV and JSON export, both single-record and bulk:

| Action | Route | Controller method |
|---|---|---|
| Export one record | `GET /{module}/{recordId}/export?format=json\|csv` | `ExportController@export` |
| Export many | `POST /{module}/export` | `ExportController@exportMany` |

- Bulk export reuses the same three selection modes as bulk delete/update.
- Every create/update/delete above (single-record and bulk) is logged automatically — see [Audit Trail & Impersonation Sessions](#16-audit-trail--impersonation-sessions).
- Supported formats: JSON and CSV.
- A single record's export includes a "line items" section appended after the main row when the module has line items enabled; bulk export omits line items (rows would have inconsistent shape across records).
- Field values are formatted for export the same way they're formatted for PDFs, so exported values match what a generated PDF/preview shows.
- Frontend: `ExportModal.vue` (format picker, single vs. bulk mode, downloads via blob response) opened from `ListActions/ExportZone.vue` on the list view.

---

## 6. Relationships

### Definition

Relationships between modules are declared in the `relationships` table and seeded by `RelationshipSeeder`. Stored shape is always one of `one-to-many`, `many-to-many`, `one-to-one` — `left_module` is the "one"/parent side, `right_module` is the "many"/child side (for `one-to-many`). System relationships (`is_system=true`) are non-deletable.

The Create form additionally offers a **`many-to-one`** option, so a relationship can be created from either side without needing to know the left/right convention — picking "many-to-one" from the *child* module's own page (e.g. "many Deals belong to one Account," created from Deals) automatically swaps left/right and stores it as `one-to-many`; `many-to-one` is purely a creation-time convenience, never persisted as a `type` value itself. See [Relationships Guide](docs/guides/relationships-guide.md) and [Relationships Implementation](docs/dev/relationships-implementation.md).

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

Relationships are deliberately not editable after creation — only create/list/delete. Deleting a **system** relationship (`is_system`) is blocked both server-side and client-side; deleting a **custom** relationship shows a confirmation dialog that includes the number of existing links when there are any. Deleting a relationship strips its `related`-panel reference from **both** modules' layouts (not just whichever module's settings page the delete was performed from), since a relationship panel is normally configured on both sides (e.g. Accounts shows a "Deals" panel, Deals shows an "Account" panel).

---

## 7. Line Items

Any module can support child line item rows — not just Orders, Invoices, and Quotes. A line item is a polymorphic child (`parent_type`/`parent_id`) that optionally references a record (`product_id`) in whichever module is configured as the host module's **line-item source** (see [Module System](#3-module-system)).

### Per-item calculations (server-side, `LineItem::calculateTotals()`)

```
subtotal        = unit_price × quantity
discount_amount = subtotal × (discount / 100)
tax_amount      = (subtotal − discount_amount) × (tax_rate / 100)
total           = subtotal − discount_amount + tax_amount
```

`discount` is percentage-only; `discount_amount` is always derived from it.

### Configurable snapshot mapping

When a line item's source-module record is picked (e.g. a Product on an Invoice line), which of its fields autofill which line-item fields is configurable per module via the **`lineItemsSnapshot`** layout type:

- Defined per module in the `layouts` table (falls back to `config/default_layouts.php`'s `lineItemsSnapshot` entry, e.g. `unit_price` ← source module's `price` field).
- Edited through `LayoutLineItemMappingEditor.vue`, reached the same way as other layout types (`/settings/modules/{module}/layouts`).
- On the record page, `LineItemsPanel.vue` walks the mapping generically and copies each mapped source field into the corresponding line-item field.

The line-items table's **visible columns** are likewise configurable — they come from the host module's record layout, inside the "Line Items" section's own `layout` array.

### API

| Action | Route |
|---|---|
| List | `GET /line-items?parent_type=X&parent_id=Y` |
| Create | `POST /line-items` |
| Update | `PUT /line-items/{lineItem}` |
| Delete | `DELETE /line-items/{lineItem}` |
| Reorder | `POST /line-items/reorder` (updates `sort_order`) |

The record page shows line items when the module has line items enabled.

### Parent total roll-up (server-side, `LineItemTotalsObserver`)

Registered on `LineItem` from `AppServiceProvider::boot()`, this observer recomputes the parent record's `subtotal`/`discount_amount`/`tax_amount`/`total` by summing all its line items on every save/delete — not dependent on anyone having that record's page open. `total`/`subtotal`/`tax_amount`/`discount_amount` are flagged `is_calculated`, so the recompute doesn't create audit-log noise. `Record.vue` mirrors the recalculated totals into the open record's form for immediate display.

---

## 8. Search

### Global Search

Route `GET /search` handled by `SearchController`, orchestrated through `GlobalSearchService`. `BaseModule` uses Laravel Scout's `Searchable` trait, backed by the `database` driver. `UniversalSearcher` performs the actual per-module search. The `GlobalSearch.vue` overlay and `SearchOverlay.vue` component manage the front-end.

### Related Field Search

Route `GET /relatedfield/search/{related_module}` handled by `RelatedFieldController`. This is the typeahead endpoint used by the `RelatedRecord.vue` field component when the user types to find a record to link.

### Advanced List Filtering

List views have a full condition-based filter builder, not just column-level equality:

- **Filterable fields**: a field is filterable if it's one of the stock keys or has `Field::filterable === true`; `owner_id` is excluded on modules without `has_owner`.
- **Operators per field type** are declared in `config/filter_operators.php` — e.g. `equals`/`not_equals`/`contains`/`starts_with` for text, `greater_than`/`less_than`/`between` for numbers, `before`/`after`/`between` for dates, `is_empty`/`is_not_empty` for any type — enforced both server-side and in the picker UI.
- **Conditions** are an array of `{field, operator, value}` combined with a `match_type` of `all` (AND) or `any` (OR). A condition's value can be the literal `@current_user`, substituted with the logged-in user's ID — this is how "my records" style filters work.
- **Saved / shared / system filters** are a real model (`ListFilter`): a filter has `is_shared` (visible to the whole team), `is_system` (seeded, e.g. `my_records`, non-deletable), `is_global`, `conditions` (JSON), `match_type`, and `last_used`. Full CRUD at `POST|PUT|DELETE /{module}/filters[/{filter}]`.
- **Frontend**: `ListActions/FilterZone.vue` — multi-condition builder, save/edit/delete named filters, a "share with team" toggle, an AND/OR switch, and an overflow panel separating private vs. shared filters sorted by last-used.
- **Selecting a saved filter** via `?filter=<slug|uuid>` in the URL applies its conditions and bumps `last_used` — so links stay shareable.

### Sorting

`BaseModuleHandler::getListData()` validates the requested sort column against the model's fillable list and defaults to `created_at desc`.

---

## 9. Dashboard

Route `GET /` → `DashboardController@index`. Dashboards are fully personalizable per user, not a fixed widget set.

### Personalized, persisted layout

- `Dashboard` model: per-user `layout` (JSON) holding the full widget configuration.
- `DashboardController@index` loads the user's own saved layout, falling back to a role-based default (`admin`/`read_only` variants) only if the user has never saved their own.
- `DashboardController@saveLayout` persists the layout.
- `DashboardController@widgetData` resolves live data per widget type (`time-series`, `metric`, `breakdown`, `record-list`, `people`) through `AggregationService`, automatically owner-scoped for non-admin users. Companion endpoints let each widget's config form populate its field/relationship/filter pickers per module.

### Editing UI

`resources/js/Pages/Dashboard/Index.vue` provides an explicit edit mode:

- HTML5 drag-and-drop reordering of widgets with a ghost placeholder and a masonry-style row-span layout.
- Add/remove/reconfigure widgets — auto-persists immediately outside of bulk-edit mode, or via explicit Save/Cancel while editing.
- Two widget kinds coexist: legacy fixed string-id widgets (not configurable) and typed, configurable instances added through `AddWidgetPanel.vue`.
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

---

## 10. Settings

Settings are organized into groups, each with one or more items, each holding key-value rows in `setting_values`. Most settings pages are rendered by `Settings/Page.vue`.

### System

| Item | Setting | Type |
|---|---|---|
| **Locale** | `app_locale`, `default_locale` | Language pickers |
| | `date_format`, `datetime_format` | Dropdown (locale-aware examples) |
| | `timezone` | Dropdown (IANA zones) |
| | `first_day_of_week` | Integer |
| | `default_currency` | Dropdown |
| | `multi_currency_mode` | Checkbox |
| **Style** | `theme` | Dropdown (light/dark/system) |
| | `primary_color`, `secondary_color`, `success_color`, `danger_color` | Color pickers |
| | `border_radius` | Integer/slider |
| | `table_striped_rows` | Checkbox |
| | `use_individual_module_colors` | Checkbox |
| **Display Defaults** | `list_view_limit`, `linking_panel_limit`, `related_panel_limit` | Integer |
| **Data Retention** | `retention_notifications_days`, `retention_audit_logs_days`, `retention_userinvites_days`, `retention_failed_jobs_days` | Integer (days) |

The `SettingsController` provides live preview options for date/datetime formats based on the selected timezone. These are organization-wide defaults set by an admin — see [User Preferences](#20-user-preferences) for the separate, per-user override of these same settings.

### Data Retention & Automatic Cleanup

Old, no-longer-relevant rows are pruned automatically on a daily schedule rather than accumulating indefinitely:

| What's cleaned | Configurable window (default) |
|---|---|
| In-app/email notifications | `retention_notifications_days` (180 days) |
| Audit log entries (and their bulk-batch affected-record rows, removed together automatically) | `retention_audit_logs_days` (730 days) |
| Resolved user invites — accepted or expired | `retention_userinvites_days` (365 days) |
| Failed queue jobs | `retention_failed_jobs_days` (30 days) |

Each window is an organization-wide default editable at `/settings/system/data-retention`, following the same admin-configurable settings pattern used throughout [Settings](#10-settings). Pending user invites are never pruned regardless of age — only ones already accepted or expired are eligible. Expired, unused password-reset tokens are cleared daily as well, governed by the existing password-reset expiry window rather than a separate retention setting.



### Customisation

Module Builder, Module Manager, and Dropdown Manager (below) are grouped here in Settings navigation.

### Company

Organisation identity — company name, address, phone, email, website, logo — editable at `/settings/company/company-info` (also the first step of onboarding, see [Onboarding & Setup](#15-onboarding--setup)).

### Field Manager

Admins manage per-module field definitions at `/settings/modules/{module}/fields`:

- **List** — shows editable fields with type and status; custom fields additionally show a `records_using` count (how many of the module's records have a non-null value for that field).
- **Create** — creates a new field (label, type, validation options, dropdown list).
- **Edit** — update label, required flag, validation rules, etc.
- **Delete** — custom fields only; stock/seed-time fields back a real DB column and are protected. Shows a confirmation dialog with the `records_using` count when there are any. Deleting a field also strips it from that module's `list`/`record`/`linkingPanel` layouts, and every field-rendering surface in the app gracefully skips a field reference that no longer resolves.

### Dropdown Manager

Named dropdown option lists live at `/settings/dropdowns`. Lists can be created, edited inline, or created-and-immediately-attached to a field. Components: `DropdownSelector.vue`, `CreateNewDropdownListModal.vue`, `EditDropdownListModal.vue`.

### Status Fields

A `status`-typed field's dropdown list gets a richer editor than a plain `select` list: each option carries its own color, background color, and icon — authored inline via a color picker and the shared icon picker (`StatusOptionRowFields.vue`, with a live badge preview), with drag-to-reorder for option order. Status rows are edited in place (no separate staging/commit step); a row's `value` key auto-derives from its label and locks once saved, so later label edits never rewrite a key other records may reference. Status fields render the result as colored pill badges (`StatusField.vue`). Every option requires its own explicit color/background/icon — there is no predefined style palette to fall back on (a neutral gray/circle-icon default is used only when a value is genuinely unset).

Whether a list gets this rich editor is a property of the **list itself**, not just the field that happens to be editing it: `DropdownList.is_status` decides which editor renders everywhere the list can be reached — the standalone Dropdown Manager (`List.vue`, which marks flagged lists with a "Status" badge, and `Record.vue`), and the field-creation modals (`CreateNewDropdownListModal.vue`/`EditDropdownListModal.vue`). A list edited in status context "sticks" as status from then on, and lists backing the stock status fields seeded at install are flagged automatically.

### PDF Templates

See [PDF Generation](#13-pdf-generation).

### Audit

See [Audit Trail & Impersonation Sessions](#16-audit-trail--impersonation-sessions).

### Notifications

See [Notifications](#19-notifications).

---

## 11. Permissions & Roles

### Role Model

The system has two role levels:

| Flag | Meaning |
|---|---|
| `is_root = true` | Super admin — cannot be demoted; bypasses all checks |
| `is_admin = true` | Admin — accesses settings, user management, impersonation |
| neither | Regular user |

`AdminMiddleware` protects all routes under `/settings`, `/users`, and `/invites`.

### Module Visibility

Whether a regular (non-admin) user can see a module at all is a single binary split:

- `is_active` and `show_in_sidebar`/`show_in_module_manager` (see [Module Flags Reference](#module-flags-reference)) control visibility, evaluated the same way for every non-admin user.
- The `users` and `settings` modules are always hidden from non-admins; admins see everything.
- Once a module is visible to a regular user, that user has full create/edit/delete on its records and can link/unlink it via relationships.

### Impersonation

Admins can impersonate any user via `POST /users/{user}/impersonate`. A persistent banner (`ImpersonationBanner.vue`) is shown while impersonating. `POST /leaveimpersonate` returns to the original admin session. Every impersonation session (who, whom, from what IP, start/end) and every action taken while impersonating is logged — see [Audit Trail & Impersonation Sessions](#16-audit-trail--impersonation-sessions).

### Record Ownership

`has_owner` modules (see [Module Flags Reference](#module-flags-reference)) attach `owner_id` (FK → users) to each record. Dashboard widgets and list queries can be scoped to the authenticated user's owned records via `OwnershipService`.

---

## 12. User Management & Invitations

### User CRUD (Admin-only)

Routes under `/users` cover full CRUD for user accounts. Admins can set `is_admin`, assign `title`, `phone`, `mobile`, `avatar`. Password is hashed on save.

| Route | Purpose |
|---|---|
| `GET /users` | Paginated user list |
| `GET /users/create` | Create form |
| `POST /users` | Store user |
| `GET /users/{id}` | Show user record |
| `PUT /users/{id}` | Update user |
| `POST /users/{user}/reset-password` | Admin-initiated password reset |
| `POST /users/{user}/send-set-password` | Send a set-password link to the user |
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

### User Profile

`GET /profile` and `PUT /profile` let the authenticated user update their own name, email, phone, mobile, title, description, and avatar.

---

## 13. PDF Generation

### PDF Templates

Templates are stored in the `pdf_templates` table. Each template is fully standalone — it owns its layout `definition` (JSON) directly.

| Column | Type | Purpose |
|---|---|---|
| `id` | UUID | PK |
| `module_slug` | string | Which module the template belongs to |
| `name` | string | Human-readable name |
| `blade_view` | string | Blade view path |
| `description` | string (nullable) | Optional admin description |
| `is_default` | boolean | Only one default per module at a time |
| `definition` | JSON | Section tree that drives the rendered output |

### Template Manager (Settings)

Full CRUD at `/settings/pdf-templates`:

| Route | Method | Action |
|---|---|---|
| `/settings/pdf-templates` | GET | List — paginated, searchable by name or module label |
| `/settings/pdf-templates/create?module=X` | GET | Create — pre-loads fields, relationships, line-item fields for selected module |
| `/settings/pdf-templates` | POST | Store — validates the section definition, ensures a single default per module |
| `/settings/pdf-templates/preview` | POST | Live preview — server-renders with sample data |
| `/settings/pdf-templates/{id}` | GET/PUT/DELETE | Edit / update / delete |
| `/settings/pdf-templates/{id}/default` | POST | Atomically sets one default, clears others |

### Section-Based Definition Format

A template `definition` is a JSON object of sections. Each section has a `type` that controls how it is rendered:

| Section type | Description |
|---|---|
| `header` | Two-column header row grid; left/right slots each hold a `kind` item |
| `footer` | Fixed-to-bottom footer; same two-column row format |
| `fields` | Horizontal row of labelled field values; supports `full` or `half` width |
| `text` | Static text/notes block with optional title |
| `divider` | Horizontal rule |
| `relationship` | Table of related records with configurable columns |
| `line_items` | Full line-items table with subtotal / tax / discount / total summary |

**Slot `kind` values** (used inside `header`/`footer` rows and `fields` sections): `logo`, `meta` (company name/address/phone/email), `title` (document title + record number), `field` (a single record field value), `page_number`, `date`.

**Field `displayStyle` variants**: `title`, `subtitle`, `bold`, `small`, `label`, `status` (colored pill badge), `address` (multi-line), `highlight`, `muted`.

**Half-width pairing**: consecutive `fields` or `relationship` sections with `width: "half"` are automatically paired into a two-column table row.

### Rendering Engine

- **Engine**: [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf) (DomPDF).
- **Fonts**: Fira Sans and Heebo, registered directly with the PDF engine so CSS can reference them by name.
- **Value rendering**: normalises every field type for output — dates/datetimes via the system format settings, select/status/dropdown values resolved to their human-readable label, decimals/currency/numbers formatted locale-aware, addresses formatted multi-line, checkboxes as yes/no.
- **Currency symbols**: maps 23 common ISO 4217 currency codes to their glyphs, falling back to the raw code for anything else.
- **Company branding**: logo is embedded directly so PDFs render without external HTTP round-trips.
- **Relationship data**: all relationships referenced by field items across sections are pre-loaded before rendering.
- **Output**: streams inline in the browser; filename follows `{module-slug}-{record.number|recordId}.pdf`.
- **Template selection**: caller may request a specific template; otherwise the module's default is used.

### Live Preview Panel

`PdfPreviewPanel.vue` posts the current section definition to the preview endpoint, which builds representative sample data (including sample line-items and relationship rows) and returns rendered HTML, displayed in a scaled A4-width iframe.

### Record-Level PDF Modal

`PdfModal.vue` is mounted on the record view when templates exist for the module. It shows a picker of all available templates for the module (default pre-highlighted), fetches the generated PDF as a blob, and triggers a download automatically — auto-generating immediately if there is only one template.

### Field Type Coverage in PDFs

All field types render in PDF output: text, longtext, email, phone, url, select, status, checkbox, date, datetime, integer, decimal, percentage, currency, address, record.

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

- **Storage & lifetime**: database-backed sessions, 8-hour lifetime, not expired on browser close.
- **"Remember me"**: checkbox on the login screen grants a long-lived (~400-day) cookie independent of the normal session, so a device stays signed in across browser restarts. Signing out explicitly clears it.
- **Idle timeout**: a keep-alive heartbeat keeps an open, visible tab signed in indefinitely; a backgrounded or closed tab expires after the idle window.
- **Session recovery**: an expired-session-mid-edit is handled gracefully — a stale-but-still-alive session flashes a quick "please save again" notice without losing the in-progress edit; a fully expired session redirects to login and restores the in-progress edit after re-authentication (currently wired up for the record edit page).
- **Multi-device**: fully independent by design — the same user can be signed in on multiple devices simultaneously.
- **Logout**: invalidates only the current device's session.

### Locale & Timezone

Request-time middleware applies the active user's locale and timezone preferences automatically.

---

## 15. Onboarding & Setup

Two distinct, sequential flows take a fresh install from zero users to a working CRM.

### A. Instance setup — first admin account

- `SetupController` (`GET|POST /setup/{token}`) is only reachable while no users exist yet — a one-time install gate.
- The `{token}` is validated against a dedicated setup-token service; the setup link is emailed to the installer.
- Submitting the form creates the first user as a root admin, optionally sets the app locale, logs the user in, and redirects into onboarding below.
- Frontend: `resources/js/Pages/Setup.vue`.

### B. Post-login onboarding wizard

- Every request is gated to the onboarding flow until it's marked complete.
- Step 1, **organisation** — company logo, name, address, phone, email, website.
- Step 2, **demo-data** — optional toggle to populate sample records, relationships, ownership, and default dashboard layouts.
- Step 3, **invite** — invite team members; can be skipped.
- Frontend: `resources/js/Pages/Onboarding.vue` — a 3-step wizard with step-dot progress.

---

## 16. Audit Trail & Impersonation Sessions

Every create/update/delete on any module record, every relationship link/unlink, and every impersonation session (the login-as itself, not just individual actions taken during it) is logged automatically — no per-module setup required.

### Two tables, two different shapes

| Table | Shape | Purpose |
|---|---|---|
| `audit_logs` | Append-only, one row per event | `created`/`updated`/`deleted`/`linked`/`unlinked` — module, record, actor, action, and a JSON diff |
| `audit_log_affected_records` | Append-only, one row per (batch, record) pair | Lets a bulk-operation batch row be attributed back to every individual record it touched, each with its own prior value |
| `impersonation_sessions` | Mutable, one row per session | Who impersonated whom, from what IP, start/end (null while ongoing) |

### Write paths

- **Field-level changes** on any module record (create/update/delete) are captured automatically, with no per-module setup.
- **Bulk update/delete** log one row per batch (not per affected record) for both selection modes (explicit selection and "all matching a filter"). Each affected record's own prior value (for updates) or display label (for deletes) is captured individually, so a record's own history shows what it actually changed from — not just what the whole batch was set to.
- **Linking/unlinking** relationships logs on **both sides** of the relationship, so either record's own history shows the connection regardless of which side the action was performed from.

Deletion is a hard delete — a display label is captured at delete time so the audit trail shows what was deleted, though a deleted record itself isn't recoverable from its audit entry.

### Actor resolution and transparency

Every write auto-resolves the current session identity and, if an impersonation session is active, the real actor behind it. **The impersonator's identity is always shown, unconditionally, to anyone who can see the row.**

### Calculated fields

Fields flagged as calculated (e.g. `total`/`subtotal`/`tax_amount`/`discount_amount` on any module with line items) are excluded from the change diff, since they're recalculated automatically rather than directly edited by a user.

### Frontend

| Surface | Route | Gate |
|---|---|---|
| Per-record history | Record page action menu → "View History" (modal) | Same visibility as the record itself |
| Global audit log | `/settings/audit-trail` | Admin |
| Impersonation sessions | `/settings/impersonation-sessions` | Admin |

Both Settings pages filter using the app's real field components (searchable dropdowns, date pickers) rather than native HTML inputs. Clicking a row in the global audit log opens one of two views depending on the row: a row with a specific record behind it opens the per-record History modal, with full field-aware old→new rendering (resolved field labels, dropdown option labels, related-record names, locale-aware date formatting). A bulk-batch row instead opens a breakdown view listing every record the batch touched — each with its own resolved label and, for updates, its own old→new value — paginated so this stays fast even for a batch spanning an entire module's table.

Within the per-record History modal itself, a bulk-batch entry the viewed record was part of shows both the batch-wide summary and a real old→new diff row for that specific record's own change.

### Reference

- `docs/dev/audit-trail-implementation.md` — full technical writeup.
- `docs/guides/audit-trail-guide.md` — plain-language, user-facing guide.
- `tests/Feature/Audit/` — automated test coverage (39 tests as of this writing).

---

## 17. Bulk Import

### Import Wizard

Any module's list view offers an **Import** option (next to Export in the actions dropdown) that creates or updates many records at once from an uploaded file, through a guided multi-step wizard: upload → map columns → optional match field → confirm → results.

| Action | Route | Controller method |
|---|---|---|
| Upload & preview (parse headers/sample rows) | `POST /{module}/import/preview` | `ImportController@preview` |
| Start import | `POST /{module}/import/{import}/start` | `ImportController@start` |
| Poll progress | `GET /{module}/import/{import}/status` | `ImportController@status` |

Frontend: `ImportModal.vue` (wizard flow) and `ImportFieldSelect.vue` (per-column field picker).

### Supported files

CSV (comma- or semicolon-separated, delimiter auto-detected via `CsvDelimiterDetector`) or JSON (an array of record objects). Limits are configurable in `config/import.php`: up to 10MB and 50,000 rows per file; files of 200 rows or fewer process synchronously and land straight on the results screen, larger files are queued (`ProcessImportJob`) with a polling progress bar.

### Column mapping

Every column detected in the file maps to a target field, or "Don't import" to skip it; any field required by the module must be mapped before the import can start. `Import::mappableFields()` scopes the offered targets to writable fields — readonly and calculated fields are excluded, as are field types with no single-column text mapping (`record`, `address`, `image` — configurable via `import.excluded_fields`).

### Value coercion

`ImportValueCoercer` normalizes incoming text per field type: dropdown/status fields match against the option's displayed label in any supported language, not just its raw stored value; checkbox fields accept common yes/no wordings (`config('import.checkbox_true_values')`/`checkbox_false_values`) in addition to Cubrel's own translated wording; dates are parsed flexibly; numbers tolerate currency symbols and thousands separators.

### Matching to avoid duplicates

An optional "match existing records on" field (e.g. Email) lets a row update an existing record — matched by that field's value, including custom fields — instead of creating a duplicate. Left unset, every row becomes a new record.

### Results & error handling

Per-row failures (a bad value, a violated required field) are skipped individually rather than failing the whole file, and recorded with the row number and reason, capped at 100 stored errors (`errors_truncated` flag beyond that). The results screen reports created/updated/skipped counts plus the per-row error list.

### Reference

- `docs/guides/en/import-guide.md` — plain-language, user-facing guide.

---

## 18. Activities

Tasks, Calls, Meetings, and Notes are first-class core modules (see [Data Modeling](#1-data-modeling)) that plug into every other module through a dedicated activity timeline sidebar — a running, chronological record of what's happened on and around a given record.

### Two Module Flags Drive It

Any module can opt into this system through the `is_activity`/`has_activity` flags on `Module` (see [Module Flags Reference](#module-flags-reference)), set when the module is created in the Module Builder:

- `is_activity` — set on Tasks, Calls, Meetings, Notes.
- `has_activity` — set on Leads, Accounts, Contacts, Deals, Support Cases, Quotes, Orders, Invoices.

Setting either flag on a module automatically generates the many-to-many relationships needed to link it to every module on the other side (`RelationshipService::syncActivityRelationships()`) — no manual relationship setup required. The same generation runs for every existing `is_activity`/`has_activity` module at install time.

### The Activity Sidebar

A collapsible panel appears on the record page of any `has_activity` module, positioned beneath the record header and sized to its own content:

- **Tabs** — All, Activity (linked Tasks/Calls/Meetings/Notes only), Changes (field-level history only); the selected tab persists across page reloads.
- **Add menu** — a single "+ Add" control opens a dropdown of every `is_activity` module, so the list of available activity types stays compact regardless of how many exist. Picking one opens the module's normal create form and automatically links the new record to the one being viewed on save.
- **Timeline** — entries render on a connecting rail with a per-type icon, relative timestamps ("2h ago", "Today 14:30", "Yesterday 09:12", falling back to a plain date), and status/link changes rendered as the same colored pill badges used elsewhere in the app.
- **Task completion** — a Task entry in the timeline renders as a live checkbox; checking it updates the real Task's status immediately, no page navigation required.
- The sidebar refreshes automatically after saving the record itself or linking a record through the Related tab, in addition to right after creating a new linked activity.

### What Powers the Timeline

Each entry comes from one of two sources, merged and sorted chronologically by `RecordTimelineController`:

- **Linked activity records** — live Task/Call/Meeting/Note rows linked to the record through the relationships above, fetched fresh (not a frozen snapshot), which is what makes the Task checkbox interactive.
- **Audit history** — the same `audit_logs` entries that back the record's audit trail (see [Audit Trail & Impersonation Sessions](#16-audit-trail--impersonation-sessions)), including field changes, linking/unlinking, and dedicated `line_item.added`/`line_item.removed` entries whenever a line item is added to or removed from the record.

### Linking Activities to Records

Activities use the same many-to-many relationship system as any other module relationship (see [Relationships](#6-relationships)) — a Meeting, for example, can be linked to more than one record at once (an attendee's Account alongside the Deal being discussed), not just a single parent. By default, activity relationships stay out of the standard Related-tab panel list so they don't duplicate what the timeline already shows; an admin can still add one to a module's Related layout through the Layout Builder if they want it to also appear there.

### Route

| Action | Route |
|---|---|
| Get a record's merged activity/audit timeline | `GET /modules/{module}/{recordId}/timeline` |

### Meeting Attendees

**Meetings is a special case.** Everything else in this document that's per-module — line items, activity linking, the timeline sidebar — is a flag any module can opt into through the Module Builder (see [Module Flags Reference](#module-flags-reference)). Attendees is not: it is built specifically for the Meetings module (a dedicated `meeting_attendees` table, `MeetingOrganizerObserver`, `AttendeesPanel.vue`, and a dedicated Layout Builder section), not a general capability, and there's no flag that turns it on for another module.

Meetings carry a dedicated attendee list, separate from the generic activity-linking relationships above — it tracks *who* is invited and their individual RSVP/attendance, not just which records the meeting relates to.

#### Data

`meeting_attendees` is a plain FK to `meetings`, not polymorphic — this is Meetings-only, not a general per-module capability like line items:

| Column | Purpose |
|---|---|
| `source_type` / `source_id` | Nullable link to a Contact, Lead, or User record. Null on both means an external guest. |
| `name` / `email` | Snapshotted at add time for linked attendees; typed directly for external guests. |
| `role` | `organizer` \| `required` \| `optional` |
| `rsvp_status` | `invited` \| `accepted` \| `declined` \| `tentative` |
| `attendance_status` | `attended` \| `no_show`, null until recorded |
| `responded_at` | Set automatically whenever `rsvp_status` moves off `invited` |

Valid values for `role`, `rsvp_status`, and `attendance_status`, plus which modules can be picked as an attendee source, are defined once in `config/meeting_attendees.php` and shared to the frontend via Inertia (`meetingAttendeeOptions`) — the option lists shown in the UI and the values accepted by server-side validation read from the same source.

#### Auto-organizer, single-organizer enforcement

- `MeetingOrganizerObserver` fires on every `Meeting` creation and adds the meeting's owner as an attendee with `role: organizer`, `rsvp_status: accepted` — every meeting has at least one attendee from the moment it's created.
- Only one attendee can hold `role: organizer` at a time. `MeetingAttendeeController::demoteOtherOrganizers()` runs after any create/update that sets a new organizer, demoting whoever held it before to `required`. This is enforced server-side and mirrored client-side while attendees are still staged (unsaved), so picking a second "Organizer" in the same batch gives immediate feedback instead of silently losing the earlier one on save.

#### Adding attendees — internal, external, or mixed, in one batch

`AttendeeOverlay.vue` stages attendees into a single list before saving, regardless of source:

- **Internal** — pick a source module (Contact, Lead, or User), then search and multi-select via `RecordMultiSelectorDrawer.vue`, a generic checkbox-based multi-select record picker (not specific to this feature).
- **External** — a small inline form (name, email, role) with an "Add another guest" action. Email is required and format-validated (`emailValidate`) here, since it's the only way to reach someone with no linked record.
- Both feed the same staged list, shown beneath either tab regardless of which is active, so one save can mix internal picks and external guests together. Each staged entry keeps its own editable role.
- Saving loops through the staged list and posts each attendee individually; a partial failure leaves only the failed entries staged for retry rather than losing the whole batch.

#### Record view panel

`AttendeesPanel.vue` lists attendees sorted Organizer → Required → Optional (rank driven by `config/meeting_attendees.php`, re-applied client-side so a new or edited row lands correctly without a refetch):

- Avatar and highlight color reflect the *linked module's* color (Contact/Lead/User), not the Meeting's own — external guests get a neutral gray. Colors respect the `use_individual_module_colors` setting the same way every other related-record display does.
- RSVP and attendance render as colored pill badges. Their edit controls stay hidden until a row is hovered (or actively mid-edit), toggled by a pen/check icon rather than a persistent inline dropdown.
- "Mark all attended" bulk-sets every attendee with no recorded attendance to `attended`.

#### Layout Builder integration

The Attendees section is a fixed, non-field layout section (`has_attendees: true` in `config/module_layouts/meetings.php`). In `LayoutRecordEditor.vue` it renders as a locked placeholder ("Attendees will be generated automatically") rather than an editable drop zone, so no field can be dragged into it. It can still be removed or re-added via its own "Attendees" button next to "Line Items", mirroring that existing pattern.

#### API

| Action | Route |
|---|---|
| List a meeting's attendees | `GET /meeting-attendees?meeting_id=X` |
| Add an attendee | `POST /meeting-attendees` |
| Update an attendee | `PUT /meeting-attendees/{id}` |
| Remove an attendee | `DELETE /meeting-attendees/{id}` |
| Mark all unrecorded attendees as attended | `POST /meeting-attendees/mark-all-attended` |

### Reference

- `docs/guides/en/activities-guide.md` — plain-language, user-facing guide.

---

## 19. Notifications

Seven event types notify the relevant user automatically — no per-module setup required, the same way [Audit Trail](#16-audit-trail--impersonation-sessions) captures every record change without opt-in. Delivery is live: a bell icon in the top bar and a bottom-left toast popup both update the instant an event happens, over a private WebSocket channel (`laravel/reverb`), with a slow background poll as a fallback if the connection ever drops.

### Triggers

| Type | Triggered from | Notified |
|---|---|---|
| Record assigned | `AuditObserver` — record created/updated with `owner_id` set to someone else | New owner |
| Activity on a record you own | `AuditObserver` (update/delete) and `RelationshipService::link()` (activity linked) | Record owner, unless they're the actor |
| Meeting invite | `MeetingAttendeeController::store()` | The invited user |
| Task due soon | `NotifyTasksDueSoon` (scheduled hourly) | Task owner |
| User invite accepted | `InviteService::accept()` | Whoever sent the invite |
| User invite expired | `NotifyExpiredInvites` (scheduled hourly) | Whoever sent the invite |
| Account impersonated | `UserController::impersonate()` | The impersonated user |

### Delivery channels

Each type broadcasts over up to three independent channels, controlled by `BaseAppNotification::via()`:

| Channel | Surface | Toggle |
|---|---|---|
| `database` + `broadcast` | Bell dropdown + live toast (bundled as one "in-app" toggle) | `notify_inapp_<type>` |
| `mail` | Email | `notify_email_<type>` |

A live push renders its title/body server-side at broadcast time, in the recipient's own saved language (`HasLocalePreference`) — not the actor's locale or the queue worker's default — so a German-language user always sees a clean, single-language notification regardless of who triggered it or what locale the background job happens to run under.

### Personal and system-wide control

Both toggles exist at two levels, the same "system default with a personal override" pattern used throughout [Settings](#10-settings):

| Level | Location | Scope |
|---|---|---|
| System-wide default | `/settings/system/notifications` (admin) | Organization default for every type/channel pair |
| Personal override | Preferences → Notifications | This user only; unset falls back to the system default |

Both pages render the same 14 email/in-app pairs as a two-column toggle table (`Settings/Notifications.vue`, `Preferences/Index.vue`), reading from and writing to the same 14 `setting_values` rows (`setting_item = notifications`) — an admin sets the organization's defaults, a user can independently override either channel for any type on their own account.

### Reference

- `docs/dev/notifications-implementation.md` — full technical writeup.
- `docs/guides/en/notification-guide.md` — plain-language, user-facing guide.
- **No automated test coverage yet.** An initial test suite was written and then discarded (too many assumptions about the codebase baked in without verifying against real behavior first) — this feature currently has no dedicated tests, only incidental coverage from unrelated suites exercising `AuditObserver`/`RelationshipService` generically.

---

## 20. User Preferences

Every user has a `/preferences` page (`PreferencesController`, `Preferences/Index.vue`) where they can personally override any of the organization's system-wide defaults (see [Settings](#10-settings)) for their own account only — no admin access required, and it never affects what anyone else sees.

### Tabs

| Tab | Fields |
|---|---|
| General | App language, date format, datetime format |
| Style | Primary/secondary/success/danger colors, "use individual module colors" |
| Lists & Panels | Related panel limit, list view limit, linking panel limit |
| Notifications | All 14 email/in-app toggles — see [Notifications](#19-notifications) |

Tabs are reflected in the URL (`?tab=general`), so any tab can be linked to directly and the browser's back/forward buttons move between them — `NotificationBell.vue`'s settings icon, for example, opens straight to `/preferences?tab=notifications`.

### Config-driven, not hardcoded per field

`config/preferences.php` defines every tab and field (type, label, validation rule) once; `PreferencesController::overridableKeys()` derives the full set of valid keys and validation rules straight from that config, so adding a new overridable setting is a config change, not a new controller method. The same config also drives which fields render with which input component (`Checkbox`, `ColorPicker`, `Switcher`, a format-aware `DropdownField`, or a plain number input).

### Override mechanism

A user's overrides are stored as a single JSON `preferences` column on `User`. Saving a field writes an override; explicitly resetting a field back to "System default" removes that key from the JSON entirely rather than storing a duplicate of the default — so a later change to the organization's own default is picked up automatically for anyone who never overrode it. Every field shows the current system default inline (`current_system_value`) so a user can see what they're diverging from before they touch it, and a top-level "Reset" clears all unsaved edits (not saved overrides) back to what's currently on the account.
