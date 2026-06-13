# Cubrel CRM — Feature Inventory

> Updated 2026-06-12 from routes, controllers, models, config, migrations, and Vue components.
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
| **Orders** | `orders` | order_number, total_amount, status, order_date, due_date | Yes | Yes |
| **Invoices** | `invoices` | number, status, issue_date, due_date, currency, subtotal, tax, total | Yes | Yes |
| **Quotes** | `quotes` | number, status, valid_until, currency, subtotal, tax, total | Yes | Yes |
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
| `currency` | `decimal` | `CurrencyField.vue` ⚠ | non-negative number |
| `address` | `json` | `AddressField.vue` ⚠ | all sub-fields non-empty |
| `record` | `string` | `RelatedRecord.vue` | UUID format |
| `number` | `integer` | (same as integer) | — |

**⚠ INCOMPLETE — `AddressField.vue` and `CurrencyField.vue`** are currently untracked new files on branch `feat/82-field-types`. They are wired into the registry and validation utilities but have not been committed. Consider them in-progress.

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
- Feature flags: `has_line_items`, `has_owner`

**Categories:** `sales` (Leads, Accounts, Contacts, Deals) · `revenue` (Quotes, Orders, Invoices, Products, LineItems) · `support` (Cases) · `communication` (Inquiries) · `system` (Users, User Invites, Settings)

### Module Builder — Custom Modules

Admins can build entirely new modules without writing code. The flow has dedicated routes and a multi-step UI:

1. **Create** — `ModuleBuilderController@create` / `Settings/Modules/Create.vue` — define name, slug, icon, category, color
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

**⚠ INCOMPLETE** — The file-generation step (`generate-files`) creates PHP stubs on disk. How well the generated handler integrates with the handler pattern (filters, search, custom computed fields) is not fully validated; the deploy flow has no automated tests visible in the codebase.

### Handler Pattern

Every module has a handler class (extends `BaseModuleHandler`) responsible for:

- `query()` — base Eloquent query with scope/filter application
- `getListData()` — data for list views (pagination, sorting, column selection)
- `getRecordData()` — data for single-record views (field values, related panels)
- Declaring searchable columns and relationship eager-loads

---

## 4. Layouts

### Layout Types

Each module can have up to five layout types stored in the `layouts` table as JSON blobs. Default layouts are defined in `config/module_layouts/{module}.php`.

| Type | Purpose | UI editor |
|---|---|---|
| `list` | Columns shown in the module list table | `LayoutListEditor.vue` |
| `record` | Sections and fields shown on a single record page | `LayoutRecordEditor.vue` |
| `related` | Columns shown in related-record subpanels | `LayoutRelatedEditor.vue` |
| `linkingPanel` | Columns shown in the record-selector overlay | `LayoutLinkingPanelEditor.vue` |
| `pdf` | Fields rendered into PDF output | `LayoutPdfEditor.vue` |

### Layout Editor

Routes under `/settings/modules/{module}/layouts` let admins view and update layouts.

- `LayoutManagerController@show` — lists layout types for a module
- `LayoutManagerController@edit` — loads current layout JSON
- `LayoutManagerController@store` — persists updated layout JSON

The Vue editors (`Settings/Layouts/Edit.vue`, `Settings/Layouts/Record.vue`) provide a drag-and-drop interface for reordering fields and toggling per-field options (`readonly`, `required`, `sortable`, `hidden`).

Layout config per module specifies fields by name and includes display metadata (label translation key, readonly flag, required flag, sortable flag).

**⚠ INCOMPLETE** — `LayoutSubpanelEditor.vue` exists as a component but no subpanel layout route is defined. `LayoutRelatedFields.vue` (field selector for related panels) exists but its integration path is unclear.

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

The `RelatedLinksOverlay.vue` and `RecordSelectorDrawer.vue` components handle the UI for selecting and linking records.

### Relationship Manager (Settings)

Admins can view, create, and delete relationship definitions at `/settings/modules/{module}/relationships`. UI: `Settings/Relationships/List.vue` and `Settings/Relationships/Create.vue`.

**⚠ INCOMPLETE** — The resource route exists for `update` but no edit UI was found for modifying an existing relationship definition after creation.

---

## 7. Line Items

Orders, Invoices, and Quotes support child line item rows. A line item is a polymorphic child (`parent_type` / `parent_id`) referencing an optional `Product`.

### Per-item calculations (server-side, `LineItem::calculateTotals()`)

```
subtotal        = unit_price × quantity
discount_amount = subtotal × (discount / 100)
tax_amount      = (subtotal − discount_amount) × (tax_rate / 100)
total           = subtotal − discount_amount + tax_amount
```

### API

| Action | Route |
|---|---|
| List | `GET /line-items?parent_type=X&parent_id=Y` |
| Create | `POST /line-items` |
| Update | `PUT /line-items/{lineItem}` |
| Delete | `DELETE /line-items/{lineItem}` |
| Reorder | `POST /line-items/reorder` (updates `sort_order`) |

