# Cubrel — Dynamic Dashboard Implementation Brief

**For:** Claude Code
**Owner:** Simo
**Goal:** Turn the dashboard from a set of hard-coded widgets into a system where users configure their own widgets from a small number of configurable widget _types_. The existing widgets stay — they become pre-seeded presets.

Read this whole brief, then **explore the codebase before writing code**. Confirm the data-layer contract (Phase 1) with me before generalizing. Work phase by phase; keep diffs small and reviewable. Do **not** big-bang the migration — the old and new widget shapes must coexist during the transition.

---

## 1. Context — stack & conventions (match these, don't invent new ones)

- **Backend:** Laravel 12, PHP 8.4, MySQL 8.4. Multi-tenant.
- **Frontend:** Vue 3 + Inertia.js, Tailwind CSS. SFCs with `<script setup>`.
- **Tenancy & access are enforced via Eloquent, not in controllers.** Models carry global scopes for tenant isolation. There is an `OwnershipService` for record ownership and ACL-style module access. **Every query you build must run through the Eloquent model so these scopes apply automatically.** Never query tables directly.
- **Modules** are declared through a `BaseModule` abstraction. Each module exposes its model class and **field metadata** (field name, type, label, etc.). This metadata is your source of truth for what's queryable. Find the registry/resolver and the field-metadata accessor before Phase 1.
- **`RelationshipService`** handles relationships; **`AggregationService`** (new, this project) follows the same naming/structure conventions.
- **`FilterZone`** (list views) already has a filter-format → query translation. **Reuse that exact code path** for widget filters — same format, same translator. Do not write a second filter engine.
- **`FieldRenderer.vue`** already renders dynamic form fields from field metadata. The widget config form is built on top of it — this is wiring, not new components.
- N+1 is handled via static request-level caching; follow the existing pattern if you touch hot paths.

**Housekeeping note:** the existing folder is spelled `@/Pages/Components/Dashbaord/` (typo, "Dashbaord"). Keep the existing spelling for now to avoid breaking imports, or rename in a dedicated commit — do **not** silently half-rename.

---

## 2. The core architectural decision (do not deviate from this)

Today a registry key (e.g. `deals-over-time`) conflates two things: the _kind_ of widget and a _specific configured copy_. Split them.

- **Widget type** — the visualization + a `configSchema` describing what it accepts. Lives in a **type registry**.
- **Widget instance** — a specific configured copy on a user's dashboard. Stored as `{ instanceId, type, cols, config }`.

The nine current widgets collapse into **four types**:

| Type          | Replaces                                      | Config (high level)                                                    |
| ------------- | --------------------------------------------- | ---------------------------------------------------------------------- |
| `metric`      | `stat-leads/won/open/lost`                    | module + aggregate (count/sum/avg) + optional period-over-period delta |
| `time-series` | `deals-over-time`                             | module + dateField + metric + interval + chartType + filters           |
| `breakdown`   | `deal-stages`                                 | module + groupBy field + metric + chartType (donut/bar)                |
| `record-list` | `my-records`, `recent-leads`, `recent-orders` | module + columns + sort + filter + limit                               |

The existing widgets become **default presets** — instances seeded with config. That _is_ "predefined dashboards but customizable": defaults are just config the user can clone, edit, or delete.

**Instance shape (contract):**

```js
{ instanceId: 'uuid', type: 'time-series', cols: 4,
  config: { module:'deals', dateField:'created_at',
            metric:{ type:'count' }, interval:'month',
            chartType:'bar', dateRange:'last_6_months', filters:[] } }
```

**Type registry entry (contract, illustrative — match real conventions when writing):**

```js
'time-series': {
  label: 'Records over time',
  component: TimeSeriesWidget,
  cols: 4,
  configSchema: {
    module:    { type:'module', required:true },
    dateField: { type:'field', filter:'date', dependsOn:'module', required:true },
    metric:    { type:'metric' },                 // count | sum:field | avg:field
    interval:  { type:'enum', options:['day','week','month'], default:'month' },
    chartType: { type:'enum', options:['bar','line'], default:'bar' },
    dateRange: { type:'enum', options:['last_30_days','last_6_months','last_12_months','ytd'], default:'last_6_months' },
    filters:   { type:'filters', dependsOn:'module' },
  },
}
```

---

## 3. SECURITY — the footgun, read twice

A user-driven query endpoint in a multi-tenant app is a data-exfiltration risk. Hold this line:

1. **Resolve the module** through `BaseModule` → get the model class + declared field metadata.
2. **Allowlist every client-supplied identifier against that metadata** before it touches a query: `dateField`, `metric.field`, `groupBy` field, every filter field, every `columns` entry. Reject anything not in the module's declared fields with a 422.
3. **Aggregate functions are an enum** (`count`, `sum`, `avg`) — never a passthrough string.
4. **Build the query through the Eloquent model**, so tenant global scopes + `OwnershipService` rules apply automatically. Never touch raw tables or interpolate identifiers into SQL.
5. **Module access is ACL-checked** — a user can only target modules they're allowed to see. The field/module pickers on the frontend must be filtered the same way (don't rely on the frontend for enforcement — enforce server-side, mirror on the client for UX).
6. Filters go through the **existing FilterZone translator**, not bespoke SQL.

