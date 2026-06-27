#!/usr/bin/env bash
# Post-deploy production optimization (Hostinger). Safe to re-run.
set -euo pipefail

SSH_HOST="${QIEC_SSH_HOST:-hostinger-qiec}"
REMOTE_APP="${QIEC_REMOTE_APP:-domains/training.qiec.sa/public_html}"

echo "=== Optimizing production on ${SSH_HOST} ==="

ssh "${SSH_HOST}" "
  set -e
  cd ${REMOTE_APP}

  echo 'Clear stale caches...'
  rm -f bootstrap/cache/config.php 2>/dev/null || true
  rm -f bootstrap/cache/routes*.php 2>/dev/null || true
  rm -rf storage/framework/cache/data/* 2>/dev/null || true

  echo 'Rebuild Laravel caches (skip if ionCube blocks CLI)...'
  php artisan config:cache 2>/dev/null || echo 'config:cache skipped'
  php artisan view:cache 2>/dev/null || echo 'view:cache skipped'
  php artisan route:cache 2>/dev/null || echo 'route:cache skipped'

  echo 'Restart PHP workers...'
  pkill -u \$(whoami) lsphp 2>/dev/null || true
  sleep 2

  echo 'Branch:' \$(git branch --show-current)
  echo 'Commit:' \$(git log -1 --oneline)
  echo 'PHP workers:' \$(ps aux 2>/dev/null | grep lsphp | grep -v grep | wc -l)
"

echo "Done."
