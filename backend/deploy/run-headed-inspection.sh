#!/usr/bin/env bash
# Optional diagnostic. Never enables the application or changes GitHub auth.
set -Eeuo pipefail
[[ ${EUID} -eq 0 ]] || { echo 'Run with sudo.' >&2; exit 1; }
HERE=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
export PLAYWRIGHT_BROWSERS_PATH=/opt/odtumist-browsers
/opt/odtumist-runtime/node_modules/.bin/playwright install --no-shell --no-remove chromium
BINARY=$(/usr/local/bin/node --input-type=module -e 'import { chromium } from "/opt/odtumist-runtime/node_modules/playwright/index.mjs"; console.log(chromium.executablePath())')
[[ -x "$BINARY" && "$BINARY" == /opt/odtumist-browsers/* ]]
cat >/etc/apparmor.d/odtumist-full-chromium <<EOF
abi <abi/4.0>,
include <tunables/global>
profile odtumist-full-chromium ${BINARY} flags=(unconfined) {
  userns,
}
EOF
apparmor_parser -r /etc/apparmor.d/odtumist-full-chromium
install -d -o bursmetre -g bursmetre -m 0750 /var/lib/odtumist-bursmetre/diagnostics
install -d -o bursmetre -g bursmetre -m 0700 /var/lib/odtumist-bursmetre/diagnostics/browser-config /var/lib/odtumist-bursmetre/diagnostics/browser-cache
install -m 0644 "$HERE/inspect-givelively-headed.mjs" /opt/odtumist-runtime/inspect-givelively-headed.mjs
systemd-run --unit=odtumist-headed-inspection --collect --wait --pipe \
    -p User=bursmetre -p Group=bursmetre -p NoNewPrivileges=true \
    -p PrivateTmp=true -p ProtectSystem=strict -p ProtectHome=true \
    -p ReadWritePaths=/var/lib/odtumist-bursmetre/diagnostics \
    -p MemoryMax=1G -p CPUQuota=75% -p TasksMax=256 -p RuntimeMaxSec=120 \
    --setenv=PLAYWRIGHT_BROWSERS_PATH=/opt/odtumist-browsers \
    --setenv=XDG_CONFIG_HOME=/var/lib/odtumist-bursmetre/diagnostics/browser-config \
    --setenv=XDG_CACHE_HOME=/var/lib/odtumist-bursmetre/diagnostics/browser-cache \
    /usr/bin/xvfb-run -a -s '-screen 0 1280x900x24' \
    /usr/local/bin/node /opt/odtumist-runtime/inspect-givelively-headed.mjs
