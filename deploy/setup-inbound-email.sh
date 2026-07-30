#!/bin/bash
# Registers (or retries registering) Mailtrap inbound email capture for ONE
# tenant: Mailtrap domain -> inbound_enabled -> DNS records written to
# Cloudflare -> a catch-all inbox -> a webhook, then writes
# MAILTRAP_API_TOKEN + MAILTRAP_INBOUND_WEBHOOK_SECRET into that tenant's
# .env. Safe to re-run against an already-provisioned tenant whose first
# attempt failed or was skipped (e.g. provision.env didn't have the
# Mailtrap/Cloudflare vars set yet at provisioning time) — it doesn't touch
# anything else (no re-clone, no re-migrate, no service restart).
#
# Called internally by provision-tenant.sh right after it writes a new
# tenant's .env, and also meant to be run standalone against an existing
# tenant directory.
#
# Usage: setup-inbound-email.sh <name>
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
  echo "${C_YELLOW}Usage: $0 <name>${C_RESET}" >&2
  exit 1
}

if [ "$(id -u)" -ne 0 ]; then
  err "Must run as root (writes the tenant's .env, chowns it back to www-data)."
  exit 1
fi

if ! command -v jq >/dev/null 2>&1; then
  err "jq is required — install it first (apt-get install -y jq)."
  exit 1
fi

NAME="${1:-}"
[ -n "$NAME" ] || usage

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BASE_DIR="$(dirname "$SCRIPT_DIR")"
TENANT_DIR="$BASE_DIR/tenants/$NAME"
CONFIG_FILE="$SCRIPT_DIR/provision.env"

if [ ! -d "$TENANT_DIR" ]; then
  err "No such tenant directory: $TENANT_DIR"
  exit 1
fi

if [ ! -f "$CONFIG_FILE" ]; then
  err "Missing $CONFIG_FILE — copy provision.env.example and fill it in first."
  exit 1
fi
# shellcheck disable=SC1090
source "$CONFIG_FILE"

if [ -z "${MAILTRAP_API_TOKEN:-}" ] || [ -z "${CLOUDFLARE_API_TOKEN:-}" ] || [ -z "${CLOUDFLARE_ZONE_ID:-}" ]; then
  err "MAILTRAP_API_TOKEN / CLOUDFLARE_API_TOKEN / CLOUDFLARE_ZONE_ID not all set in $CONFIG_FILE — nothing to do."
  exit 1
fi

cd "$TENANT_DIR"

if [ ! -f .env ]; then
  err "$TENANT_DIR/.env doesn't exist — this tenant was never fully provisioned."
  exit 1
fi

# Read the domain from what's actually configured, not reconstructed from
# $NAME — the tenant may have been provisioned with a custom --domain.
DOMAIN=$(grep -m1 '^APP_URL=' .env | sed -E 's/^APP_URL="?https?:\/\/([^"]+)"?$/\1/')

if [ -z "$DOMAIN" ]; then
  err "Could not read APP_URL from $TENANT_DIR/.env"
  exit 1
fi

