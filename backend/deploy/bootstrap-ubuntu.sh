#!/usr/bin/env bash
# Initial host setup only. Does not clone a repository or start the application.
set -Eeuo pipefail
umask 022
[[ ${EUID} -eq 0 ]] || { echo 'Run as root or with sudo.' >&2; exit 1; }
. /etc/os-release
[[ ${ID} == ubuntu ]] || { echo 'Ubuntu is required.' >&2; exit 1; }
[[ $(uname -m) == x86_64 ]] || { echo 'This bootstrap currently supports x86_64.' >&2; exit 1; }
LOG=/var/log/odtumist-bootstrap.log
trap 'echo "Bootstrap failed; see /var/log/odtumist-bootstrap.log" >&2; tail -25 "$LOG" >&2' ERR
export DEBIAN_FRONTEND=noninteractive
echo 'Installing host packages...'
apt-get update >>"$LOG" 2>&1
apt-get -o DPkg::Lock::Timeout=300 install -y --no-install-recommends ca-certificates curl git gh nginx certbot python3-certbot-nginx xz-utils >>"$LOG" 2>&1

echo 'Installing checksum-verified Node.js 24 LTS...'
install -d -m 0755 /var/cache/odtumist /opt/odtumist-runtime /opt/odtumist-browsers
cd /var/cache/odtumist
curl -fsS --retry 3 https://nodejs.org/dist/latest-v24.x/SHASUMS256.txt -o SHASUMS256.txt
NODE_ARCHIVE=$(awk '$2 ~ /^node-v24\.[0-9]+\.[0-9]+-linux-x64\.tar\.xz$/ {print $2}' SHASUMS256.txt)
[[ ${NODE_ARCHIVE} =~ ^node-v24\.[0-9]+\.[0-9]+-linux-x64\.tar\.xz$ ]]
NODE_DIR=${NODE_ARCHIVE%.tar.xz}
if [[ ! -x /opt/${NODE_DIR}/bin/node ]]; then
    curl -fsS --retry 3 "https://nodejs.org/dist/latest-v24.x/${NODE_ARCHIVE}" -o "$NODE_ARCHIVE"
    awk -v filename="$NODE_ARCHIVE" '$2 == filename' SHASUMS256.txt | sha256sum --check --status
    tar -xJf "$NODE_ARCHIVE" -C /opt
fi
ln -sfn "/opt/${NODE_DIR}" /opt/node-lts
for binary in node npm npx; do ln -sfn "/opt/node-lts/bin/${binary}" "/usr/local/bin/${binary}"; done
export PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
node --version
npm --version

echo 'Creating separate deployment and runtime accounts...'
id odtumist >/dev/null 2>&1 || useradd --create-home --shell /bin/bash odtumist
id bursmetre >/dev/null 2>&1 || useradd --system --home-dir /var/lib/odtumist-bursmetre --shell /usr/sbin/nologin bursmetre
install -d -o odtumist -g odtumist -m 0755 /srv/odtumist
install -d -o bursmetre -g bursmetre -m 0750 /var/lib/odtumist-bursmetre
install -d -m 0755 /var/www/odtumist-acme/.well-known/acme-challenge

echo 'Installing pinned Playwright and Chromium headless shell dependencies...'
npm install --prefix /opt/odtumist-runtime --save-exact --omit=dev --no-fund --no-audit playwright@1.64.1 >>"$LOG" 2>&1
PLAYWRIGHT_BROWSERS_PATH=/opt/odtumist-browsers /opt/odtumist-runtime/node_modules/.bin/playwright install --with-deps --only-shell chromium >>"$LOG" 2>&1
chmod -R a+rX /opt/odtumist-browsers

# Ubuntu restricts unprivileged user namespaces. Allow only this installed
# Chromium executable to create its browser sandbox, without changing sysctls.
if command -v apparmor_parser >/dev/null 2>&1 && [[ -d /sys/kernel/security/apparmor ]]; then
    SHELL_BINARY=$(find /opt/odtumist-browsers -type f \( -name headless_shell -o -name chrome-headless-shell \) -print -quit)
    [[ -n ${SHELL_BINARY} ]]
    cat >/etc/apparmor.d/odtumist-chromium <<EOF
abi <abi/4.0>,
include <tunables/global>
profile odtumist-chromium ${SHELL_BINARY} flags=(unconfined) {
  userns,
}
EOF
    apparmor_parser -r /etc/apparmor.d/odtumist-chromium >>"$LOG" 2>&1
fi

if [[ ! -e /etc/odtumist-bursmetre.env ]]; then
    cat >/etc/odtumist-bursmetre.env <<'EOF'
NODE_ENV=production
HOST=127.0.0.1
PORT=3100
DATA_DIR=/var/lib/odtumist-bursmetre
PLAYWRIGHT_BROWSERS_PATH=/opt/odtumist-browsers
SOURCE_URL=https://secure.givelively.org/teams/bridge-to-turkiye/2026-btf-marathon-steps-that-shape-futures/odtumist
TCMB_URL=https://www.tcmb.gov.tr/kurlar/today.xml
SOURCE_TIMEOUT_MS=60000
SOURCE_STALE_MINUTES=90
FX_STALE_HOURS=96
ANNUAL_SCHOLARSHIP_TRY=49500
EOF
    chown root:bursmetre /etc/odtumist-bursmetre.env
    chmod 0640 /etc/odtumist-bursmetre.env
fi
systemctl enable --now nginx >>"$LOG" 2>&1
echo 'Host packages, accounts, browser and runtime are ready. Application is not deployed.'
