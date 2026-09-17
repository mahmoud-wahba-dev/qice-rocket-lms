#!/usr/bin/env bash
# Post-deploy: clear caches, sync vendor public assets, strip public/hot (CloudPanel VPS).
set -euo pipefail

SSH_HOST="${QIEC_SSH_HOST:-hostinger-qiec}"
REMOTE_APP="${QIEC_REMOTE_APP:-/home/qiec-training/htdocs/training.qiec.sa}"

ssh "${SSH_HOST}" "
  set -e
  git config --global --add safe.directory ${REMOTE_APP} 2>/dev/null || true
  cd ${REMOTE_APP}

  # Never leave Vite hot file on production (forces [::1]:5173 and breaks CSS)
  rm -f public/hot

  if command -v php8.4 >/dev/null 2>&1; then
    PHP_BIN=php8.4
  elif command -v php >/dev/null 2>&1; then
    PHP_BIN=php
  else
    PHP_BIN=\$(bash scripts/hostinger-php.sh 2>/dev/null || echo php)
  fi
  echo \"Using PHP: \${PHP_BIN}\"
  \${PHP_BIN} -v 2>/dev/null | head -1 || true

  if [ -f vendor/unisharp/laravel-filemanager/public/js/stand-alone-button.js ]; then
    rm -rf public/vendor/laravel-filemanager
    mkdir -p public/vendor/laravel-filemanager
    cp -a vendor/unisharp/laravel-filemanager/public/. public/vendor/laravel-filemanager/
    echo 'Synced laravel-filemanager public assets'
  fi
  if [ -d vendor/stijnvanouplines/blade-country-flags/resources/svg ]; then
    rm -rf public/vendor/blade-country-flags
    mkdir -p public/vendor/blade-country-flags
    cp -a vendor/stijnvanouplines/blade-country-flags/resources/svg/. public/vendor/blade-country-flags/
    echo 'Synced blade-country-flags SVG assets'
  fi

  if [ -f vendor/autoload.php ] && \${PHP_BIN} artisan --version >/dev/null 2>&1; then
    \${PHP_BIN} artisan optimize:clear
    \${PHP_BIN} artisan storage:link 2>/dev/null || true
    echo 'Laravel caches cleared via artisan'
  else
    echo 'WARNING: artisan unavailable; clearing caches manually'
    rm -f bootstrap/cache/config.php bootstrap/cache/routes*.php 2>/dev/null || true
    rm -rf storage/framework/cache/data/* 2>/dev/null || true
    rm -rf storage/framework/views/* 2>/dev/null || true
  fi

  # Fix ownership for CloudPanel site user when deploying as root
  if id qiec-training >/dev/null 2>&1; then
    chown -R qiec-training:qiec-training public/build public/assets storage bootstrap/cache 2>/dev/null || true
  fi

  test -f public/build/manifest.json && echo 'build/manifest.json: OK' || echo 'WARNING: public/build/manifest.json missing'
  test ! -f public/hot && echo 'public/hot: absent (good)' || echo 'WARNING: public/hot still present'

  echo Branch: \$(git branch --show-current)
  echo Commit: \$(git log -1 --oneline)
"
