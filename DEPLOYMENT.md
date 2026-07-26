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
tenant — `/var/www/cubrel/tenants/reverb-ports.conf`:

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
`REVERB_HOST`/`REVERB_PORT` from that tenant's own `.env` (Section 4), which
is exactly what makes the ports registry the only place that number lives.

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
nano deploy/provision.env                           # REPO_URL, DB_USERNAME, DB_PASSWORD, (optional) MYSQL_ROOT_PASSWORD
```

`deploy/provision.env` is gitignored (like `deploy.sh` itself) — it holds
real credentials and never gets committed.

**Per new tenant** (example: a tenant called `test` on branch `test`,
domain defaults to `test.cubrel.com`):

```bash
sudo bash deploy/provision-tenant.sh test
# or, if the branch/domain don't follow the name:
sudo bash deploy/provision-tenant.sh test test-branch test.cubrel.com
```

What it does, in order: picks the next free Reverb port from
`reverb-ports.conf` (Section 1) → clones the branch → writes `.env` with
tenant-specific `APP_URL`/`DB_DATABASE`/Reverb keys+port/`VITE_REVERB_*` →
optionally creates the database (if `MYSQL_ROOT_PASSWORD` is set in
`provision.env`, otherwise it pauses and asks you to create it by hand) →
`composer install` + `npm run build` + `migrate` → starts
`cubrel-queue@test` and `cubrel-reverb@test` → renders and enables the
nginx vhost from `deploy/templates/tenant.nginx.conf.tmpl` → `nginx -t &&
systemctl reload nginx`.

It deliberately does **not** touch `MAIL_*`/`AWS_*` in `.env` — those need
real per-tenant credentials that aren't safe to template automatically, and
it prints a reminder to fill them in at the end.

No DNS or cert work needed per tenant — `*.cubrel.com` already resolves via
Cloudflare and the wildcard cert already covers any subdomain.

<details>
<summary>What the script replaces, if you'd rather do it by hand</summary>

```bash
# 0. Register the port first
echo "test=8084" >> /var/www/cubrel/tenants/reverb-ports.conf

# 1. Clone the right branch
git clone --branch test git@your-git-host:you/cubrel.git /var/www/cubrel/tenants/test
cd /var/www/cubrel/tenants/test

# 2. .env — copy from .env.example and fill in, tenant-specific:
#    APP_URL=https://test.cubrel.com
#    DB_DATABASE=cubrel_test              (own database)
#    REVERB_APP_ID / KEY / SECRET         (own values, don't reuse another tenant's)
#    REVERB_HOST=127.0.0.1
#    REVERB_PORT=8084                     (from step 0)
#    REVERB_SCHEME=http
#    VITE_REVERB_HOST=test.cubrel.com     (public — the domain, not 127.0.0.1)
#    VITE_REVERB_PORT=443
#    VITE_REVERB_SCHEME=https
cp .env.example .env
nano .env
php artisan key:generate

# 3. Build & migrate
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan storage:link
chown -R www-data:www-data /var/www/cubrel/tenants/test

# 4. Start the per-tenant processes
systemctl enable --now cubrel-queue@test cubrel-reverb@test

# 5. nginx vhost (Section 3 template, name=test, port=8084)
nano /etc/nginx/sites-available/test.cubrel.com
ln -s /etc/nginx/sites-available/test.cubrel.com /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx
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
composer install --no-dev --optimize-autoloader
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

If two tenants ever end up with the same `REVERB_PORT`, the second Reverb
process to start will fail to bind (or silently steal traffic from the
first, depending on start order). Always check `reverb-ports.conf` before
picking a port for a new tenant, and keep the file itself as the single
source of truth rather than trusting memory or `.env` files scattered
across tenant directories.
