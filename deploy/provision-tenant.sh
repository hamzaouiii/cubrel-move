#!/bin/bash
# Provisions a new Cubrel tenant end to end: registers a Reverb port, clones
# the branch, templates .env, builds/migrates, starts the per-tenant systemd
# services, and writes+enables the nginx vhost.
#
# Usage: provision-tenant.sh <name> [--branch B] [--domain D] [--app-name A] [--email E]
#   --branch     defaults to <name>
#   --domain     defaults to <name>.cubrel.com
#   --app-name   APP_NAME in .env — defaults to "Cubrel"
#   --email      where the first-setup link gets sent (via `cubrel:bootstrap`)
#                once the tenant is live. Omitted, the link is printed to the
#                terminal instead of emailed.
#
# Requires deploy/install-systemd-units.sh to have been run once already,
# and deploy/provision.env to exist (copy from provision.env.example).
# Inbound email capture (BCC-to-CRM) needs no per-tenant setup here at
# all — see deploy/setup-postfix.sh, a one-time server-wide install plus
# a single wildcard MX record that covers every tenant automatically.
set -euo pipefail

# Colors only when stdout is an actual terminal — plain text if redirected
# to a file or piped, so logs don't fill up with escape codes.
if [ -t 1 ]; then
  C_RESET=$'\033[0m'; C_BOLD=$'\033[1m'
  C_CYAN=$'\033[36m'; C_GREEN=$'\033[32m'; C_YELLOW=$'\033[33m'; C_RED=$'\033[31m'
else
  C_RESET=''; C_BOLD=''; C_CYAN=''; C_GREEN=''; C_YELLOW=''; C_RED=''
fi

step() { echo "${C_CYAN}${C_BOLD}==>${C_RESET} $*"; }
ok()   { echo "${C_GREEN}${C_BOLD}==>${C_RESET} $*"; }
warn() { echo "${C_YELLOW}${C_BOLD}==>${C_RESET} $*"; }
err()  { echo "${C_RED}${C_BOLD}Error:${C_RESET} $*" >&2; }

usage() {
  echo "${C_YELLOW}Usage: $0 <name> [--branch B] [--domain D] [--app-name A] [--email E]${C_RESET}" >&2
  exit 1
}

if [ "$(id -u)" -ne 0 ]; then
  err "Must run as root (needs to write to /etc/nginx, /etc/systemd, chown www-data)."
  exit 1
fi

NAME="${1:-}"
[ -n "$NAME" ] && [ "${NAME#--}" = "$NAME" ] || usage
shift

BRANCH=""
DOMAIN=""
APP_NAME="Cubrel"
EMAIL=""

while [ $# -gt 0 ]; do
  case "$1" in
    --branch) BRANCH="${2:-}"; shift 2 ;;
    --domain) DOMAIN="${2:-}"; shift 2 ;;
    --app-name) APP_NAME="${2:-}"; shift 2 ;;
    --email) EMAIL="${2:-}"; shift 2 ;;
    *) err "Unknown argument: $1"; usage ;;
  esac
done

BRANCH="${BRANCH:-$NAME}"
DOMAIN="${DOMAIN:-$NAME.cubrel.com}"

# SCRIPT_DIR is wherever this script actually lives (the "ops" directory —
# e.g. /var/www/cubrel/ops). TENANTS_DIR is derived as its sibling rather
# than hardcoded, so this still works if /var/www/cubrel ever moves.
# reverb-ports.conf lives next to this script, not inside tenants/ — it's
# ops bookkeeping (root-owned, read/written only by this script), not a
# tenant, and mixing it into tenants/ made that directory's contents
# ambiguous ("is this a tenant folder or not?").
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BASE_DIR="$(dirname "$SCRIPT_DIR")"
TENANTS_DIR="$BASE_DIR/tenants"
TENANT_DIR="$TENANTS_DIR/$NAME"
PORTS_FILE="$SCRIPT_DIR/reverb-ports.conf"
CONFIG_FILE="$SCRIPT_DIR/provision.env"

if [ ! -f "$CONFIG_FILE" ]; then
  err "Missing $CONFIG_FILE — copy provision.env.example and fill it in first."
  exit 1
fi
# shellcheck disable=SC1090
source "$CONFIG_FILE"

if [ -d "$TENANT_DIR" ]; then
  err "Tenant directory already exists: $TENANT_DIR"
  exit 1
fi

mkdir -p "$TENANTS_DIR"
touch "$PORTS_FILE"

if grep -q "^${NAME}=" "$PORTS_FILE"; then
  err "Tenant '$NAME' already has a port registered in $PORTS_FILE"
  exit 1
fi