The record page shows line items when `module.has_line_items` is true. Line item totals roll up to the parent record's `total_amount` field.

**⚠ INCOMPLETE** — The roll-up from line item totals to parent `total_amount` is not automatically triggered server-side on every save; the current implementation in `LineItemController@store/update` calls `calculateTotals()` on the item but there is no observer or event that recomputes the parent's aggregate total.

---

## 8. Search

### Global Search

Route `GET /search` (name: `search`) handled by `SearchController`. The `Searchable` trait on `BaseModule` provides `toSearchableArray()` and `searchableFields()` for each module. The `GlobalSearch.vue` overlay and `SearchOverlay.vue` component manage the front-end.

### Related Field Search

Route `GET /relatedfield/search/{related_module}` (name: `records.search`) handled by `RelatedFieldController`. This is the typeahead endpoint used by the `RelatedRecord.vue` field component when the user types to find a record to link.

### List Filtering & Sorting

List views support column-level filtering and sorting. The handler class controls which fields are `filterable` and `sortable`. Filter state is preserved in the URL so links are shareable.

**⚠ INCOMPLETE** — Global search relies on a `Searchable` Scout-style trait, but no Scout driver configuration (Meilisearch, Algolia, database) is apparent from seeder or config files. It may fall back to a basic Eloquent `LIKE` query, which is not suited for large datasets.

---

## 9. Dashboard

Route `GET /` → `DashboardController@index`. The dashboard assembles several data sets for the authenticated user:

| Widget | Data source | Vue component |
|---|---|---|
| Recent leads | Last N leads where `owner_id = user` | `NewRecords.vue` |
| Record counts | COUNT per module scoped to user | (inline in `Index.vue`) |
| Owned records | Cross-module records owned by user | `MyRecords.vue` |
| Recent orders | Last 5 orders | `RecentOrders.vue` |
| Deals over time | Monthly deal amounts, 12-month window | `DealsOverTime.vue` (line chart) |
| Deal stages | Won / lost / open deal counts | `DealStages.vue` (doughnut chart) |
| Invoice overview | Invoice status breakdown | (inline in `Index.vue`) |

Per-user dashboard widget configuration is stored in the `dashboards` table (JSON `configuration` column) introduced in migration `2026_05_04`.

**⚠ INCOMPLETE** — The `dashboards` table and model exist, but the dashboard page appears to render a fixed set of widgets rather than reading the user's saved configuration. Widget drag-and-drop or personalisation UI was not found.

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

**⚠ INCOMPLETE** — The capability flags (`can_view`, `can_create`, etc.) are stored per module globally, not per user or per role. There is no role-based access control (RBAC) system: all non-admin users share the same permissions. `ModulePolicy` exists but its enforcement coverage across all controller methods has not been audited here.

### Impersonation

Admins can impersonate any user via `POST /users/{user}/impersonate`. A persistent banner (`ImpersonationBanner.vue`) is shown while impersonating. `POST /leaveimpersonate` returns to the original admin session.

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

### User Security Fields

The `users` table includes `two_factor_secret`, `failed_login_attempts`, `locked_until`, `last_login_at`, `last_login_ip`, `password_changed_at`. These columns are present but **⚠ INCOMPLETE** — two-factor authentication UI and brute-force lockout logic were not found in controllers or middleware.

### IP Whitelist

An `ip_whitelists` table exists in migrations. No middleware or controller referencing it was found — **⚠ INCOMPLETE / UNUSED**.

### Locale & Timezone

`SetLocaleFromSettings` middleware reads user preferences on each request and sets the application locale accordingly.

### Local Auto-Login

`LocalAutoLogin` middleware bypasses authentication in local environments. Should be disabled for staging/production.

---

## Summary of Incomplete / Half-Built Areas

| Area | Issue |
|---|---|
| `AddressField.vue`, `CurrencyField.vue` | New field type components committed on `feat/82-field-types` — now merged; fully wired in registry and PDF renderer |
| Line item parent total roll-up | No server-side observer to recompute parent `total_amount` when line items change |
| Global search driver | `Searchable` trait present; no Scout driver configured — likely falling back to naive LIKE queries |
| Dashboard personalisation | `dashboards` table exists for per-user config but no UI to customise widget layout |
| Module permissions (RBAC) | `can_view/create/edit/delete` flags are global, not per-user/role |
| `ModulePolicy` coverage | Policy class exists but not verified across all controller actions |
| Relationship edit UI | No route or component to edit an existing relationship definition |
| Layout subpanel editor | `LayoutSubpanelEditor.vue` exists but no route is wired to it |
| Custom module deployment | Generated handler quality and deploy rollback reliability are untested |
| Two-factor authentication | DB columns present, no UI or enforcement middleware found |
| IP whitelist | Table exists, no middleware reads it |
