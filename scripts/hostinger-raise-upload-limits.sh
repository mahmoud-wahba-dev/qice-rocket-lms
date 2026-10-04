#!/usr/bin/env bash
# Raise web-server + PHP body limits for large course videos on Hostinger.
# Run as root on the VPS. Safe to re-run.
set -euo pipefail

SIZE="${1:-102400M}"
PHP_VALUE_SIZE="${SIZE}"

echo "==> Target body/upload size: ${SIZE}"

# CloudPanel / nginx site configs
for conf in /etc/nginx/sites-enabled/*training* /etc/nginx/conf.d/*.conf /etc/nginx/nginx.conf; do
  [[ -f "$conf" ]] || continue
  if grep -q 'client_max_body_size' "$conf"; then
    sed -i -E "s/client_max_body_size[[:space:]]+[^;]+;/client_max_body_size ${SIZE};/g" "$conf"
    echo "updated client_max_body_size in $conf"
  else
    # Insert inside http{} or server{} best-effort for nginx.conf
    if [[ "$conf" == *nginx.conf ]]; then
      sed -i -E "s/(http[[:space:]]*\{)/\\1\n    client_max_body_size ${SIZE};/" "$conf" || true
      echo "inserted client_max_body_size in $conf (if http block matched)"
    fi
  fi
done

# LiteSpeed / CloudPanel PHP_VALUE often in vhost
for conf in /etc/nginx/sites-enabled/* /usr/local/lsws/conf/vhosts/*/vhconf.conf; do
  [[ -f "$conf" ]] || continue
  if grep -q 'upload_max_filesize' "$conf"; then
    sed -i -E "s/upload_max_filesize=[^ \\\"]+/upload_max_filesize=${PHP_VALUE_SIZE}/g" "$conf"
    sed -i -E "s/post_max_size=[^ \\\"]+/post_max_size=${PHP_VALUE_SIZE}/g" "$conf"
    echo "updated PHP upload values in $conf"
  fi
done

if command -v nginx >/dev/null 2>&1; then
  nginx -t && systemctl reload nginx || service nginx reload || true
  echo "nginx reloaded"
fi

echo "Done. Also deploy public/.user.ini (102400M) from the repo."
echo "App validation no longer caps curriculum video size."
