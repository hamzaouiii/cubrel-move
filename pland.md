# Umzugsrechner — Implementation Plan

## Context

Cubrel needs a lead-gen surface: a 7-step German-language moving-quote wizard,
embedded on movers' websites, or a standalone webpage to be called from their website, that turns anonymous site visitors
into qualified `moverequests` + `Contact` records in Cubrel, with an instant
price/volume estimate and a PDF quote emailed to the prospect. This document
is the build plan — no code yet. It's grounded in a live audit of Cubrel's
actual API, data model, and module tooling (not assumptions), plus your
answers to four scoping questions asked during planning.

Research findings that shape this plan:

- **`moverequests` and `moves` modules do exist** in Cubrel.
- **`line_items` is unsuitable** for the room/item inventory: it's excluded
  from the REST API entirely (`config('api.excluded_modules')`, confirmed via
  the Postman collection's own negative test) and its schema is
  price/quantity-computation-oriented with no room/category concept. Confirms
  your instinct to flag inventory storage as a genuinely open decision below.
- **Sanctum abilities are `{module}:write`, not create-only** — `write` covers
  both POST and PUT/PATCH. You confirmed accepting this as the pragmatic
  scope: grant `contacts:write` + `moverequests:write`, and the backend
  service simply never issues an update call. No Cubrel core change.

- No backend server required. Cubrel serves as the backend directly.
- **Same repo as Cubrel**, not a separate codebase. See Repo & folder
  structure below.

## Constraints, explained back

- **Same repo, public-endpoint integration, not third-party API-only.** The
  widget (frontend ONLY, Vue) lives in this repo as its own folder, built and
  deployed alongside Cubrel, but it does not touch Cubrel's Laravel app
  directly at runtime and holds no credentials — same isolation a
  third-party consumer would have, just without a second codebase or a
  Sanctum token to manage. At runtime it only calls the new public
  `/api/public/v1/umzugsrechner/*` routes described below.

- **Server-side pricing, always.** The Vue frontend may render a live
  preview number as the user fills the wizard (nice-to-have UX, computed
  client-side from the same formula for responsiveness), but the number that
  gets persisted to `moverequests` is recomputed on cubrel from
  the submitted wizard state. The client-computed preview is never trusted or
  forwarded as-is. (if this means a new endpoint or a new service in cubrel to accommodate, or even just adjust the model of moverequests then it is so )

- **Credential isolation is the load-bearing security property here.**
  The following is what an agent wrote : " Vue app runs inside a public iframe or on third-party sites — its JS bundle
  and network requests are fully visible to anyone who opens devtools on the
  mover's website. That means the Sanctum token can **never** reach the
  browser. Concretely: the Vue frontend talks only to the backend service
  (its own public, unauthenticated-but-rate-limited endpoint); the backend
  service is the only thing that ever holds and sends the Sanctum token to
  Cubrel. This isn't just "thin backend as a proxy for convenience" — it's
  the actual security boundary the whole architecture hangs on. "
  I do not want a backend server but I acknowledge the security risk here. How would not having a small server between the frontend and cubrel be possible here ?

  **Resolved:** no separate backend service. Cubrel itself already is the
  server — the widget talks straight to it. Instead of routing through the
  generic Sanctum-authenticated `{module}:write` REST surface (which requires
  a token the browser can't be trusted with), Cubrel exposes a small set of
  new **public, unauthenticated routes** dedicated to this wizard, e.g.
  `POST /api/public/v1/umzugsrechner/quote`. These live in the existing
  Laravel app and call the `Moverequests`/`Contacts` models in-process — no
  HTTP hop, no bearer token, so there is nothing for devtools to steal.
  Protection comes from rate limiting + a captcha/Turnstile challenge + strict
  server-side validation, not from a credential. The controller backing these
  routes is narrow by construction: it can only create one `moverequests` +
  `Contact` pair per request, never read or update existing records, and
  always recomputes the price/volume server-side, ignoring whatever the
  client submitted as a preview number.

## Wizard steps

7-step flow, German-language, metric units:

1. **Objekttyp** — Haus / Wohnung / Studio
2. **Zimmeranzahl** + **Wohnfläche** (m²)
3. **Access** — Stockwerk / Etagenanzahl + Aufzug vorhanden toggle
4. **Room-by-room item picker** — Wohnzimmer → Schlafzimmer → Küche →
   Badezimmer, with a live truck-fill volume gauge
5. **Besonderheiten** — Langer Tragweg, Zerbrechliche Gegenstände, Demontage
   erforderlich
6. **Contact capture** — Name, E-Mail, Telefon, Wunschtermin
7. **Result** — Preisspanne, Volumen, Fahrzeug, Crew, PDF download

Maps mostly cleanly onto the existing `moverequests` columns (`objekttyp`,
`zimmeranzahl`, `wohnflaeche`, `stockwerk`, `etagenanzahl`,
`aufzug_vorhanden`, `langer_tragweg`, `zerbrechliche_gegenstaende`,
`demontage`, `wunschtermin`, `geschaetztes_volumen_m3`,
`geschaetzter_preis_von/bis`). Gaps still open, to resolve before build:

- **Step 4 inventory** — this is the room/item picker the `line_items`
  research finding already flagged as unusable. Still need a concrete
  storage shape (new table? `custom_fields` JSON on `moverequests`?) before
  the pricing formula can consume it.
- **Step 5 vs. schema** — `moverequests` has both `demontage` and `montage`
  booleans, but step 5 only surfaces "Demontage erforderlich." Confirm
  whether `montage` (reassembly at destination) belongs in the wizard too, or
  is set some other way.
- **Step 7 Fahrzeug / Crew** — not columns on `moverequests` today. These
  read as *derived* from volume/distance rather than stored, but if they
  need to persist (e.g. for the mover to see later, or the PDF regenerated
  offline), the schema needs new fields.

## Repo & folder structure

Same repo, no second codebase:

- `widget/` — the Vue wizard, its own frontend build (own `package.json`,
  bundled/deployed independently of Cubrel's own frontend assets), calling
  only the public `/api/public/v1/umzugsrechner/*` routes.
- Public routes + their controller live in the existing Laravel app
  (`routes/`, a new `App\Http\Controllers\Public\UmzugsrechnerController` or
  similar) alongside the existing `moverequests`/`moves` module code, not
  under the generic Sanctum-authenticated module API.
