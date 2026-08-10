# Module import JSON spec

Input format for `php artisan modules:import <path>` (`app/Console/Commands/ImportModuleFromJson.php`).
Dev-only scaffolding tool — creates/updates a module, its fields, table, model + handler
files, layouts, relationships and dropdown/status lists from one JSON file. Safe to re-run:
everything is upserted (by module slug / field name / dropdown key / layout type /
relationship name), never duplicated.

Give this whole file to an agent as the spec when asking it to generate a module JSON.

## Running it

```
php artisan modules:import path/to/module.json
```

Prompts for confirmation outside `local`/`testing` envs — pass `--force` to skip
(never do this against production).

## Top-level keys

Only `name` and `label` are required. Everything else has a sane default.

| Key | Default | Notes |
|---|---|---|
| `name` | — | required |
| `label` | — | required. Usually a translation key, e.g. `"modules.move_requests.label"` |
| `single_label` | = `label` | singular form, e.g. `"modules.move_requests.single_label"` |
| `slug` | slugified `name` | used to derive table/model/handler names below |
| `icon` | `fa-solid fa-cube` | FontAwesome class |
| `color` | `#0d6efd` | hex |
| `path` | `/{slug}` | sidebar route |
| `sort_order` | next available | |
| `category` | `custom` | sidebar grouping |
| `is_active` | `true` | |
| `has_activity` | `false` | this module can *have* activities (calls/tasks/notes) attached |
| `is_activity` | `false` | this module *is* an activity type itself |
| `show_in_sidebar` | `true` | |
| `show_in_module_manager` | `true` | |
| `description` | `""` | |
| `is_custom` | `true` | see "Custom vs core" below |
| `is_relatable` | `true` | can be the target of a relationship |
| `has_owner` | `true` | adds an `owner_id` user field |
| `is_product_like` | `false` | |
| `has_line_items` | `false` | see "Line items" below |
| `line_item_source_module` | `products` (only used if `has_line_items`) | module that price/unit line items pull from |
| `table_name` | `cstm_{slug}` if custom, else `{slug}` | override only if you need a specific name |
| `model_class` | `App\Models\Modules\Custom\{StudlySlug}` if custom, else `App\Models\Modules\{StudlySlug}` | |
| `handler_class` | `App\Handlers\Modules\Custom\{StudlySlug}ModuleHandler` if custom, else `App\Handlers\Modules\{StudlySlug}ModuleHandler` | |
| `fields` | `{}` | see below |
| `layouts` | `{}` | see below |
| `relationships` | `[]` | see below |

### Custom vs core

- `is_custom: true` (default) — model/handler land under the `Custom` namespace,
  gitignored, matches what the in-app module builder produces. Use this unless told otherwise.
- `is_custom: false` — core-style module, model/handler committed to git, for
  hand-off-ready test modules.

### New module vs pre-existing table

If `table_name` doesn't already exist, every field in `fields` gets a **real column**
on a freshly created table. If the table already exists, new fields are added as
`custom_fields`-JSON-backed instead (no schema change) and fields that already exist
are left untouched beyond their descriptive metadata. For a brand-new module, just
don't reuse an existing `table_name` and every field becomes a real column.

## `fields`

Object keyed by field name (the key doubles as `name` if `"name"` is omitted inside
the definition — just use the field name as the key and skip `"name"`).

```json
"fields": {
  "wunschtermin": {
    "type": "date",
    "filterable": true,
    "sortable": true
  }
}
```

Common per-field keys (all optional except `type`):

| Key | Default | Notes |
|---|---|---|
| `type` | `text` | see type table below |
| `label` | headline-cased field name | plain text, not a translation key — the command generates and registers the translation key itself |
| `required` | `false` | |
| `readonly` | `false` | |
| `hidden` | `false` | |
| `searchable` | `false` | |
| `filterable` | `false` | |
| `sortable` | `false` | |
| `default_value` | `null` | |
| `min_length` / `max_length` | `null` | |
| `regex` | `null` | |
| `related_module` | `null` | **required** for `type: record` — the target module's slug |
| `dropdown_list` | `{module_slug}_{field_name}_list` | only for `select`/`status`, see below |

### Field types

