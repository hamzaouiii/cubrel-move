#!/bin/bash
# Provisions a new Cubrel tenant end to end: registers a Reverb port, clones
# the branch, templates .env, builds/migrates, starts the per-tenant systemd
# services, and writes+enables the nginx vhost.
#
# Usage: provision-tenant.sh <name> [branch] [domain] [app_name]
#   branch defaults to <name>
#   domain defaults to <name>.cubrel.com
#   app_name (APP_NAME in .env) defaults to "Cubrel"
#
# Requires deploy/install-systemd-units.sh to have been run once already,
# and deploy/provision.env to exist (copy from provision.env.example).
set -euo pipefail

if [ "$(id -u)" -ne 0 ]; then
  echo "Must run as root (needs to write to /etc/nginx, /etc/systemd, chown www-data)." >&2
  exit 1
fi

NAME="${1:-}"
BRANCH="${2:-$NAME}"
DOMAIN="${3:-$NAME.cubrel.com}"
APP_NAME="${4:-Cubrel}"

if [ -z "$NAME" ]; then
  echo "Usage: $0 <name> [branch] [domain]" >&2
  exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TENANTS_DIR="/var/www/cubrel/tenants"
TENANT_DIR="$TENANTS_DIR/$NAME"
PORTS_FILE="$TENANTS_DIR/reverb-ports.conf"
CONFIG_FILE="$SCRIPT_DIR/provision.env"

if [ ! -f "$CONFIG_FILE" ]; then
  echo "Missing $CONFIG_FILE — copy provision.env.example and fill it in first." >&2
  exit 1
fi
# shellcheck disable=SC1090
source "$CONFIG_FILE"

if [ -d "$TENANT_DIR" ]; then
  echo "Tenant directory already exists: $TENANT_DIR" >&2
  exit 1
fi

mkdir -p "$TENANTS_DIR"
touch "$PORTS_FILE"

if grep -q "^${NAME}=" "$PORTS_FILE"; then
  echo "Tenant '$NAME' already has a port registered in $PORTS_FILE" >&2
  exit 1
fi

# --- 1. Assign the next free Reverb port ---------------------------------
LAST_PORT=$( { grep -oE '=[0-9]+$' "$PORTS_FILE" | tr -d '=' | sort -n | tail -1; } || true )
PORT=$(( ${LAST_PORT:-8080} + 1 ))
echo "==> Assigning Reverb port $PORT to tenant '$NAME'"

# --- 2. Clone the branch ---------------------------------------------------
echo "==> Cloning branch '$BRANCH' into $TENANT_DIR"
git clone --branch "$BRANCH" "$REPO_URL" "$TENANT_DIR"
cd "$TENANT_DIR"

# --- 3. Template .env -------------------------------------------------------
echo "==> Writing .env"
cp .env.example .env

REVERB_APP_ID=$(openssl rand -hex 8)
REVERB_APP_KEY=$(openssl rand -hex 16)
REVERB_APP_SECRET=$(openssl rand -hex 20)
DB_DATABASE="cubrel_${NAME}"

set_env() {
  # Rewrites .env line-by-line instead of using sed, so values containing
  # sed-special characters (#, &, /, etc. — anything a password might have)
  # can't corrupt the substitution.
  local key="$1" value="$2"
  local tmp found=0
  tmp=$(mktemp)
  while IFS= read -r line || [ -n "$line" ]; do
    if [[ "$line" == "${key}="* || "$line" == "# ${key}="* ]]; then
      printf '%s=%s\n' "$key" "$value" >> "$tmp"
      found=1
    else
      printf '%s\n' "$line" >> "$tmp"
    fi
  done < .env
  if [ "$found" -eq 0 ]; then
    printf '%s=%s\n' "$key" "$value" >> "$tmp"
  fi
  mv "$tmp" .env
}

