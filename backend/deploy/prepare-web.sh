#!/usr/bin/env bash
# Prepare this domain and TLS, while leaving the application undeployed.
set -Eeuo pipefail
[[ ${EUID} -eq 0 ]] || { echo 'Run with sudo.' >&2; exit 1; }
HERE=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
DOMAIN=odtumist.edutrip.ai
install -d -m 0755 /var/www/odtumist-acme/.well-known/acme-challenge
cat >/etc/nginx/conf.d/odtumist-rate-limit.conf <<'EOF'
limit_req_zone $binary_remote_addr zone=odtumist_api:1m rate=5r/s;
EOF
if [[ ! -f /etc/letsencrypt/live/${DOMAIN}/fullchain.pem ]]; then
    cat >/etc/nginx/sites-available/odtumist <<'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name odtumist.edutrip.ai;
    location ^~ /.well-known/acme-challenge/ { root /var/www/odtumist-acme; }
    location = /bootstrap-healthz { default_type application/json; return 200 '{"host":"ready","application":"not_deployed"}\n'; }
    location / { default_type application/json; return 503 '{"status":"not_deployed","message":"Backend deployment is pending."}\n'; }
}
EOF
    ln -sfn /etc/nginx/sites-available/odtumist /etc/nginx/sites-enabled/odtumist
    nginx -t
    systemctl reload nginx
    certbot certonly --webroot -w /var/www/odtumist-acme -d "$DOMAIN" --non-interactive --agree-tos --register-unsafely-without-email
fi
cat >/etc/nginx/sites-available/odtumist <<'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name odtumist.edutrip.ai;
    location ^~ /.well-known/acme-challenge/ { root /var/www/odtumist-acme; }
    location / { return 301 https://$host$request_uri; }
}
server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name odtumist.edutrip.ai;
    ssl_certificate /etc/letsencrypt/live/odtumist.edutrip.ai/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/odtumist.edutrip.ai/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    add_header X-Robots-Tag "noindex, nofollow" always;
    location = /bootstrap-healthz { default_type application/json; return 200 '{"host":"ready","application":"not_deployed"}\n'; }
    location / { default_type application/json; return 503 '{"status":"not_deployed","message":"Backend deployment is pending."}\n'; }
}
EOF
ln -sfn /etc/nginx/sites-available/odtumist /etc/nginx/sites-enabled/odtumist
install -m 0644 "$HERE/odtumist-api.service" "$HERE/odtumist-collector.service" "$HERE/odtumist-collector.timer" /etc/systemd/system/
install -d -m 0755 /etc/letsencrypt/renewal-hooks/deploy
cat >/etc/letsencrypt/renewal-hooks/deploy/odtumist-reload-nginx <<'EOF'
#!/bin/sh
/usr/sbin/nginx -t && /usr/bin/systemctl reload nginx
EOF
chmod 0755 /etc/letsencrypt/renewal-hooks/deploy/odtumist-reload-nginx
nginx -t
systemctl reload nginx
systemctl daemon-reload
echo 'HTTPS and systemd unit files are ready. Application services remain disabled.'
