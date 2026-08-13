#!/bin/bash
# Reverses provision-tenant.sh: stops the per-tenant systemd services, removes
# the nginx vhost, removes the crontab line, drops the tenant's database,
# frees its Reverb port, and deletes the tenant directory.
#
# Usage: deprovision-tenant.sh <name> [--yes]
#   --yes   skip the interactive "type the name to confirm" prompt
#
# Requires deploy/provision.env to exist (same file provision-tenant.sh uses)
# so the database can be dropped with the right credentials.
set -euo pipefail

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
  echo "${C_YELLOW}Usage: $0 <name> [--yes]${C_RESET}" >&2
  exit 1
}

if [ "$(id -u)" -ne 0 ]; then
  err "Must run as root (needs to write to /etc/nginx, /etc/systemd, crontab -u www-data)."
  exit 1
fi

NAME="${1:-}"
[ -n "$NAME" ] && [ "${NAME#--}" = "$NAME" ] || usage
shift

CONFIRM=1
while [ $# -gt 0 ]; do
  case "$1" in
    --yes) CONFIRM=0; shift ;;
    *) err "Unknown argument: $1"; usage ;;
  esac
done

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

if [ ! -d "$TENANT_DIR" ]; then
  err "No tenant directory at $TENANT_DIR"
  exit 1
fi

DB_DATABASE="cubrel_${NAME}"

echo "This will permanently delete tenant '${C_BOLD}${NAME}${C_RESET}':"
echo "  - stop/disable cubrel-queue@${NAME} and cubrel-reverb@${NAME}"
echo "  - remove nginx vhost for its domain"
echo "  - remove its www-data crontab entry"
echo "  - DROP DATABASE \`${DB_DATABASE}\`"
echo "  - free its line in $PORTS_FILE"
echo "  - rm -rf $TENANT_DIR"
echo
if [ "$CONFIRM" -eq 0 ]; then
  :
else
  read -rp "Type the tenant name to confirm: " TYPED
  if [ "$TYPED" != "$NAME" ]; then
    err "Name didn't match — aborting."
    exit 1
  fi
fi

# --- 1. Stop & disable per-tenant systemd services ---------------------------
step "Stopping queue worker + Reverb"
systemctl disable --now "cubrel-queue@${NAME}" "cubrel-reverb@${NAME}" 2>/dev/null || \
  warn "Could not stop/disable one or both services (maybe already stopped)."

# --- 2. Remove nginx vhost ----------------------------------------------------
step "Removing nginx vhost"
VHOST_FILE=$(grep -rlF "root ${TENANT_DIR}/public" /etc/nginx/sites-available 2>/dev/null | head -1 || true)
if [ -n "$VHOST_FILE" ]; then
  DOMAIN_FILE="$(basename "$VHOST_FILE")"
  rm -f "/etc/nginx/sites-enabled/${DOMAIN_FILE}" "$VHOST_FILE"
  nginx -t && systemctl reload nginx
else
  warn "Couldn't find an nginx vhost referencing $TENANT_DIR — skipping."
fi

# --- 3. Remove crontab entry --------------------------------------------------
step "Removing cron schedule entry"
( crontab -u www-data -l 2>/dev/null | grep -vF "cd $TENANT_DIR &&" ) | crontab -u www-data - 2>/dev/null || true

# --- 4. Drop the database -----------------------------------------------------
if [ -n "${MYSQL_ROOT_PASSWORD:-}" ]; then
  step "Dropping database $DB_DATABASE"
  mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e "DROP DATABASE IF EXISTS \`${DB_DATABASE}\`;"
else
  warn "MYSQL_ROOT_PASSWORD not set — skipping DB drop."
  echo "    Drop it manually: DROP DATABASE \`${DB_DATABASE}\`;"
fi

# --- 5. Free the Reverb port --------------------------------------------------
if [ -f "$PORTS_FILE" ] && grep -q "^${NAME}=" "$PORTS_FILE"; then
  step "Freeing Reverb port in $PORTS_FILE"
  tmp=$(mktemp)
  grep -v "^${NAME}=" "$PORTS_FILE" > "$tmp" || true
  mv "$tmp" "$PORTS_FILE"
fi

# --- 6. Delete the tenant directory -------------------------------------------
step "Deleting $TENANT_DIR"
rm -rf "$TENANT_DIR"

echo
ok "Done. Tenant '$NAME' has been deprovisioned."
