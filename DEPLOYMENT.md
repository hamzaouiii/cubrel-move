# Deploying Cubrel to Hetzner (multi-tenant)

All instances live side by side on one VPS under `/var/www/cubrel/tenants/`,
each a full independent checkout of a git branch:

| Tenant  | Domain            | Branch | Path                              |
|---------|--------------------|--------|-------------------------------------|
| app     | app.cubrel.com     | main   | `/var/www/cubrel/tenants/app`     |
| demo    | demo.cubrel.com    | demo   | `/var/www/cubrel/tenants/demo`    |
| solar   | solar.cubrel.com   | solar  | `/var/www/cubrel/tenants/solar`   |

(Adjust branch names above to whatever demo/solar actually track.)

Each tenant is fully isolated: its own `.env`, own database, own queue
worker process, own Reverb websocket process. nginx and the TLS cert
(`*.cubrel.com` wildcard, already issued) are the only shared pieces.

Because every tenant runs its own Reverb process on the same machine, **each
one needs a unique Reverb port** — that's the main thing the old
single-instance workflow didn't account for. Everything below is built
around a small port registry plus two systemd *template* units, so adding
tenant #4 is a checklist, not new config to invent from scratch.

## 1. Port registry

Keep one file as the source of truth for which port belongs to which
tenant — `/var/www/cubrel/ops/reverb-ports.conf` (next to the provisioning
scripts, not inside `tenants/` — it's ops bookkeeping, not a tenant, and
`provision-tenant.sh` derives this path as a sibling of wherever it's
running from, not a hardcoded one):

```
app=8081
demo=8082
solar=8083
```

(Starting at 8081 rather than Reverb's default 8080 just avoids ambiguity
with any leftover default-config instance.) When you provision a new
tenant, append the next free port here first — this is the only place port
numbers are decided.

## 2. systemd template units (one-time setup)

Instead of a bespoke unit file per tenant, use systemd's `@`-templating so
one file covers all current and future tenants — `%i` is filled in with the
tenant name at start time (`systemctl start cubrel-queue@demo`).

The unit files live in the repo at
[`deploy/systemd/`](deploy/systemd/) and get installed with
[`deploy/install-systemd-units.sh`](deploy/install-systemd-units.sh) — see
Section 4 for the one-time install command. Their contents, for reference:

`/etc/systemd/system/cubrel-queue@.service`:

```ini
[Unit]
Description=Cubrel queue worker (%i)
After=network.target mysql.service

[Service]
User=www-data
Group=www-data
Restart=always
RestartSec=3
WorkingDirectory=/var/www/cubrel/tenants/%i
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

`/etc/systemd/system/cubrel-reverb@.service`:

```ini
[Unit]
Description=Cubrel Reverb websocket server (%i)
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
RestartSec=3
WorkingDirectory=/var/www/cubrel/tenants/%i
ExecStart=/usr/bin/php artisan reverb:start