set_env "APP_NAME" "\"${APP_NAME}\""
set_env "APP_ENV" "production"
set_env "APP_DEBUG" "false"
set_env "APP_URL" "https://${DOMAIN}"
set_env "DB_CONNECTION" "mysql"
set_env "DB_HOST" "127.0.0.1"
set_env "DB_DATABASE" "$DB_DATABASE"
set_env "DB_USERNAME" "$DB_USERNAME"
set_env "DB_PASSWORD" "$DB_PASSWORD"
set_env "SESSION_SECURE_COOKIE" "true"
set_env "BROADCAST_CONNECTION" "reverb"
set_env "REVERB_APP_ID" "$REVERB_APP_ID"
set_env "REVERB_APP_KEY" "$REVERB_APP_KEY"
set_env "REVERB_APP_SECRET" "$REVERB_APP_SECRET"
set_env "REVERB_HOST" "127.0.0.1"
set_env "REVERB_PORT" "$PORT"
set_env "REVERB_SCHEME" "http"
set_env "VITE_REVERB_APP_KEY" '"${REVERB_APP_KEY}"'
set_env "VITE_REVERB_HOST" "$DOMAIN"
set_env "VITE_REVERB_PORT" "443"
set_env "VITE_REVERB_SCHEME" "https"

# --- 4. Optional: create the database --------------------------------------
if [ -n "${MYSQL_ROOT_PASSWORD:-}" ]; then
  echo "==> Creating database $DB_DATABASE"
  mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e \
    "CREATE DATABASE IF NOT EXISTS \`${DB_DATABASE}\`; \
     GRANT ALL PRIVILEGES ON \`${DB_DATABASE}\`.* TO '${DB_USERNAME}'@'localhost'; \
     FLUSH PRIVILEGES;"
else
  echo "==> MYSQL_ROOT_PASSWORD not set — skipping DB creation."
  echo "    Create it manually: CREATE DATABASE \`${DB_DATABASE}\`; GRANT ALL ON \`${DB_DATABASE}\`.* TO '${DB_USERNAME}'@'localhost';"
  read -rp "Press enter once the database exists and is reachable... "
fi

# --- 5. Build & migrate ------------------------------------------------------
echo "==> composer install"
composer install --no-dev --optimize-autoloader
php artisan key:generate --force
echo "==> npm build"
npm ci && npm run build
echo "==> migrate"
php artisan migrate --force
php artisan storage:link
chown -R www-data:www-data "$TENANT_DIR"

# --- 6. Register the port now that setup succeeded --------------------------
echo "${NAME}=${PORT}" >> "$PORTS_FILE"

# --- 7. Start per-tenant systemd services -----------------------------------
echo "==> Starting queue worker + Reverb"
systemctl enable --now "cubrel-queue@${NAME}" "cubrel-reverb@${NAME}"

# --- 8. nginx vhost -----------------------------------------------------------
echo "==> Writing nginx vhost"
VHOST_FILE="/etc/nginx/sites-available/${DOMAIN}"
sed -e "s/__NAME__/${NAME}/g" -e "s/__DOMAIN__/${DOMAIN}/g" -e "s/__PORT__/${PORT}/g" \
  "$SCRIPT_DIR/templates/tenant.nginx.conf.tmpl" > "$VHOST_FILE"
ln -sf "$VHOST_FILE" "/etc/nginx/sites-enabled/${DOMAIN}"
nginx -t && systemctl reload nginx

cat <<EOF

==> Done. Tenant '$NAME' is live at https://${DOMAIN}
    Reverb port: $PORT (registered in $PORTS_FILE)
    Queue/Reverb: systemctl status cubrel-queue@${NAME} cubrel-reverb@${NAME}

Still needs a manual look before this is really production-ready:
  - MAIL_* settings in $TENANT_DIR/.env (still whatever .env.example defaults to)
  - AWS_* settings in $TENANT_DIR/.env, if this tenant uses S3
  - Confirm DNS for ${DOMAIN} is resolving (Cloudflare wildcard should already cover it)
EOF
