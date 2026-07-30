# Email Capture: Implementation Notes

Companion to `docs/guides/en/emails-guide.md`, which covers the concept and workflow. This document covers the concrete mechanics: schema, the two capture-address types, the self-hosted inbound relay, message parsing, and how this integrates with the module system and Conversion Rules.

## 1. Provider history (why self-hosted, not a third-party API)

Worth knowing before reading the rest: this feature went through three inbound-email providers before landing on a self-hosted relay, and the code still carries traces of that (field names, comments referencing "the provider"). In order: Mailtrap (hit a hard plan cap — inbound-only domains count against the same sending-domain limit as outbound, and the cheapest tiers above Free only allow 1–5 domains total, which doesn't scale per-tenant), CloudMailin (worked, used briefly to validate the pipeline end-to-end during development), then a wider provider comparison (Postmark, Mailgun, Lettermint, others) narrowed by a hard EU-data-residency requirement — Postmark was ruled out entirely on this basis (no EU hosting, no plans to add it). Self-hosted Postfix won on cost (zero marginal cost, no per-domain pricing), data sovereignty (mail never leaves infrastructure we control), and fit (the tenant/user resolution logic already lived in the app layer, so Postfix only ever needed to be a dumb catch-all relay, not a smart per-tenant router).

`provider_message_id` (§2) and the generic "relay" naming throughout are leftovers of this history — there is no actual third-party provider anymore, but renaming a stable column/concept for cosmetic reasons wasn't worth the churn.

## 2. Schema

`emails` table (`database/migrations/2026_07_29_120000_create_emails_table.php`), a normal `BaseModule` table:

```php
$table->char('id', 36)->primary();
$table->string('name');                     // subject
$table->longText('body')->nullable();
$table->string('from_address')->nullable();
$table->string('from_name')->nullable();
$table->json('to_addresses')->nullable();
$table->json('cc_addresses')->nullable();
$table->timestamp('sent_at')->nullable();
$table->string('direction')->default('logged');   // always 'logged' today — one-directional capture, no outbound sending built
$table->string('provider_message_id')->nullable()->unique();  // RFC 5322 Message-ID, or a content hash if a message omits one — makes relay retries idempotent
$table->string('mailbox')->nullable()->index();    // which capture address received it — see §5
$table->foreignUuid('owner_id')->nullable()->constrained('users')->nullOnDelete();
$table->json('custom_fields')->default(DB::raw('(JSON_OBJECT())'));
```

Registered like any other stock module: `App\Models\Modules\Email` (extends `BaseModule`, `moduleCasts` for the two JSON address arrays + `sent_at`), `App\Handlers\Modules\EmailsModuleHandler`, a `config/modules.php` entry with `is_activity: true`, field definitions in `config/stock_fields.php['emails']`, and `config/module_layouts/emails.php` for list/record layout. No bespoke controller or Vue pages — the generic `RecordController`/`ListController` + `Pages/Modules/{Create,List,Record}.vue` handle CRUD, same as Meetings/Calls/Notes.

`email_capture_addresses` table (`database/migrations/2026_07_30_090000_create_email_capture_addresses_table.php`) is separate infrastructure, not a `BaseModule` — see §4.

## 3. Two kinds of capture address, one resolution path

`EmailInboundWebhookController::resolveMailbox(string $recipient)` checks both, in order, and both ultimately feed the same `Email::create()` call:

```php
protected function resolveMailbox(string $recipient): ?array
{
    $user = $this->addresses->findUserByRecipientAddress($recipient);
    if ($user) {
        return ['slug' => $user->username, 'owner_id' => $user->id];
    }

    $localPart = Str::lower(Str::before($recipient, '@'));
    $address = EmailCaptureAddress::where('slug', $localPart)->first();
    if ($address) {
        return ['slug' => $address->slug, 'owner_id' => $address->owner_id];
    }

    return null;
}
```

### 3.1 Personal addresses — no stored token at all

`App\Services\Users\EmailCaptureAddressService::addressFor(User $user)` builds `{$user->username}@{host}` on the fly from `config('app.url')`'s host — there is no separate token column, no generation step, nothing to look up. This was a deliberate simplification from an earlier design (an opaque `log+{token}@` scheme, matching the security posture of `UserInvite`/`SetupToken`) — rejected because a BCC-capture address is a standing address a user needs to view repeatedly (not a one-time link), and the address's compromise risk is low (worst case: a forged logged email, not account access), so the cleaner, memorable `username@` form was judged worth the tradeoff. `findUserByRecipientAddress()` matches case-insensitively via `whereRaw('LOWER(username) = ?', [...])` rather than trusting the DB column's collation, since SMTP clients don't reliably lowercase local-parts.

Surfaced on the Profile page (`UserProfileController` passes `emailCaptureHost`; `Profile/Index.vue` computes the full address client-side as `${form.username}@${host}` so it updates live if the user edits their username before saving, with a copy-to-clipboard button).

### 3.2 Admin-created team addresses

`App\Models\EmailCaptureAddress` — plain `Model` (not `BaseModule`; infrastructure, sibling to `Module`/`Field`/`Relationship`/`Transformation`), `slug` (unique, validated `^[a-z0-9._-]+$` and checked against every existing `users.username` at creation so a team address can never shadow — or be shadowed by — a personal one), `label`, optional `owner_id`, `created_by`. CRUD via `EmailCaptureAddressController` at `/settings/email-capture-addresses` (list + inline create form + delete; `resources/js/Pages/Settings/EmailCaptureAddresses/List.vue`).

The Settings nav entry for this reused an existing but disabled placeholder in `config/settings.php` (`email.items.inbound-email`, `isActive: 0`) rather than adding a new one — flipped to `isActive: 1` and repointed `path` at the real route.

An ownerless team address (`owner_id === null`) is legitimate — a captured `Email` with no resolved owner falls through to `BaseModule`'s own default-owner logic (`getDefaultOwnerId()`), same as any other module, not a special case in the webhook controller.

## 4. Inbound relay: self-hosted Postfix

One Postfix instance, server-wide, shared by every tenant — not per-tenant infrastructure. Lives under `deploy/postfix/` (pcre lookup tables + `main.cf`/`master.cf` snippets), installed by `deploy/setup-postfix.sh` (idempotent — checks for existing config blocks before appending, safe to re-run).

### 4.1 Catch-all acceptance, not per-tenant domain registration

```
# virtual_mailbox_domains
/^.+\.cubrel\.com$/    1

# virtual_mailbox_maps (existence check — any local-part accepted)
/^.+@.+\.cubrel\.com$/    1
```

Postfix accepts SMTP for *any* `*.cubrel.com` recipient via these two PCRE maps, rather than a maintained list of tenant domains — a new tenant needs zero Postfix/DNS changes; the one wildcard MX record (`*.cubrel.com MX 10 mail.cubrel.com`, added once, manually, in Cloudflare) covers it automatically. `virtual_transport = cubrelrelay:` routes every accepted message to a custom pipe transport instead of real mailbox delivery — nothing is ever stored on the mail server itself.

**Known footgun, now fixed in the installer but worth knowing**: PCRE lookup-table support is a *separate* Debian/Ubuntu package (`postfix-pcre`), not bundled with base `postfix`. Without it, every `RCPT TO` fails with `451 4.3.0 Temporary lookup failure` — a config-looks-correct, fails-at-runtime trap that cost real debugging time during rollout. `setup-postfix.sh` now checks `postconf -m | grep pcre` and installs it if missing.

### 4.2 The pipe transport and relay script

`master.cf`:

```
cubrelrelay unix  -       n       n       -       -       pipe
  flags=DRhu user=cubrelrelay argv=/usr/local/bin/cubrel-inbound-relay.sh ${original_recipient}
```

`${original_recipient}` is the actual SMTP envelope recipient (`RCPT TO`), not anything parsed from the message body — this is load-bearing, not incidental: a BCC'd address never appears in a message's own `To:`/`Cc:` headers by definition, so the envelope recipient is the *only* reliable source of which capture address a message was actually sent to.

`deploy/cubrel-inbound-relay.sh` is a thin relay, not a parser: reads the raw message on stdin, buffers it to a temp file (so `curl` sends a real `Content-Length`, not chunked), and POSTs it to `https://{recipient-domain}/api/webhooks/email-inbound` with `X-Cubrel-Relay-Secret` and `X-Cubrel-Relay-Recipient` headers. `curl --fail --max-time 30` — a non-2xx or a hung tenant app must make the *script* fail too, so Postfix's own queue/retry semantics apply rather than silently swallowing a failed delivery.

### 4.3 Trust model: shared secret, not per-provider HMAC

Runs as an unprivileged `cubrelrelay` system user (created by `setup-postfix.sh`), reading a secret from `/etc/cubrel/inbound-relay-secret` (mode `400`, owned by `cubrelrelay`). This is a **single secret for the whole server**, not generated per-tenant — the relay isn't a third party across the internet, it's infrastructure we control, so `hash_equals($secret, $request->header('X-Cubrel-Relay-Secret'))` is sufficient (`EmailInboundWebhookController::hasValidSecret()`); no HMAC-over-payload scheme needed. `config('services.inbound_relay.secret')` reads it from each tenant's own `INBOUND_RELAY_SECRET` env var, templated in by `provision-tenant.sh` from the ops-level `deploy/provision.env` (§6).

## 5. `EmailInboundWebhookController`: parsing and matching

`POST /api/webhooks/email-inbound` (`routes/api.php`) — deliberately outside the `web`-middleware group other `api.php` routes sit in, so it gets `api.php`'s default stateless middleware (no session, no CSRF) automatically rather than needing an explicit exemption.

```php
$message = (new MailMimeParser())->parse($request->getContent(), false);
$messageId = $message->getMessageId() ?: hash('sha256', $raw);
```

`zbateson/mail-mime-parser` (composer dependency added for this feature) does the actual RFC822 parsing. `getHeader('from'|'to'|'cc')` auto-resolves to `AddressHeader` (confirmed against the installed package's `HeaderFactory`, not assumed) — `AddressHeader::getAddresses()` returns every address including ones inside groups, each with `->getEmail()`/`->getName()`.

Idempotency: `provider_message_id` is the RFC 5322 `Message-ID` header, falling back to a SHA-256 of the raw body for the rare message that omits one. A duplicate relay delivery (Postfix retry after a transient failure) is a no-op, not a second record.

Contact linking reuses the exact same generic-activity-relationship mechanism as every other `is_activity` module (`RelationshipService::link('contacts_emails', ...)`, auto-provisioned by `RelationshipSeeder` from the `is_activity`/`has_activity` flags) — every `from`/`to`/`cc` address is checked case-insensitively against `contacts.email` (indexed — see the folded index in `create_contacts_table.php`), and every match gets linked. No bespoke pivot table.

## 6. Provisioning integration

`provision-tenant.sh` templates one line into every new tenant's `.env`:

```bash
set_env "INBOUND_RELAY_SECRET" "\"${INBOUND_RELAY_SECRET:-}\""
```

...sourced from `deploy/provision.env`, the same ops-level, gitignored file `DB_USERNAME`/`MAIL_*` already live in. Since the secret is server-wide, not per-tenant, this is the *only* provisioning step this feature needs — no DNS registration, no third-party API calls, no per-tenant Postfix config (contrast with the earlier Mailtrap-based design, which needed a `setup-inbound-email.sh` companion script making four separate API calls per tenant; deleted entirely when this feature moved to self-hosted Postfix).

A tenant provisioned before `provision.env` had `INBOUND_RELAY_SECRET` set needs it added to its `.env` by hand (see `DEPLOYMENT.md` §10 "Retrofitting").

`deploy/deploy.sh` (routine updates to an *existing* tenant) deliberately does **not** run any seeders, including for this feature — see §8's `ModulesTableSeeder` note.

## 7. Conversion Rules integration

Every captured `Email` carries `mailbox` (§2) — the slug of whichever address captured it. Because it's a registered `Field` (`config/stock_fields.php['emails']['mailbox']`), it's selectable in the Studio condition builder like any other field, making `mailbox equals "leads"` a real, working [Conversion Rule](conversion-implementation.md) automation trigger.

`database/seeders/TransformationSeeder::seedEmailToContact()` seeds an `Email → Contact` reference rule, but with `automation_enabled: false` — deliberately manual-only. The original intent was auto-creating a Contact only for an *unmatched* sender, but `Transformation::evaluateSingleCondition()` only ever inspects the source record's own field values (§3 of the conversion-implementation doc) — there is no operator for "this record has no linked Contact yet," so that specific trigger can't be expressed with the current condition engine. Same situation as the seeded Lead→Account/Lead→Deal rules, which are also manual-only for the same structural reason.

## 8. Known gaps / not built

- **No automated test coverage** — this feature has no dedicated test suite as of this writing.
- **`ModulesTableSeeder` is destructive** (`DB::table('modules')->delete()` then reinserts only from `config('modules')`) — re-running it against a live tenant would silently delete any custom modules that tenant's admins built via the Module Builder. This is why `deploy/deploy.sh` never calls seeders at all; syncing a *new* stock module (like this one) into an already-provisioned tenant currently has no safe automated path — it needs the seeder rewritten to `updateOrCreate` per module first.
- **No end-to-end test of a team `EmailCaptureAddress` on a live tenant** — personal username-based capture is confirmed working against real inbound mail; an admin-created team address has only been exercised via tinker/unit-level checks, not a real email through Postfix.
- **No outbound sending** — `direction` is always `'logged'`; this is capture-only, matching "Level 1 + BCC capture" from the original feature scope. Real outbound sending (compose-and-send from a record) was scoped as a separate, larger tier and not built.
- **No email threading** — a reply is captured as a completely independent `Email` record; Mailtrap's Messages API exposed `thread_id`/`in_reply_to`/`rfc_message_id` when that provider was in use, but nothing analogous is extracted from the current MIME-parsed messages. Explicitly accepted as fine for now — each entry stands alone on a record's timeline, same as Notes/Calls.

## 9. Reference

- `docs/guides/en/emails-guide.md` — plain-language, user-facing guide.
- `DEPLOYMENT.md` §10 — server-side Postfix setup, DNS records, and both real pitfalls hit during rollout.
- `docs/dev/conversion-implementation.md` — the Conversion Rules engine this feature's `mailbox` field integrates with.
