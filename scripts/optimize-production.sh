#!/usr/bin/env bash
# Post-deploy: clear stale caches and restart PHP (Hostinger shared hosting).
set -euo pipefail

SSH_HOST="${QIEC_SSH_HOST:-hostinger-qiec}"
REMOTE_APP="${QIEC_REMOTE_APP:-domains/training.qiec.sa/public_html}"

ssh "${SSH_HOST}" "
  set -e
  cd ${REMOTE_APP}
  PHP_BIN=\$(bash scripts/hostinger-php.sh 2>/dev/null || echo /opt/alt/php82/usr/bin/php)
  echo \"Using PHP: \${PHP_BIN}\"
  \${PHP_BIN} -v 2>/dev/null | head -1 || true
  if command -v node >/dev/null 2>&1 && [ -f scripts/optimize-landing-images.js ]; then
    node scripts/optimize-landing-images.js 2>/dev/null || echo 'Image optimization skipped'
  fi
  if [ -f vendor/unisharp/laravel-filemanager/public/js/stand-alone-button.js ]; then
    mkdir -p public/vendor/laravel-filemanager
    cp -r vendor/unisharp/laravel-filemanager/public/* public/vendor/laravel-filemanager/
    echo 'Synced laravel-filemanager public assets'
  fi
  if [ -f vendor/autoload.php ] && \${PHP_BIN} artisan --version >/dev/null 2>&1; then
    \${PHP_BIN} artisan optimize:clear
    echo 'Laravel caches cleared via artisan'
  else
    echo 'WARNING: artisan unavailable; clearing caches manually'
    rm -f bootstrap/cache/config.php bootstrap/cache/routes*.php 2>/dev/null || true
    rm -rf storage/framework/cache/data/* 2>/dev/null || true
    rm -rf storage/framework/views/* 2>/dev/null || true
  fi
  pkill -u \$(whoami) lsphp 2>/dev/null || true
  sleep 2
  echo Branch: \$(git branch --show-current)
  echo Commit: \$(git log -1 --oneline)
"