[Install]
WantedBy=multi-user.target
```

Note there's no `--host`/`--port` flag on `reverb:start` — Reverb reads
`REVERB_SERVER_HOST`/`REVERB_SERVER_PORT` from that tenant's own `.env`
(Section 4), which is exactly what makes the ports registry the only place
that number lives.

**`REVERB_SERVER_HOST`/`PORT` vs `REVERB_HOST`/`PORT` — don't confuse
them**, this cost real debugging time: `REVERB_SERVER_HOST`/`PORT` is what
`reverb:start` actually binds to. `REVERB_HOST`/`PORT`/`SCHEME` is a
*separate* pair — what Laravel's server-side broadcaster uses to reach
Reverb's internal REST API to publish events. If only `REVERB_PORT` is set
(not `REVERB_SERVER_PORT`), `reverb:start` silently falls back to Reverb's
package default of `8080` regardless of what's in the ports registry —
every tenant collides onto the same port with no error, and nginx proxies
to a port nothing is listening on (`NS_ERROR_WEBSOCKET_CONNECTION_REFUSED`
in the browser). Keep both pairs set to the same value per tenant.

```bash
sudo systemctl daemon-reload
```

You only do this once. From here on, starting a tenant's processes is
`systemctl enable --now cubrel-queue@<name> cubrel-reverb@<name>`.

## 3. nginx vhost template

Same shape as the existing demo.cubrel.com config, with a websocket proxy
block added for Reverb. `/etc/nginx/sites-available/<name>.cubrel.com`:

```nginx
server {
    listen 80;
    server_name <name>.cubrel.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name <name>.cubrel.com;
    root /var/www/cubrel/tenants/<name>/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/cubrel.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/cubrel.com/privkey.pem;

    # Reverb websocket proxy — this tenant's port from reverb-ports.conf.
    # Must come before the generic `location /` block.
    location /app/ {
        proxy_pass http://127.0.0.1:<port>;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 60s;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Replace `<name>` and `<port>` with the tenant's values. Everything else
(cert paths, PHP-FPM socket) is identical across tenants since they share
the wildcard cert and PHP-FPM pool.

## 4. Provisioning a new tenant (automated)

Everything in this section — port assignment, clone, `.env` templating,
build, migrate, systemd start, nginx vhost, reload — is scripted in
[`deploy/provision-tenant.sh`](deploy/provision-tenant.sh). One-time setup,
then one command per new tenant.

**One-time, per server:**

```bash
sudo bash deploy/install-systemd-units.sh          # installs the @-templated units (Section 2)
cp deploy/provision.env.example deploy/provision.env
nano deploy/provision.env                           # REPO_URL, DB_USERNAME, DB_PASSWORD, MAIL_*, (optional) MYSQL_ROOT_PASSWORD
```

`deploy/provision.env` is gitignored (like `deploy.sh` itself) — it holds
real credentials and never gets committed.

**Per new tenant** (example: a tenant called `test`; only `<name>` is
required, everything else is an optional flag):

```bash
sudo bash deploy/provision-tenant.sh test
# or, with everything spelled out:
sudo bash deploy/provision-tenant.sh test \
  --branch test-branch \
  --domain test.cubrel.com \
  --app-name "Cubrel" \
  --email you@example.com
```

`--email` is where the first-setup link gets sent once the tenant is live
(via `php artisan cubrel:bootstrap`) — omit it and the link is printed to
the terminal instead.

What it does, in order: picks the next free Reverb port from
`reverb-ports.conf` (Section 1) → clones the branch → sets
`git config core.fileMode false` (before anything touches permissions) →
writes `.env` with tenant-specific `APP_URL`/`DB_*`/`MAIL_*`/Reverb
keys+port/`VITE_REVERB_*` → optionally creates the database (if
`MYSQL_ROOT_PASSWORD` is set in `provision.env`, otherwise it pauses and
asks you to create it by hand) → `composer install` + `npm run build` →
`migrate:fresh --seed` (safe here — this only ever runs once, against a
database guaranteed empty) → `chmod`/`chown` → starts
`cubrel-queue@test` and `cubrel-reverb@test` → renders and enables the
nginx vhost from `deploy/templates/tenant.nginx.conf.tmpl` → `nginx -t &&
systemctl reload nginx` → generates the first-setup link.

It deliberately does **not** touch `AWS_*` in `.env` — S3 isn't used, so
those keys are left as whatever `.env.example` ships with.

No DNS or cert work needed per tenant — `*.cubrel.com` already resolves via
Cloudflare and the wildcard cert already covers any subdomain.

<details>
<summary>What the script replaces, if you'd rather do it by hand</summary>

```bash
# 0. Register the port first
echo "test=8084" >> /var/www/cubrel/ops/reverb-ports.conf

# 1. Clone the right branch
git clone --branch test git@your-git-host:you/cubrel.git /var/www/cubrel/tenants/test
cd /var/www/cubrel/tenants/test

# 2. Do this BEFORE any chmod/chown, or the first permission fixup makes
#    git think every file changed.
git config core.fileMode false

# 3. .env — copy from .env.example and fill in, tenant-specific:
#    APP_NAME="Cubrel"
#    APP_URL=https://test.cubrel.com
#    DB_DATABASE=cubrel_test              (own database)
#    DB_USERNAME="..." / DB_PASSWORD="..." (quoted — passwords may contain # etc.)
#    DB_HOST=localhost                    (NOT 127.0.0.1 — see Section 9)
#    MAIL_* (mailer, host, port, username, password, encryption, from address/name)
#    REVERB_APP_ID / KEY / SECRET         (own values, don't reuse another tenant's)
#    REVERB_SERVER_HOST=127.0.0.1
#    REVERB_SERVER_PORT=8084              (from step 0 — this is what reverb:start binds to)
#    REVERB_HOST=127.0.0.1
#    REVERB_PORT=8084                     (same port — this is for server-side event publishing)
#    REVERB_SCHEME=http
#    VITE_REVERB_HOST=test.cubrel.com     (public — the domain, not 127.0.0.1)
#    VITE_REVERB_PORT=443
#    VITE_REVERB_SCHEME=https
cp .env.example .env
nano .env
php artisan key:generate

# 4. Build & migrate (migrate:fresh --seed is safe ONLY here — a brand new,
#    guaranteed-empty database. Never run it against a tenant with real data.)
composer install --optimize-autoloader
npm ci && npm run build
php artisan migrate:fresh --seed --force
php artisan storage:link
chmod -R 755 /var/www/cubrel/tenants/test
chown -R www-data:www-data /var/www/cubrel/tenants/test

# 5. Start the per-tenant processes
systemctl enable --now cubrel-queue@test cubrel-reverb@test

# 6. nginx vhost (Section 3 template, name=test, port=8084)
nano /etc/nginx/sites-available/test.cubrel.com
ln -s /etc/nginx/sites-available/test.cubrel.com /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx

# 7. First-setup link — sends the setup email if given an address, otherwise
#    prints the one-time link to the terminal
sudo -u www-data php artisan cubrel:bootstrap you@example.com
```

</details>

## 5. Routine deploys (existing tenants)

`deploy.sh` stays a per-tenant script — run it from inside the tenant's own
directory, same as your current manual `git pull` workflow. It now also
restarts that tenant's Reverb process (queue worker already gets a
`queue:restart` signal; Reverb needs a real restart since it doesn't
hot-reload):

```bash
#!/bin/bash
set -e
git config core.fileMode false
git pull
composer install --optimize-autoloader
php artisan migrate --force
php artisan optimize
npm ci && npm run build
chmod -R 755 .
chown -R www-data:www-data .
php artisan queue:restart
php artisan storage:link

TENANT=$(basename "$PWD")
systemctl restart "cubrel-reverb@${TENANT}"

echo "Deploy done for tenant: ${TENANT}"
```

`TENANT` is derived from the directory name, so this one script works
unmodified for app, demo, solar, and every future tenant — as long as the
tenant folder name matches its systemd instance name (`app`, `demo`,
`solar`, ...), which the provisioning steps above already guarantee.

`systemctl restart` requires root or passwordless sudo for the deploy user;
since `deploy.sh` already runs `chown` (root-only in practice), this should
already be satisfied.

## 6. Verification per tenant

For each tenant after deploying:

- `systemctl status cubrel-queue@<name> cubrel-reverb@<name>` — both
  `active (running)`
- `ss -tlnp | grep <port>` — confirms Reverb is actually bound to its
  registered port and nothing collided with another tenant
- Open `https://<name>.cubrel.com`, check browser devtools → Network → WS —
  a `wss://<name>.cubrel.com/app/...` connection should show **101
  Switching Protocols**, not a failed/red connection
- `php artisan queue:failed` on that tenant — nothing unexpected piling up

## 7. Common pitfall: port collisions

If two tenants ever end up with the same `REVERB_SERVER_PORT`, the second
Reverb process to start will fail to bind (or silently steal traffic from
the first, depending on start order). Always check `reverb-ports.conf`
before picking a port for a new tenant, and keep the file itself as the
single source of truth rather than trusting memory or `.env` files
scattered across tenant directories.

Also watch for the quieter version of this: if only `REVERB_PORT` gets set
and `REVERB_SERVER_PORT` is missing, there's no error at all — `reverb:start`
just silently falls back to the package default (`8080`) and *every* tenant
missing it collides on the same port with no warning. `ss -tlnp | grep php`
(Section 6) is the way to actually verify what's listening, since `.env`
and `reverb-ports.conf` agreeing with each other doesn't prove the running
process agrees with them.

## 8. Common pitfall: stale config cache after hand-editing `.env`

If a tenant was ever deployed via `deploy.sh` (which runs
`php artisan optimize`, i.e. `config:cache`), Laravel stops reading `.env`
at all and serves everything from `bootstrap/cache/config.php` instead.
Hand-editing `.env` directly on the server after that point has **no
effect** until that cache is cleared — the symptom is bizarre-looking
(correct value in `.env`, wrong value in the running app, no error) because
it looks like the edit didn't take when it actually did, it's just not
being read.

```bash
php artisan config:clear
```

Run this after any manual `.env` edit on a live tenant, before restarting
whatever service needed the new value (`cubrel-reverb@<name>`,
`cubrel-queue@<name>`, php-fpm).

## 9. Common pitfall: `DB_HOST=127.0.0.1` gets rejected even with the right password

MySQL's `root`@`localhost` grant only covers Unix-socket connections. PHP's
mysqli/PDO driver treats the literal string `"localhost"` specially — it
forces a socket connection, matching the `mysql` CLI's default behavior.
Any other value, including `"127.0.0.1"`, forces a TCP connection instead,
which MySQL evaluates against a *different* host grant. If there's no
`'root'@'127.0.0.1'` (or `'%'`) grant, a TCP connection gets `Access denied
... (using password: YES)` — a real password, wrongly rejected, because
it's being checked against the wrong grant row entirely.

Always use `DB_HOST=localhost`, not `DB_HOST=127.0.0.1`, unless you've
specifically granted the DB user for TCP too. `provision-tenant.sh` already
defaults to `localhost` for this reason.

## 10. Inbound email relay (Postfix)

Every tenant's [Email Capture](FEATURES.md#22-email-capture) feature is
served by one self-hosted Postfix instance, shared across every tenant on
this server — not a third-party inbound-email provider. This is **one-time,
server-wide setup**, not something that happens per tenant.

### One-time setup

```bash
sudo bash deploy/setup-postfix.sh
```

This installs Postfix (non-interactively) plus `postfix-pcre` (see the
pitfall below), creates an unprivileged `cubrelrelay` system user, writes
the catch-all config (`deploy/postfix/`), and **prints a generated shared
secret** at the end. Two things to do with that output:

1. Add it to `deploy/provision.env` as `INBOUND_RELAY_SECRET="<secret>"` —
   from then on, every new tenant provisioned via `provision-tenant.sh`
   picks it up automatically, no extra steps.
2. Add the wildcard DNS records it tells you to (Cloudflare, `cubrel.com`
   zone):
   - `A` record: `mail` → this server's IP, **DNS only** (grey cloud —
     Cloudflare's proxy only handles HTTP(S), not SMTP; a proxied record
     here breaks mail delivery entirely).
   - `MX` record: `*` (wildcard) → `mail.cubrel.com`, priority `10`.

   One set of records covers every current and future tenant subdomain —
   there is no per-tenant DNS step for this feature.