# --- 1. Assign the next free Reverb port ---------------------------------
LAST_PORT=$( { grep -oE '=[0-9]+$' "$PORTS_FILE" | tr -d '=' | sort -n | tail -1; } || true )
PORT=$(( ${LAST_PORT:-8080} + 1 ))
step "Assigning Reverb port $PORT to tenant '$NAME'"

# --- 2. Clone the branch ---------------------------------------------------
step "Cloning branch '$BRANCH' into $TENANT_DIR"
git clone --branch "$BRANCH" "$REPO_URL" "$TENANT_DIR"
cd "$TENANT_DIR"

# From here on, a failed run leaves a half-provisioned tenant dir that
# blocks retrying ("Tenant directory already exists") and, once the DB
# step below has run, an orphaned database too. Clean both up on any
# non-zero exit so a retry can just be run again as-is.
cleanup_on_failure() {
  local exit_code=$?
  [ "$exit_code" -eq 0 ] && return
  err "Provisioning failed (exit $exit_code) — cleaning up partial tenant '$NAME'."
  cd /
  rm -rf "$TENANT_DIR"
  if [ -n "${MYSQL_ROOT_PASSWORD:-}" ]; then
    mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e "DROP DATABASE IF EXISTS \`${DB_DATABASE:-cubrel_$NAME}\`;" 2>/dev/null || true
  fi
}
trap cleanup_on_failure EXIT

# Fail fast, before .env is written or the database is touched, if
# composer.json and composer.lock have drifted out of sync (e.g. someone
# edited composer.json without regenerating the lock file before merging
# to main). The CI check in .github/workflows/composer-lock-check.yml is
# meant to stop that from reaching main at all — this is the fallback.
step "Validating composer.lock is in sync with composer.json"
composer validate --strict --no-check-publish

# validate above only checks composer.json/composer.lock content against
# each other — it says nothing about whether *this machine* can actually
# install what the lock requires. That's a separate check: PHP version and
# every ext-* the locked packages declare, compared against what's actually
# loaded here. This is the same check `composer install` runs internally
# (the "Verifying lock file contents can be installed on current platform"
# step) — running it explicitly first just surfaces a missing-extension
# failure before .env/DB exist instead of after.
step "Checking PHP platform requirements (extensions, version)"
composer check-platform-reqs --lock

# Set this BEFORE any chmod/chown below — otherwise the very first
# permission fixup makes git think every file changed. This isn't a
# workaround: it's git's own mechanism for "ops-managed permissions differ
# from what's committed", which is exactly this situation (www-data needs
# to own/read files that were committed under a dev user's umask).
git config core.fileMode false

# --- 3. Template .env -------------------------------------------------------
step "Writing .env"
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
# "localhost" (not "127.0.0.1") so PHP's mysqli/PDO driver uses the Unix
# socket, matching how the root MySQL account is actually granted — TCP to
# 127.0.0.1 hits a different host-match and gets rejected even with the
# right password.
set_env "DB_CONNECTION" "mysql"
set_env "DB_HOST" "localhost"
set_env "DB_DATABASE" "$DB_DATABASE"
set_env "DB_USERNAME" "\"${DB_USERNAME}\""
set_env "DB_PASSWORD" "\"${DB_PASSWORD}\""
set_env "SESSION_SECURE_COOKIE" "true"

# Shared mail account across all tenants (AWS/S3 isn't used, so those keys
# in .env.example are left untouched — just whatever ships in the template).
set_env "MAIL_MAILER" "${MAIL_MAILER:-log}"
set_env "MAIL_HOST" "${MAIL_HOST:-127.0.0.1}"
set_env "MAIL_PORT" "${MAIL_PORT:-2525}"
set_env "MAIL_USERNAME" "\"${MAIL_USERNAME:-}\""
set_env "MAIL_PASSWORD" "\"${MAIL_PASSWORD:-}\""
set_env "MAIL_ENCRYPTION" "${MAIL_ENCRYPTION:-null}"
set_env "MAIL_FROM_ADDRESS" "${MAIL_FROM_ADDRESS:-noreply@$DOMAIN}"
set_env "MAIL_FROM_NAME" "\"${MAIL_FROM_NAME:-$APP_NAME}\""

# Shared secret for the self-hosted Postfix inbound relay (see
# deploy/setup-postfix.sh) — one value for the whole server, not
# generated per-tenant, since the relay isn't a third party.
set_env "INBOUND_RELAY_SECRET" "\"${INBOUND_RELAY_SECRET:-}\""

