#!/usr/bin/env bash
# Read-only production health check for training.qiec.sa
# Does not modify the server or application.

set -euo pipefail

SSH_HOST="${QIEC_SSH_HOST:-hostinger-qiec}"
SITE_URL="${QIEC_SITE_URL:-https://training.qiec.sa}"
REMOTE_APP="${QIEC_REMOTE_APP:-domains/training.qiec.sa/public_html}"

echo "=== QIEC production health check ==="
echo "Time: $(date -u '+%Y-%m-%d %H:%M:%S UTC')"
echo "SSH:  ${SSH_HOST}"
echo "URL:  ${SITE_URL}"
echo ""

echo "--- Server (read-only) ---"
ssh -o ConnectTimeout=15 "${SSH_HOST}" "
  echo 'Load:'; uptime
  echo 'Log size:'; du -sh ${REMOTE_APP}/storage/logs 2>/dev/null || echo 'n/a'
  echo 'Git:'; cd ${REMOTE_APP} && git log -1 --oneline
  echo 'Debug:'; grep -E '^APP_DEBUG|^APP_ENV' .env 2>/dev/null || echo 'n/a'
"

echo ""
echo "--- HTTP timing (read-only) ---"
for path in "/" "/login"; do
  curl -s -o /dev/null -w "${SITE_URL}${path}  ttfb:%{time_starttransfer}s  total:%{time_total}s  http:%{http_code}\n" \
    "${SITE_URL}${path}"
done

echo ""
echo "Done. No changes were made on the server."
