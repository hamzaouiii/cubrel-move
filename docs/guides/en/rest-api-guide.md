# REST API

Cubrel exposes a REST API so an external system — a partner integration, a
script, a data pipeline — can read and write your records without a human
clicking through the app. This guide covers how to get a token, what the API
looks like, and the rules it enforces.

## Getting an API token

API access is token-based, not username/password. An admin creates tokens
from **Settings → API Tokens → New Token**:

1. Give the token a name (for your own reference — "Zapier integration",
   "Nightly export script").
2. Pick the user the token acts as. Every request made with this token is
   attributed to that user (for example, records it creates will show that
   user as the owner).
3. Either grant **Full Access** (every module, every action), or leave it
   unchecked and pick individual **Read** / **Write** / **Delete**
   permissions per module. Some modules only offer Read — see
   [Modules the API can't touch](#modules-the-api-cant-touch) below.
4. Save. The plaintext token is shown **exactly once**, immediately after
   creation. Copy it somewhere safe — Cubrel only ever stores a hash of it,
   so if you lose it, you'll need to revoke it and create a new one.

Revoke a token any time from the same **API Tokens** list. A revoked token
stops working immediately.

## Authenticating requests

Send the token as a bearer token on every request:

```
Authorization: Bearer <your-token>
```

There's no session, no cookies, no CSRF token to worry about — this header
is the entire authentication mechanism. A missing or invalid token always
gets a clean `401` JSON response — no `Accept` header needed.

## Base URL and endpoints

Every endpoint is namespaced under `/api/v1/{module}`, where `{module}` is a
module's slug (`leads`, `contacts`, `deals`, `accounts`, and so on — the same
slug you see in the module's URL inside the app).

| Method | Path | Action |
| --- | --- | --- |
| `GET` | `/api/v1/{module}` | List records |
| `GET` | `/api/v1/{module}/{id}` | Get one record |
| `POST` | `/api/v1/{module}` | Create a record |
| `PUT` / `PATCH` | `/api/v1/{module}/{id}` | Update a record |
| `DELETE` | `/api/v1/{module}/{id}` | Delete a record |

### Listing records

```
GET /api/v1/leads?per_page=25&sort=name&direction=asc&search=acme
```

| Parameter | Meaning |
| --- | --- |
| `per_page` | Records per page. Defaults to your workspace's list-view page size (usually 25). |
| `search` | Matches against the module's searchable fields. |
| `sort` | A field name to sort by. **Only works for the module's regular writable fields** — you can't sort by `id` or `created_at`, for instance. An unrecognized value is silently ignored rather than erroring, and the list falls back to unspecified order (not the newest-first default) when that happens. |
| `direction` | `asc` or `desc`. Ignored if `sort` isn't also set. |
| `filter` | The **slug or ID of a saved list filter** you already created in the app for that module (Deals → your saved "Open This Quarter" filter, for example) — not a raw filter expression. |

A list response looks like:

```json
{
  "data": [
    { "id": "...", "name": "Acme Corp", "email": "hello@acme.com", "...": "..." }
  ],
  "meta": {
    "total": 128,
    "per_page": 25,
    "current_page": 1,
    "last_page": 6
  },
  "links": {
    "next": "https://yourapp.com/api/v1/leads?page=2",
    "prev": null
  }
}
```

### Getting, creating, updating, deleting

`GET`/`POST`/`PUT`/`PATCH` responses wrap a single record in `{ "data": {...} }`.
`DELETE` returns an empty `204 No Content` body on success.

`PUT`/`PATCH` are **partial patches** — send only the fields you want to
change. Fields you omit keep their existing value.

```
POST /api/v1/leads
Content-Type: application/json

{ "name": "Jane Doe", "email": "jane@example.com" }
```

A few fields are never accepted from you, even if you send them — they're
always set by Cubrel itself: **`owner_id`** (defaults to the token's user),
**`created_by`**/**`updated_by`**, **`created_at`**/**`updated_at`**, and any
field marked read-only or calculated in the module's field setup. Sending
them isn't an error — they're just ignored.

### Custom fields

Any custom field a module has is just another key in the same flat JSON
object — both when you send it and when you read it back. There's no
separate `custom_fields` wrapper to know about:

```json
{ "name": "Jane Doe", "referral_source": "conference" }
```

`referral_source` here is a custom field defined on the Leads module in
**Settings → Modules**; from the API's point of view it behaves exactly like
a built-in field.

### Related and child records

Getting, creating, or updating a single record (never a list) includes
extra keys beyond that record's own fields, so you rarely need a second
request to get the full picture.

**`related`** — present on every module, always, keyed by relationship
name. Each value is an array of the records linked through that
relationship (an empty array if nothing's linked, or if the module has no
relationships at all):

```json
{
  "id": "...",
  "name": "Acme Corp",
  "related": {
    "leads_tasks": [
      { "id": "...", "name": "Follow up call", "status": "not_started" }
    ],
    "leads_calls": [],
    "leads_meetings": []
  }
}
```

Relationship names aren't published as a fixed list — reading them off a
record's `related` object *is* how you discover them. Each relationship is
capped to a limited number of records (the same cap the app's own
related-records panel uses) and isn't paginated within this response.

**`line_items`** — Quotes, Orders, and Invoices only:

```json
{
  "id": "...",
  "total": "45.00",
  "line_items": [
    { "id": "...", "name": "Widget", "quantity": "2.0000", "unit_price": "10.0000", "total": "20.0000" }
  ]
}
```

**`attendees`** — Meetings only, ordered organizer first, then required,
then optional, then alphabetically by name:

```json
{
  "id": "...",
  "name": "Budget planning meeting",
  "attendees": [
    { "id": "...", "name": "Alice", "role": "organizer", "rsvp_status": "accepted" }
  ]
}
```

None of this appears when listing records (`GET /api/v1/{module}` without
an `{id}`) — only on a single record.

## Permissions

Every token has a specific set of `module:action` grants (or full access),
chosen when it was created. If a request needs a grant the token doesn't
have, you get a `403`. This applies per-module and per-action — a token with
`leads:read` but not `leads:write` can list and fetch leads but not create,
update, or delete them.

### Modules the API can't touch

- Some modules are excluded entirely and never appear via the API — a
  request to one 404s regardless of what the token can do.
- Some modules are **read-only through the API** — write/delete requests to
  them always fail with `403`, even for a full-access token. These are
  system catalogs a partner integration has no legitimate reason to modify
  through this API.

Both lists are workspace configuration, not something a token grant can
override — check with your admin if a module you need access to isn't
available.

## Rate limits

Requests are limited to **60 per minute per token** (unauthenticated
requests are limited per IP address instead). Every response includes
`X-RateLimit-Limit` and `X-RateLimit-Remaining` headers so you can see how
much headroom you have left. Going over the limit returns a `429`.

## Language

Send an `Accept-Language` header to get error and validation messages back
in your preferred language:

```
Accept-Language: de
```

Currently supported: **English** (`en`) and **German** (`de`). Region
subtags are fine (`de-DE`, `en-US`) — only the base language is used. If the
header is missing, or names a language Cubrel doesn't have yet, responses
fall back to the workspace's default language. This affects every error
message below, and per-field validation messages, but has no effect on your
actual record data (a Lead's `name` value is whatever you stored — Cubrel
doesn't translate your data).

## Errors

Every error response is JSON, shaped as:

```json
{ "message": "Human-readable summary" }
```

Validation failures (`422`) additionally include a per-field breakdown:

```json
{
  "message": "The name field is required.",
  "errors": {
    "name": ["The name field is required."]
  }
}
```

With `Accept-Language: de`, the same failure comes back as:

```json
{
  "message": "Das Feld Name ist erforderlich.",
  "errors": {
    "name": ["Das Feld Name ist erforderlich."]
  }
}
```

| Status | Meaning |
| --- | --- |
| `401` | Missing or invalid token. |
| `403` | The token doesn't have the required permission for this module/action, or the module is read-only/excluded via the API. |
| `404` | The module or record doesn't exist (or the module isn't reachable via the API). |
| `422` | The request body failed validation. |
| `429` | You've hit the rate limit. |

## What comes back in a response

Responses never include anything password/token/secret-shaped, regardless
of the module. A small number of specific fields (for example, a user's
saved preferences) are also always stripped from that module's response,
independent of what your token can otherwise see.
