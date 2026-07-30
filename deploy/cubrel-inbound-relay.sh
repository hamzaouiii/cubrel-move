#!/bin/bash
# Postfix pipes here for every accepted *.cubrel.com recipient (see
# deploy/postfix/master.cf.append). Reads the raw RFC822 message on
# stdin and forwards it, plus the original envelope recipient, to that
# tenant's own /api/webhooks/email-inbound endpoint — which does the
# actual MIME parsing and tenant/user matching. This script is
# deliberately a thin relay, not a parser.
#
# Installed to /usr/local/bin/cubrel-inbound-relay.sh by
# deploy/setup-postfix.sh. Not meant to be run manually.
set -euo pipefail

RECIPIENT="${1:-}"
if [ -z "$RECIPIENT" ]; then
  echo "cubrel-inbound-relay: missing recipient argument" >&2
  exit 1
fi

DOMAIN="${RECIPIENT#*@}"

SECRET_FILE="/etc/cubrel/inbound-relay-secret"
if [ ! -f "$SECRET_FILE" ]; then
  echo "cubrel-inbound-relay: missing $SECRET_FILE" >&2
  exit 1
fi
SECRET=$(cat "$SECRET_FILE")

# Buffered to a temp file (not streamed straight into curl) so curl sends
# a real Content-Length instead of chunked transfer encoding.
TMP=$(mktemp)
trap 'rm -f "$TMP"' EXIT
cat > "$TMP"

# --fail: a 4xx/5xx from the tenant app must make curl (and therefore
# this whole pipe) exit non-zero, so Postfix treats it as a temporary
# delivery failure and retries per its normal queue policy — silently
# swallowing a failed relay would just drop the email.
# --max-time: a hung tenant app must not hang Postfix's delivery queue
# indefinitely.
curl -sS --fail --max-time 30 -X POST "https://${DOMAIN}/api/webhooks/email-inbound" \
  -H "X-Cubrel-Relay-Secret: ${SECRET}" \
  -H "X-Cubrel-Relay-Recipient: ${RECIPIENT}" \
  -H "Content-Type: message/rfc822" \
  --data-binary @"$TMP"