| `type` | Column cast | Notes |
|---|---|---|
| `text` | string | |
| `longtext` | longText | |
| `checkbox` | boolean | **this is the boolean type** — do not use `"boolean"`, it isn't a recognized type and silently falls back to a plain string column with no cast |
| `integer` / `number` | integer | |
| `decimal` | decimal:2 | |
| `currency` | decimal:2 | |
| `percentage` | decimal:2 | |
| `duration` | integer | minutes, cast to integer |
| `date` | date | |
| `datetime` | dateTime | |
| `email` | string | validated as email client-side |
| `phone` | string | |
| `url` | string | |
| `image` | string | stores a path/URL |
| `address` | json | composite field (street/city/zip/etc. bundled), no extra keys needed |
| `multivalue` | json | composite repeatable-value field (e.g. multiple email addresses), no extra keys needed |
| `record` | string | relationship-style lookup to another module's records; **requires** `related_module` set to that module's slug |
| `select` | string | plain dropdown, see below |
| `status` | string | colored/iconized dropdown (status pill), see below |

### `select` / `status` fields — dropdown options

Give the options inline and the command creates the `DropdownList` row for you
(upserted by key, so re-running updates it in place). Two accepted shapes:

**Keyed map** (preferred — matches the module builder's own status editor):

```json
"status": {
  "type": "status",
  "filterable": true,
  "sortable": true,
  "default_value": "neu",
  "options": {
    "neu": {
      "label": "Neu",
      "icon": "fa-solid fa-circle-plus",
      "bg_color": "#3B8BFF",
      "text_color": "#FFFFFF"
    },
    "gepruft": {
      "label": "Geprüft",
      "icon": "fa-solid fa-magnifying-glass",
      "bg_color": "#D9A441",
      "text_color": "#FFFFFF"
    }
  }
}
```

The map key (`neu`, `gepruft`, …) becomes the stored `value`. `icon`/`bg_color`/`text_color`
are optional — a plain `select` field can omit them entirely and just give `label` per option.

**Flat array** (already in the internal storage shape, use if you'd rather write it directly):

```json
"options": [
  { "value": "neu", "label": "Neu", "color": "#FFFFFF", "bgColor": "#3B8BFF", "icon": "fa-solid fa-circle-plus" }
]
```

(`"values"` is also accepted as a synonym for this flat-array form.)

If you deliberately want a field to reuse an **existing shared** dropdown list instead
of inlining options (e.g. a stock list already used elsewhere), omit `options`/`values`
and set `"dropdown_list": "the_existing_key"` — the command looks it up by key and warns
if it can't find it, without creating anything.

## `layouts`

Optional. Map of layout type → definition. Same shape the in-app layout editor saves,
keyed on module + type, so re-running **overwrites** the layout (not idempotent-merge —
full replace). Skip a layout type entirely to leave it on the module builder's default.

| Type | Required key | Purpose |
|---|---|---|
| `list` | `columns` | the module's list/table view |
| `record` | `sections` | the record detail view, grouped into sections |
| `related` | `columns` | related-records panels shown on other modules' record pages |
| `linkingPanel` | `columns` | columns shown when picking a record of this module to link elsewhere |
| `lineItemsSnapshot` | `fields` | only relevant if `has_line_items` — see below |

```json
"layouts": {
  "list": {
    "columns": [
      { "name": "name", "type": "text", "label": "modules.defaults.name", "sortable": true },
      { "name": "status", "type": "status", "label": "modules.move_requests.fields.status", "sortable": true }
    ]
  },
  "record": {
    "sections": [
      {
        "name": "General",
        "layout": [
          { "name": "name", "type": "text", "label": "modules.defaults.name", "required": true },
          { "name": "status", "type": "status", "label": "modules.move_requests.fields.status" }
        ]
      }
    ]
  }
}
```

Each column/layout entry is `{ "name", "type", "label", ...optional "sortable"/"required"/"readonly" }`.
`label` is a translation key — reuse the same `modules.{slug}.fields.{name}` key the
import command registers for that field (or `modules.defaults.{name}` for the built-ins:
`name`, `description`, `owner_id`, `created_at`, `updated_at`, `created_by`, `updated_by`).

`lineItemsSnapshot.fields` is a list of `{ "name": "<line item field>", "source_field": "<field on line_item_source_module, or null>" }`
controlling which line-item fields show and what autofills them from the source module.

Leave `layouts` out entirely for a first draft — the module gets usable defaults —
and only add it once you know which columns/sections you actually want.

## `relationships`

Optional list of relationships between this module and other **already-imported** modules
(so relationships to core modules like `accounts`/`contacts` work immediately; relationships
between two new custom modules need both modules imported before the relationship,
or two `modules:import` runs where the second module's JSON carries the relationship).

```json
"relationships": [
  { "right_module": "accounts", "type": "many-to-one", "label": "Account" },
  { "right_module": "line_items", "type": "one-to-many" }
]
```

| Key | Default | Notes |
|---|---|---|
| `right_module` | — | required, the other module's slug |
| `type` | — | required: `one-to-one`, `one-to-many`, `many-to-one`, `many-to-many` |
| `left_module` | this module's slug | only set to something else if defining a relationship not involving this module |
| `label` | headline-cased `right_module` | |
| `name` | `{left}_{right}` | unique key, used for idempotent re-import |
| `is_system` | `false` | |

`many-to-one` is automatically normalized into `one-to-many` with sides swapped (the DB
only models one-to-many) — write whichever direction reads naturally from this module's side.

## Line items

Setting `"has_line_items": true` is enough — subtotal/discount/tax/total fields and the
line-item picker (against `line_item_source_module`, default `products`) fall back
automatically. Nothing else to generate unless you want to customize `lineItemsSnapshot`.

## Full worked example

```json
{
  "name": "Move Requests",
  "label": "modules.move_requests.label",
  "single_label": "modules.move_requests.single_label",
  "icon": "fa-solid fa-clipboard-list",
  "color": "#9d8f4d",
  "sort_order": 5,
  "category": "sales",
  "has_activity": true,
  "description": "Inbound quote requests before they're accepted and scheduled as a Move.",
  "is_custom": true,
  "fields": {
    "zimmeranzahl": { "type": "integer" },
    "wohnflache": { "type": "decimal" },
    "aufzug_vorhanden": { "type": "checkbox" },
    "abholadresse": { "type": "address", "searchable": true },
    "zieladresse": { "type": "address", "searchable": true },
    "geschatzter_preis_von": { "type": "currency" },
    "wunschtermin": { "type": "date", "filterable": true, "sortable": true },
    "objekttyp": {
      "type": "status",
      "filterable": true,
      "options": {
        "haus": { "label": "Haus", "icon": "fa-solid fa-house", "bg_color": "#4A6B5A", "text_color": "#FFFFFF" },
        "wohnung": { "label": "Wohnung", "icon": "fa-solid fa-building", "bg_color": "#8B7355", "text_color": "#FFFFFF" }
      }
    },
    "status": {
      "type": "status",
      "filterable": true,
      "sortable": true,
      "default_value": "neu",
      "options": {
        "neu": { "label": "Neu", "icon": "fa-solid fa-circle-plus", "bg_color": "#3B8BFF", "text_color": "#FFFFFF" },
        "konvertiert": { "label": "Konvertiert", "icon": "fa-solid fa-circle-check", "bg_color": "#4A6B5A", "text_color": "#FFFFFF" },
        "verloren": { "label": "Verloren", "icon": "fa-solid fa-circle-xmark", "bg_color": "#B04A3A", "text_color": "#FFFFFF" }
      }
    }
  },
  "relationships": [
    { "right_module": "accounts", "type": "many-to-one" }
  ]
}
```

## Checklist for an agent generating this JSON

1. `name`/`label` required, everything else optional — don't over-specify defaults.
2. Boolean fields are `"type": "checkbox"`, never `"boolean"`.
3. `status`/`select` fields: inline `options` as a keyed map; don't hand-write a
   `dropdown_list_id` or pre-create a `DropdownList` — the command does that.
4. `record`-type fields need `related_module`; if the relationship should also be
   navigable/related-panel visible, add a matching entry to `relationships`.
5. Leave `table_name`/`model_class`/`handler_class` unset unless the user names them explicitly.
6. Skip `layouts` unless the user cares about list/record column order — defaults are usable.
7. Referenced modules in `relationships` must already exist (imported earlier, or core).
8. Re-running the same file is safe — it's used to iterate, not just a one-shot.