set_env() {
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

# --- 1. Domain + inbound + DNS ----------------------------------------------
step "Registering '${DOMAIN}' with Mailtrap (domain + inbound + DNS)"

DOMAIN_RESPONSE=$(curl -sS -X POST "https://mailtrap.io/api/domains" \
  -H "Api-Token: ${MAILTRAP_API_TOKEN}" -H "Content-Type: application/json" \
  -d "$(jq -n --arg d "$DOMAIN" '{domain: {domain_name: $d}}')")
DOMAIN_ID=$(echo "$DOMAIN_RESPONSE" | jq -r '.id // empty')

if [ -z "$DOMAIN_ID" ]; then
  err "Could not register '${DOMAIN}' with Mailtrap — response:"
  echo "    $DOMAIN_RESPONSE"
  echo "    (A domain name already registered on this Mailtrap account will fail here on retry — check the Mailtrap dashboard before assuming this is broken.)"
  exit 1
fi

DOMAIN_RESPONSE=$(curl -sS -X PATCH "https://mailtrap.io/api/domains/${DOMAIN_ID}" \
  -H "Api-Token: ${MAILTRAP_API_TOKEN}" -H "Content-Type: application/json" \
  -d '{"domain":{"inbound_enabled":true}}')

DNS_RECORD_COUNT=$(echo "$DOMAIN_RESPONSE" | jq '.dns_records | length' 2>/dev/null || echo 0)
if [ "$DNS_RECORD_COUNT" -gt 0 ] 2>/dev/null; then
  step "Writing $DNS_RECORD_COUNT DNS record(s) to Cloudflare for '${DOMAIN}'"
  echo "$DOMAIN_RESPONSE" | jq -c '.dns_records[]' | while read -r record; do
    RTYPE=$(echo "$record" | jq -r '.type')
    RNAME=$(echo "$record" | jq -r '.name')
    RVALUE=$(echo "$record" | jq -r '.value')
    RPRIORITY=$(echo "$record" | jq -r '.priority // 10')

    CF_PAYLOAD=$(jq -n --arg type "$RTYPE" --arg name "$RNAME" --arg content "$RVALUE" --argjson priority "$RPRIORITY" \
      '{type: $type, name: $name, content: $content, priority: $priority, ttl: 3600}')

    curl -sS -X POST "https://api.cloudflare.com/client/v4/zones/${CLOUDFLARE_ZONE_ID}/dns_records" \
      -H "Authorization: Bearer ${CLOUDFLARE_API_TOKEN}" -H "Content-Type: application/json" \
      -d "$CF_PAYLOAD" >/dev/null
  done
else
  warn "Mailtrap did not return DNS records after enabling inbound — check manually:"
  echo "    $DOMAIN_RESPONSE"
fi

# --- 2. Folder + catch-all inbox --------------------------------------------
step "Creating Mailtrap inbox for '${DOMAIN}'"
FOLDER_RESPONSE=$(curl -sS -X POST "https://mailtrap.io/api/inbound/folders" \
  -H "Api-Token: ${MAILTRAP_API_TOKEN}" -H "Content-Type: application/json" \
  -d "$(jq -n --arg n "$NAME" '{name: $n}')")
FOLDER_ID=$(echo "$FOLDER_RESPONSE" | jq -r '.id // empty')

INBOX_ID=""
if [ -n "$FOLDER_ID" ]; then
  INBOX_RESPONSE=$(curl -sS -X POST "https://mailtrap.io/api/inbound/folders/${FOLDER_ID}/inboxes" \
    -H "Api-Token: ${MAILTRAP_API_TOKEN}" -H "Content-Type: application/json" \
    -d "$(jq -n --arg n "$NAME" --argjson d "$DOMAIN_ID" '{name: $n, domain_id: $d}')")
  INBOX_ID=$(echo "$INBOX_RESPONSE" | jq -r '.id // empty')
fi

if [ -z "$INBOX_ID" ]; then
  err "Could not create a Mailtrap inbox for '${DOMAIN}' — check FOLDER_RESPONSE/INBOX_RESPONSE manually:"
  echo "    folder: $FOLDER_RESPONSE"
  echo "    inbox:  ${INBOX_RESPONSE:-<not attempted>}"
  exit 1
fi

# --- 3. Webhook ---------------------------------------------------------------
step "Registering inbound webhook"
WEBHOOK_URL="https://${DOMAIN}/api/webhooks/email-inbound"
WEBHOOK_RESPONSE=$(curl -sS -X POST "https://mailtrap.io/api/webhooks" \
  -H "Api-Token: ${MAILTRAP_API_TOKEN}" -H "Content-Type: application/json" \
  -d "$(jq -n --arg url "$WEBHOOK_URL" --argjson inbox "$INBOX_ID" \
    '{webhook: {url: $url, webhook_type: "inbound_receiving", inbound_inbox_id: $inbox}}')")

MAILTRAP_INBOUND_WEBHOOK_SECRET=$(echo "$WEBHOOK_RESPONSE" | jq -r '.webhook.signing_secret // .signing_secret // empty')

if [ -z "$MAILTRAP_INBOUND_WEBHOOK_SECRET" ]; then
  err "Webhook created but no signing_secret in the response — nothing was written to .env. Response:"
  echo "    $WEBHOOK_RESPONSE"
  exit 1
fi

# --- 4. Write to the tenant's .env -------------------------------------------
set_env "MAILTRAP_API_TOKEN" "\"${MAILTRAP_API_TOKEN}\""
set_env "MAILTRAP_INBOUND_WEBHOOK_SECRET" "$MAILTRAP_INBOUND_WEBHOOK_SECRET"
chown www-data:www-data .env

echo
ok "Inbound email capture is live for '${NAME}' (${DOMAIN})."
warn "DNS just changed — Mailtrap's inbound_verified flag won't flip true until it propagates. Give it a few minutes before sending a real test email."
