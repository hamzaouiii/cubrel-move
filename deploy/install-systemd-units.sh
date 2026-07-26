#!/bin/bash
# One-time setup on the VPS: installs the templated systemd units that
# provision-tenant.sh and deploy.sh rely on. Re-run safely after editing
# the unit files in deploy/systemd/.
set -euo pipefail

if [ "$(id -u)" -ne 0 ]; then
  echo "Must run as root." >&2
  exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

cp "$SCRIPT_DIR/systemd/cubrel-queue@.service" /etc/systemd/system/cubrel-queue@.service
cp "$SCRIPT_DIR/systemd/cubrel-reverb@.service" /etc/systemd/system/cubrel-reverb@.service

systemctl daemon-reload

echo "Installed cubrel-queue@.service and cubrel-reverb@.service."
echo "Start a tenant's processes with: systemctl enable --now cubrel-queue@<name> cubrel-reverb@<name>"
