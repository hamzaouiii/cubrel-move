# Cubrel CRM — Codebase Analysis

---

## 1. TECH STACK

**Backend:**
- Laravel 12.0 / PHP 8.2+
- SQLite (default; configurable to MySQL/PostgreSQL)
- Session, cache, queue all on database driver by default

**Key Composer packages:**
- `barryvdh/laravel-dompdf` 3.1 — PDF generation
- `laravel/scout` 11.2 — full-text search
- `inertiajs/inertia-laravel` 2.0 — Inertia SSR adapter
- Dev: `larastan`, `laravel/pail`, `laravel/pint`, `phpunit`, `mockery`

**Frontend:**
- Vue 3.5.22 + Inertia.js 2.2.8 (vue3 adapter)
- Vite 7.0.7 / Tailwind CSS 3.2.1 / Sass 1.93.2
- `chart.js` 4.5.1 + `vue-chartjs` 5.3.3
- `dayjs` 1.11.19, `libphonenumber-js` 1.12.40 (DE locale default)
- `@fortawesome/fontawesome-free` 7.2.0 + `@mdi/font` 7.4.47
- `axios` 1.11.0

---

## 2. ARCHITECTURE OVERVIEW

**Module system:**
- 17 built-in static modules (Leads, Accounts, Contacts, Deals, Orders, Invoices, Quotes, Products, Support Cases, Inquiries, Users, PDFs, etc.)
- `config/modules.php` holds seed-time definitions; a `Module` model stores runtime metadata
- Every module has a **Handler** class (`BaseModuleHandler` subclass) responsible for query building, `getListData()` (pagination/sorting/columns), `getRecordData()` (fields/relationships), and eager-load specs

**Base model composition via traits:**
- `HasCustomFields` — merges stock + custom fields via JSON column
- `HasDynamicRelationships` — registers polymorphic relationships at runtime
- `HasUuids` — string UUID primary keys across all business models
- `Searchable` — Scout integration
- `HasTranslatableLabel`, `HasFactory`

**Controllers (26 total):**
- `RecordController` (341 lines) — single-record CRUD + bulk ops
- `ListController` — module list via handler
- `LayoutManagerController`, `FieldsManagerController` — settings CRUD
- `ModuleBuilderController` + `ModuleDeploymentController` — custom module scaffolding (6-step)
- `PdfTemplatesController` + `PdfController` — template CRUD + rendering
- `RelationshipManagerController` + `RelationshipLinkController` — relationship CRUD + linking UI
- `UserController`, `InviteController`, `DashboardController`, `SearchController`, `LineItemController`

**Services (5):**
- `ModuleScaffolder` — generates PHP handler/model stubs for custom modules
- `RelationshipService` — eager-loads and resolves relationship data
- `TranslationService` — i18n key syncing
- `InviteService` — invitation token lifecycle
- `OwnershipService` — scopes queries to user-owned records

**Middleware (4):**
- `AdminMiddleware` — protects `/settings`, `/users`, `/invites`
- `HandleInertiaRequests` — shares module list, user, permissions as Inertia props
- `LocalAutoLogin` — bypasses auth in local env
- `SetLocaleFromSettings` — sets app locale from user preference

**Routes:** `/{module}` (list), `/{module}/{id}` (show), standard CRUD verbs, `/settings/*` (admin-only), `/search`, `/line-items`, `/{module}/{id}/pdf`

---

## 3. KEY FEATURES (Product Perspective)

**CRM Core:**
- Record management for 10 business module types (Leads, Accounts, Contacts, Deals, Orders, Invoices, Quotes, Products, Support Cases, Inquiries)
- Stock fields (first_name, email, phone, etc.) + unlimited custom fields per module
- Owner tracking on most modules
- Polymorphic relationships between any two modules (one-to-many, many-to-many, one-to-one)
- List view: sortable columns, filters, bulk selection (explicit, all matching, all except)
- Record detail view with related-record subpanels

**Module Builder:**
- Admin UI to create entirely new business modules without writing code
- 6-step deployment wizard: initialize → generate files → create labels → activate fields → create table → rollback
- Auto-scaffolds handler class, model class, and migration

**Field System:**
- 16 field types: text, longtext, email, phone, url, select, status, checkbox, date, datetime, integer, decimal, percentage, currency, address, record
- Composite address fields (street, city, postal, country)
- Reusable named dropdown option lists
- Per-field validation rules (email regex, phone via libphonenumber-js, integer range, etc.)
- Per-field flags: visible, readonly, required, sortable (per layout)
- Custom fields in JSON columns; stock fields as dedicated columns

**PDF Templates:**
- Section-based definition: header, footer, fields, text blocks, divider, relationship tables, line-items table
- Section kinds: logo, meta, title, field, page_number, date
- Field display styles: title, subtitle, bold, small, label, status, address, highlight, muted
- Browser preview with live re-render; DomPDF engine (barryvdh/laravel-dompdf)
- Company logo as Base64 data URI (cached); Fira Sans + Heebo fonts registered

**Layouts:**
- 5 layout types per module: list (columns), record (sections/fields), related (subpanel columns), linkingPanel (selector overlay), pdf
- Drag-and-drop editor; per-field options (readonly, required, sortable, hidden)

**Line Items:**
- Polymorphic children for Orders, Invoices, Quotes
- Per-item: subtotal, discount, tax, total; reorderable (sort_order); optional Product reference

