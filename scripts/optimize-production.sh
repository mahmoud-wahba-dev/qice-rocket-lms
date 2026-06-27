#!/usr/bin/env bash
# Post-deploy: clear stale caches and restart PHP (Hostinger shared hosting).
set -euo pipefail

SSH_HOST="${QIEC_SSH_HOST:-hostinger-qiec}"
REMOTE_APP="${QIEC_REMOTE_APP:-domains/training.qiec.sa/public_html}"

ssh "${SSH_HOST}" "
  set -e
  cd ${REMOTE_APP}
  if command -v node >/dev/null 2>&1 && [ -f scripts/optimize-landing-images.js ]; then
    node scripts/optimize-landing-images.js 2>/dev/null || echo 'Image optimization skipped'
  fi
  rm -f bootstrap/cache/config.php bootstrap/cache/routes*.php 2>/dev/null || true
  rm -rf storage/framework/cache/data/* 2>/dev/null || true
  pkill -u \$(whoami) lsphp 2>/dev/null || true
  sleep 2
  echo Branch: \$(git branch --show-current)
  echo Commit: \$(git log -1 --oneline)
"