set_env "BROADCAST_CONNECTION" "reverb"
set_env "REVERB_APP_ID" "$REVERB_APP_ID"
set_env "REVERB_APP_KEY" "$REVERB_APP_KEY"
set_env "REVERB_APP_SECRET" "$REVERB_APP_SECRET"
# REVERB_HOST/PORT: what Laravel's server-side broadcaster uses to reach
# Reverb's internal REST API (publishing events).
set_env "REVERB_HOST" "127.0.0.1"
set_env "REVERB_PORT" "$PORT"
set_env "REVERB_SCHEME" "http"
# REVERB_SERVER_HOST/PORT: what `reverb:start` actually binds to. Distinct
# from REVERB_HOST/PORT above — laravel/reverb's default config.php falls
# back to REVERB_SERVER_PORT=8080 if this isn't set, regardless of
# REVERB_PORT, which silently collided every tenant onto the same port.
set_env "REVERB_SERVER_HOST" "127.0.0.1"
set_env "REVERB_SERVER_PORT" "$PORT"
set_env "VITE_REVERB_APP_KEY" '"${REVERB_APP_KEY}"'
set_env "VITE_REVERB_HOST" "$DOMAIN"
set_env "VITE_REVERB_PORT" "443"
set_env "VITE_REVERB_SCHEME" "https"

# --- 4. Optional: create the database --------------------------------------
if [ -n "${MYSQL_ROOT_PASSWORD:-}" ]; then
  step "Creating database $DB_DATABASE"
  mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e \
    "CREATE DATABASE IF NOT EXISTS \`${DB_DATABASE}\`; \
     GRANT ALL PRIVILEGES ON \`${DB_DATABASE}\`.* TO '${DB_USERNAME}'@'localhost'; \
     FLUSH PRIVILEGES;"
else
  warn "MYSQL_ROOT_PASSWORD not set — skipping DB creation."
  echo "    Create it manually: CREATE DATABASE \`${DB_DATABASE}\`; GRANT ALL ON \`${DB_DATABASE}\`.* TO '${DB_USERNAME}'@'localhost';"
  read -rp "Press enter once the database exists and is reachable... "
fi

# --- 5. Build & migrate ------------------------------------------------------
# Full install, not --no-dev: fakerphp/faker is a require-dev package but
# Database\Factories\*::fake() is used at runtime for onboarding/demo data
# seeding, so it has to be present in production too.
step "composer install"
composer update
composer install --optimize-autoloader
php artisan key:generate --force
step "npm build"
npm ci && npm run build


step "migrate:fresh --seed"
php artisan migrate:fresh --seed --force
php artisan storage:link

chmod -R 755 "$TENANT_DIR"
chown -R www-data:www-data "$TENANT_DIR"

# --- 6. Register the port now that setup succeeded --------------------------
echo "${NAME}=${PORT}" >> "$PORTS_FILE"

# --- 7. Start per-tenant systemd services -----------------------------------
step "Starting queue worker + Reverb"
systemctl enable --now "cubrel-queue@${NAME}" "cubrel-reverb@${NAME}"

# --- 8. nginx vhost -----------------------------------------------------------
step "Writing nginx vhost"
VHOST_FILE="/etc/nginx/sites-available/${DOMAIN}"
sed -e "s/__NAME__/${NAME}/g" -e "s/__DOMAIN__/${DOMAIN}/g" -e "s/__PORT__/${PORT}/g" \
  "$SCRIPT_DIR/templates/tenant.nginx.conf.tmpl" > "$VHOST_FILE"
ln -sf "$VHOST_FILE" "/etc/nginx/sites-enabled/${DOMAIN}"
nginx -t && systemctl reload nginx

# --- 9. Laravel scheduler cron entry ------------------------------------------
# routes/console.php's Schedule::*() calls only fire if something calls
# `php artisan schedule:run` every minute — Laravel decides internally what's
# actually due. One crontab line per tenant, under www-data (same user
# Reverb/queue already run as). Filtered by tenant dir first so re-running
# this against an existing crontab never duplicates the line.
echo "==> Registering cron schedule"
systemctl enable --now cron >/dev/null 2>&1 || true
CRON_LINE="* * * * * cd $TENANT_DIR && php artisan schedule:run >> /dev/null 2>&1"
( crontab -u www-data -l 2>/dev/null | grep -vF "cd $TENANT_DIR &&"; echo "$CRON_LINE" ) | crontab -u www-data -

# --- 10. First-setup link ------------------------------------------------------
# Run as www-data (not root) since the tree was just chowned to it — this
# writes a setup token to the DB and, if $EMAIL is set, sends real mail
# using the MAIL_* values templated into .env back in step 3.
step "Generating first-setup link"
if [ -n "$EMAIL" ]; then
  sudo -u www-data php artisan cubrel:bootstrap "$EMAIL"
else
  sudo -u www-data php artisan cubrel:bootstrap
fi

echo
ok "Done. Tenant '$NAME' is live at ${C_BOLD}https://${DOMAIN}${C_RESET}"
echo "    Reverb port: $PORT (registered in $PORTS_FILE)"
echo "    Queue/Reverb: systemctl status cubrel-queue@${NAME} cubrel-reverb@${NAME}"