### How mail actually gets from Postfix to a tenant

Postfix accepts SMTP for any `*.cubrel.com` recipient (a PCRE catch-all,
not a per-tenant domain list) and pipes every accepted message to
`deploy/cubrel-inbound-relay.sh`, which POSTs the raw message plus the
original envelope recipient to that tenant's own
`/api/webhooks/email-inbound`, authenticated with the shared secret above.
`EmailInboundWebhookController` parses the MIME message
(`zbateson/mail-mime-parser`) and matches the recipient to a user or
capture address from there — see
[Email Capture](FEATURES.md#22-email-capture) for the feature itself.

The envelope recipient (not the message's own `To:`/`Cc:` headers) is what
determines the match — required for BCC to work at all, since a BCC'd
address never appears in a message's own headers.

### Retrofitting a tenant provisioned before this was set up

`provision-tenant.sh` only templates `INBOUND_RELAY_SECRET` into a
tenant's `.env` at creation time. A tenant created before
`deploy/provision.env` had that variable set needs it added by hand:

```bash
SECRET=$(cat /etc/cubrel/inbound-relay-secret)
cd /var/www/cubrel/tenants/<name>
grep -q '^INBOUND_RELAY_SECRET=' .env \
  && sed -i "s|^INBOUND_RELAY_SECRET=.*|INBOUND_RELAY_SECRET=\"${SECRET}\"|" .env \
  || echo "INBOUND_RELAY_SECRET=\"${SECRET}\"" >> .env
sudo -u www-data php artisan config:clear
```

### Verification

Send a real email to a real username or capture address at a tenant's
domain, then trace it end to end:

```bash
tail -f /var/log/mail.log                              # Postfix accepted it and ran the relay?
tail -f /var/www/cubrel/tenants/<name>/storage/logs/laravel.log   # app-side result
```

A successful delivery shows `relay=cubrelrelay ... status=sent` in
`mail.log`. Confirm the record actually landed:

```bash
cd /var/www/cubrel/tenants/<name>
sudo -u www-data php artisan tinker --execute="dd(App\Models\Modules\Email::latest()->first());"
```

### Common pitfall: `451 4.3.0 Temporary lookup failure` on every RCPT

PCRE lookup-table support (`pcre:` maps, used for the catch-all domain and
recipient acceptance) is a **separate package on Debian/Ubuntu**, not
bundled with base `postfix` — `setup-postfix.sh` installs it
(`postfix-pcre`), but if this error shows up anyway (e.g. Postfix was
already installed some other way before this feature existed), fix it
directly:

```bash
sudo apt-get install -y postfix-pcre
sudo systemctl restart postfix
```

Confirm the maps themselves work, independent of SMTP entirely:

```bash
postmap -q "test.cubrel.com" pcre:/etc/postfix/cubrel/accepted_domains.pcre
postmap -q "someone@test.cubrel.com" pcre:/etc/postfix/cubrel/accepted_recipients.pcre
```

Both should print `1`.

### Common pitfall: mail accepted and relayed, but the tenant returns 401

`mail.log` will show `status=bounced (... curl: (22) ... 401 ...)` — the
relay reached the app, but the shared secret didn't match. Almost always
means the tenant's `.env` doesn't have `INBOUND_RELAY_SECRET` set (see
"Retrofitting" above) or has a stale cached config from before it was
added — `php artisan config:clear` after any `.env` edit, same as the
general config-cache pitfall in Section 8.