**Dashboard:**
- Widgets: recent leads, record counts, owned records, recent orders, deals over time, deal stages, invoice overview
- Chart.js + vue-chartjs

**Settings:**
- System: timezone, date/datetime format, list limit
- Customization: primary/secondary/danger/success colors, theme (light/dark/system), border-radius, table striping
- Users: multi-currency mode, enabled languages, language switcher
- Email: outbound/inbound mail config; Company: org identity

**Users & Invitations:**
- Roles: root (superuser), admin (settings access), regular user
- Module-level capability flags: can_view, can_create, can_edit, can_delete
- ⚠ RBAC not implemented — all non-admin users share same permissions
- Admin impersonation with persistent banner
- Token-based invite system: send, revoke, resend, accept
- Brute-force tracking columns (failed_login_attempts, locked_until, last_login_ip) exist but ⚠ not enforced

**Search:**
- Global search via Scout `Searchable` trait; ⚠ no Scout driver configured (LIKE fallback likely active)
- Related field typeahead: `GET /relatedfield/search/{module}`

---

## 4. DATABASE

**Migration count:** 36 migrations (~1,292 lines total)

**Business module tables (12):** leads, accounts, contacts, deals, invoices, quotes, orders, products, cases, contact_messages, line_items, user_invites — all with `custom_fields` JSON column

**Infrastructure tables:** modules, fields, layouts, dropdown_lists, relationships, relationship_links, labels, icons, dashboards, pdf_templates, settings, setting_values, impersonation_sessions, ip_whitelists (unused)

**Core Laravel tables:** users, password_reset_tokens, sessions, jobs, job_batches, failed_jobs, cache, cache_locks

**Schema patterns:**
- **UUID primary keys** across all business models (string, not auto-increment)
- **Custom fields JSON column** on every module table for runtime extensibility
- **Polymorphic relationship_links** using `source_module_slug + source_record_id + target_module_slug + target_record_id` (not Laravel's standard morph columns)
- **JSON-driven layouts** — layout and PDF template definitions stored as JSON blobs in `layouts.definition` / `pdf_templates.definition`
- **Key-value settings** — `setting_values` table with group and autoload flags

---

## 5. FRONTEND

**Component count:** 109 Vue components

**Directory structure:**
```
resources/js/
├── app.js                    Inertia setup + field registry
├── Layouts/                  AppLayout, GuestLayout
├── Pages/
│   ├── Dashboard/
│   ├── Modules/              List, Record, Create + Relatedpanels/
│   ├── Users/, Profile/, Invites/
│   └── Settings/
│       ├── Modules/, Fields/, Layouts/, Dropdowns/
│       ├── PdfTemplates/, Relationships/, Builder/
│       └── Components/
│           ├── FieldTypes/   20 field type components
│           ├── Globals/      Topbar, Sidebar, Pagination, ConfirmOverlay…
│           ├── Modules/      ListActions, LineItemsPanel, PdfModal…
│           ├── Settings/     Layout editors, field editors
│           └── Dashboard/    Widget components
├── Composables/              9 composables
├── Registries/               fieldRegistry.js
└── utils/                    fieldValidation, datetime, countries, osDetections
```

**Key composables (9):**
- `useAlerts` — toast/notification system
- `useConfirm` — modal confirmation
- `useFieldValidation` + `useFieldRules` — client-side validation
- `useTrans` — i18n
- `useLayoutDragDrop` — drag-and-drop for layout editors
- `useUnsavedChangesGuard` — warns on dirty form navigation
- `useDebounce`, `useClipboard`

**Notable patterns:**
- `fieldRegistry.js` maps each of the 16 field types → Vue component + validation rules
- `FieldRenderer.vue` uses registry to dynamically resolve the correct component
- Shared Inertia props (modules, user, permissions, settings) via middleware
- No external component library (Vuetify/Element UI not in active use); fully custom Tailwind components
- FontAwesome + MDI icons via CSS fonts

---

## 6. DEVOPS / DEPLOYMENT

- **Docker:** None
- **CI/CD:** `.github/workflows/deploy_yml_disabled` exists but is disabled; no active pipelines for tests, linting, or deployment
- **Hosting config:** None (no Forge, Vapor, or server config files)
- **Local dev:** `composer dev` runs Laravel serve + queue listener + Vite dev concurrently; `laravel/sail` available optionally
- **Default .env:** SQLite, database sessions/cache/queue, `MAIL_MAILER=log`, no observability (Sentry/Datadog) vars

---

## 7. SCALE INDICATORS

| Metric | Count |
|---|---|
| PHP files total | 133 |
| Vue components | 109 |
| JS utility/composable files | 16 |
| Migrations | 36 |
| Controllers | 26 |
| Business models (built-in) | 12 |
| Custom (admin-created) modules | 12 (test/demo) |
| Field types supported | 16 |
| Services | 5 |
| Composables | 9 |
| PHP lines of code (app/) | ~8,980 |
| Frontend lines (JS + Vue) | ~30,252 |
| Migration lines | ~1,292 |
| **Total LOC** | **~40,500** |

**Complexity signal:** Medium-high. The frontend (~30k lines, 109 components) significantly outweighs the backend (~9k lines). The handler pattern, dynamic field system, module builder, and polymorphic relationship engine add meaningful architectural depth for a project of this size.
