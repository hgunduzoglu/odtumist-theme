#!/usr/bin/env bash
# Run only after the owner has connected GitHub and cloned the repository.
set -Eeuo pipefail
[[ ${EUID} -eq 0 ]] || { echo 'Run with sudo.' >&2; exit 1; }
HERE=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
APP=/srv/odtumist/app/backend
[[ -f "$APP/package-lock.json" && -f "$APP/src/server.js" ]] || { echo "Clone the project to /srv/odtumist/app first." >&2; exit 1; }
[[ -f /etc/letsencrypt/live/odtumist.edutrip.ai/fullchain.pem ]] || { echo 'Run prepare-web.sh first.' >&2; exit 1; }
cd "$APP"
systemctl stop odtumist-collector.timer
systemctl stop odtumist-collector.service
runuser -u odtumist -- /usr/local/bin/npm ci --omit=dev --no-fund --no-audit
runuser -u odtumist -- /usr/local/bin/node --test
install -m 0644 "$HERE/odtumist-api.service" "$HERE/odtumist-collector.service" "$HERE/odtumist-collector.timer" /etc/systemd/system/
install -m 0644 "$HERE/nginx-app.conf" /etc/nginx/sites-available/odtumist
nginx -t
systemctl daemon-reload
systemctl enable --now odtumist-api.service
systemctl restart odtumist-api.service
systemctl enable --now odtumist-collector.timer
systemctl reload nginx
echo 'API and collector timer enabled. Inspect /readyz before connecting WordPress.'
echo 'Collect once: systemctl start odtumist-collector.service'
