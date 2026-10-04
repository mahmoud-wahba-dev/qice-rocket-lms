#!/usr/bin/env bash
# Re-apply large upload limits on Hostinger VPS for training.qiec.sa
# Run as root: bash scripts/hostinger-raise-upload-limits.sh
set -euo pipefail

SITE_CONF="/etc/nginx/sites-enabled/training.qiec.sa.conf"
NGINX_CONF="/etc/nginx/nginx.conf"
BODY_SIZE="2048M"
UPLOAD="2048M"
POST="2048M"
MEM="1024M"
TIME="3600"

if [[ ! -f "$SITE_CONF" ]]; then
  echo "Missing $SITE_CONF"
  exit 1
fi

ts=$(date +%Y%m%d%H%M%S)
cp -a "$NGINX_CONF" "${NGINX_CONF}.bak-upload-${ts}"
cp -a "$SITE_CONF" "${SITE_CONF}.bak-upload-${ts}"

# Global nginx body size
if grep -q "client_max_body_size" "$NGINX_CONF"; then
  sed -i "s/client_max_body_size[[:space:]][^;]*;/client_max_body_size ${BODY_SIZE};/" "$NGINX_CONF"
else
  sed -i "/http {/a\\    client_max_body_size ${BODY_SIZE};" "$NGINX_CONF"
fi

python3 - <<PY
from pathlib import Path
import re
p = Path("${SITE_CONF}")
text = p.read_text()
if "client_max_body_size" not in text:
    text = text.replace("server_name training.qiec.sa;", "server_name training.qiec.sa;\\n  client_max_body_size ${BODY_SIZE};", 2)
else:
    text = re.sub(r"client_max_body_size\\s+[^;]+;", "client_max_body_size ${BODY_SIZE};", text)

replacements = {
    r"memory_limit=\\d+M;": "memory_limit=${MEM};",
    r"max_execution_time=\\d+;": "max_execution_time=${TIME};",
    r"max_input_time=\\d+;": "max_input_time=${TIME};",
    r"post_max_size=\\d+M;": "post_max_size=${POST};",
    r"upload_max_filesize=\\d+M;": "upload_max_filesize=${UPLOAD};",
}
for pat, rep in replacements.items():
    text = re.sub(pat, rep, text)
p.write_text(text)
print("Updated", p)
PY

nginx -t
systemctl reload nginx
systemctl reload php8.4-fpm 2>/dev/null || service php8.4-fpm reload || true
echo "Upload limits applied: nginx body=${BODY_SIZE}, PHP upload=${UPLOAD}, post=${POST}"
