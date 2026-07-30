#!/bin/bash
# ONE-TIME, server-wide setup: installs and configures Postfix as a
# catch-all inbound relay for *.cubrel.com, forwarding every accepted
# email to the owning tenant's own webhook via deploy/cubrel-inbound-relay.sh.
# Not per-tenant — new tenants need zero Postfix changes, only the
# one-time wildcard MX record (printed at the end) needs to exist once.
#
# Safe to re-run: config blocks are only appended if not already present,
# and the relay secret is only generated once (kept if it already exists).
#
# Usage: setup-postfix.sh
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

if [ "$(id -u)" -ne 0 ]; then
  err "Must run as root."
  exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# --- 1. Install Postfix non-interactively ------------------------------
if ! command -v postconf >/dev/null 2>&1; then
  step "Installing Postfix"
  # Preseed debconf so apt doesn't stop for the interactive "General type
  # of mail configuration" prompt. "Internet Site" is correct here — this
  # box both sends (existing MAIL_* config) and now receives.
  debconf-set-selections <<< "postfix postfix/main_mailer_type string 'Internet Site'"
  debconf-set-selections <<< "postfix postfix/mailname string cubrel.com"
  DEBIAN_FRONTEND=noninteractive apt-get update
  DEBIAN_FRONTEND=noninteractive apt-get install -y postfix
else
  step "Postfix already installed"
fi

# PCRE lookup table support (used by accepted_domains.pcre /
# accepted_recipients.pcre below) is a separate package on Debian/Ubuntu,
# not bundled with the base postfix package. Without it, every RCPT TO
# fails with "451 4.3.0 Temporary lookup failure" — a config-looks-fine,
# fails-at-runtime trap.
if ! postconf -m 2>/dev/null | grep -q '^pcre$'; then
  step "Installing postfix-pcre (PCRE lookup table support)"
  DEBIAN_FRONTEND=noninteractive apt-get install -y postfix-pcre
else
  step "postfix-pcre already installed"
fi

# --- 2. Unprivileged relay user ------------------------------------------
if ! id cubrelrelay >/dev/null 2>&1; then
  step "Creating cubrelrelay system user"
  useradd --system --no-create-home --shell /usr/sbin/nologin cubrelrelay
else
  step "cubrelrelay user already exists"
fi

# --- 3. pcre maps + relay script ------------------------------------------
step "Installing pcre maps and relay script"
mkdir -p /etc/postfix/cubrel
cp "$SCRIPT_DIR/postfix/accepted_domains.pcre" /etc/postfix/cubrel/accepted_domains.pcre
cp "$SCRIPT_DIR/postfix/accepted_recipients.pcre" /etc/postfix/cubrel/accepted_recipients.pcre

cp "$SCRIPT_DIR/cubrel-inbound-relay.sh" /usr/local/bin/cubrel-inbound-relay.sh
chmod 755 /usr/local/bin/cubrel-inbound-relay.sh
chown root:root /usr/local/bin/cubrel-inbound-relay.sh

# --- 4. Shared relay secret -------------------------------------------------
mkdir -p /etc/cubrel
SECRET_FILE="/etc/cubrel/inbound-relay-secret"
if [ ! -f "$SECRET_FILE" ]; then
  step "Generating shared relay secret"
  openssl rand -hex 32 > "$SECRET_FILE"
else
  step "Relay secret already exists, keeping it"
fi
chown cubrelrelay:cubrelrelay "$SECRET_FILE"
chmod 400 "$SECRET_FILE"
RELAY_SECRET=$(cat "$SECRET_FILE")

# --- 5. Config blocks (idempotent append) -----------------------------------
step "Configuring main.cf"
if ! grep -q "Cubrel inbound email capture" /etc/postfix/main.cf; then
  cat "$SCRIPT_DIR/postfix/main.cf.append" >> /etc/postfix/main.cf
else
  warn "main.cf already has the Cubrel block — not appending again."
fi

step "Configuring master.cf"
if ! grep -q "^cubrelrelay " /etc/postfix/master.cf; then
  cat "$SCRIPT_DIR/postfix/master.cf.append" >> /etc/postfix/master.cf
else
  warn "master.cf already has the cubrelrelay transport — not appending again."
fi

# --- 6. Reload -----------------------------------------------------------------
step "Checking Postfix config"
postfix check
systemctl enable --now postfix
systemctl reload postfix

echo
ok "Postfix inbound relay is configured."
echo
warn "Two things still need doing manually, once, server-wide (not per-tenant):"
echo "    1. Add a wildcard MX record in Cloudflare for the cubrel.com zone:"
echo "         *.cubrel.com.   MX   10   <this server's hostname, e.g. mail.cubrel.com>"
echo "       ...plus an A record for that mail hostname pointing at this server's IP."
echo "       This covers every current AND future tenant — no per-tenant DNS work again."
echo "    2. Add this shared secret to deploy/provision.env so provision-tenant.sh"
echo "       templates it into every new tenant's .env as INBOUND_RELAY_SECRET:"
echo
echo "         INBOUND_RELAY_SECRET=\"${RELAY_SECRET}\""
echo
warn "Existing tenants provisioned before this script ran need INBOUND_RELAY_SECRET added to their .env by hand (same value above), then 'php artisan config:clear' in each."