If any input fails validation, fail loud (422) — never silently fall back to an unscoped query.

---

## 4. Data layer — `AggregationService` + endpoint

`getProps(props)` goes away. Each widget instance fetches its own data. The big page payload shrinks (stop shipping `dealsOverTime`, `recentOrders`, etc. to every user).

**Endpoint:** `POST /dashboard/widget-data` → `{ type, config }`.

**`AggregationService`** branches on type and returns a shape-per-type:

- `time-series` / `breakdown`: `{ labels: string[], series: [{ name?, data: number[] }] }`
  - Time bucketing: MySQL `DATE_FORMAT` per interval (`%Y-%m-%d` / week / `%Y-%m`), or Carbon periods. Use **org-level timezone** for bucket boundaries (you're already org-level on currency — keep date logic consistent). Fill empty buckets with 0 so charts don't skip months.
  - `dateRange` is **relative** (resolved server-side from "now") — dashboards must not show stale fixed windows.
- `metric`: `{ value: number, previous?: number, change?: number, changeType?: 'up'|'down' }`
  - **The current stat widgets' "12.5% up" values are hard-coded fakes.** Either compute a real period-over-period delta (current window vs. previous equal window) or drop the delta for v1. Don't ship fake numbers.
- `record-list`: **reuse the existing list/index data path** with a `limit` — this overlaps heavily with list views, so it's reuse, not new aggregation. Return `{ rows, columns }`.

Each widget component owns its own loading / empty / error state.

---

## 5. Persistence

> **DECISION POINT (Simo to confirm before handoff):** Default below is the JSON column for speed to July 15. The `dashboard_widgets` table is the documented upgrade — it's queryable and matches the private/shared/admin-override pattern already used for saved filters, which seeds admin-defined default dashboards cleanly. Flip this section if you'd rather do the table now.

**v1 (default): JSON column.** A `dashboards` row per user with a `widgets` JSON column holding the instance array (Section 2 shape). The existing `POST /dashboard/layout` becomes `POST /dashboard` saving instances instead of string IDs.

**Upgrade (later): `dashboard_widgets` table** — `id, user_id, tenant_id, type, config (json), cols, sort_order, scope (private|shared), …`. Enables admin-defined defaults that seed new users, reusing the saved-filters scoping pattern.

---

## 6. Frontend

- **Config form auto-generates from `configSchema`**, rendered via the existing `FieldRenderer`. Field-type → control mapping: `module` → module picker (ACL-filtered), `field` → field picker filtered by `filter:` (e.g. only date fields for `dateField`, numeric for sum/avg targets), `enum` → select, `metric` → count/sum/avg + target field, `filters` → embed FilterZone.
- **Dependent dropdowns:** pick module → fetch that module's field metadata → populate dependent pickers (`dependsOn: 'module'`). One metadata fetch, reused across all pickers in the form.
- **Add-widget flow:** pick type → configure → instantiate (push instance, persist).
- **Edit flow:** each `w-cell` gets a gear button that reopens the config form pre-filled from the instance's `config`.
- Keep the existing grid/`cols` span layout; `cols` now lives on the instance.

---

## 7. Phased plan (ship in this order)

**Phase 0 — Orientation (no code).** Locate and report back: `BaseModule` registry + field-metadata accessor, `OwnershipService` module-access check, FilterZone filter→query translator, the current dashboard controller + `/dashboard/layout` route, `FieldRenderer` API. Confirm the Section 4 endpoint contract with me.

**Phase 1 — Prove it end-to-end with `time-series` only.**

- Build `AggregationService::timeSeries(config)` with full Section 3 validation.
- Build `POST /dashboard/widget-data`.
- Build `TimeSeriesWidget.vue` (self-fetching) + the auto-generated config form.
- Add `time-series` as a **new type alongside the existing hard-coded registry entries** (both shapes coexist).
- Acceptance: a user can add a time-series widget, pick any allowed module + date field + metric + interval, see a correct chart; an out-of-allowlist field returns 422; the chart respects tenant + ownership scopes.

**Phase 2 — Generalize.** Add `metric` and `breakdown` to `AggregationService` (same query builder, different output shape). Migrate the 4 stat widgets + `deal-stages` to presets. Decide real delta vs. no delta for metric.

**Phase 3 — `record-list`.** Reuse list-view data path with limit/sort/columns. Migrate `my-records`, `recent-leads`, `recent-orders` to presets.

**Phase 4 — Switch persistence** to the instance-object array (Section 5) once ≥1 configurable type is live. Seed the current default dashboard as preset instances so existing users see no change.

---

## 8. Non-goals / out of scope (v1)

- Drag-to-reorder / resize beyond the existing `cols` span (instances store `cols`; reordering can come later).
- Multiple dashboards per user (one per user for v1).
- Cross-module joins in a single widget.
- Currency conversion (org-level single currency stands).
- Absolute custom date ranges (relative ranges only for v1).

---

## 9. Definition of done (per phase)

- All client identifiers validated against module metadata; bad input → 422, never an unscoped query.
- Every aggregation runs through the Eloquent model; tenant + ownership scopes verified with a second-user/second-tenant test.
- Old widgets render identically as presets after each migration step (no visual regression for existing users).
- Page payload no longer ships per-widget datasets the user didn't request.
- Diffs reviewable; old and new shapes coexist until Phase 4.
