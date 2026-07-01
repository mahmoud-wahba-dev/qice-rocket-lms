#!/usr/bin/env bash
# Resolve the PHP CLI binary on Hostinger for Laravel artisan.
# Default SSH `php` is often PHP 8.1 without ionCube; this app needs PHP 8.2+ with ionCube Loader.
set -euo pipefail

if [ -n "${QIEC_PHP_BIN:-}" ] && [ -x "${QIEC_PHP_BIN}" ]; then
    echo "${QIEC_PHP_BIN}"
    exit 0
fi

for candidate in \
    /opt/alt/php82/usr/bin/php \
    /opt/alt/php83/usr/bin/php \
    /opt/alt/php81/usr/bin/php \
    php; do
    if [ -x "${candidate}" ] || command -v "${candidate}" >/dev/null 2>&1; then
        if "${candidate}" -m 2>/dev/null | grep -qi 'ionCube Loader'; then
            echo "${candidate}"
            exit 0
        fi
    fi
done

# Fallback: first PHP that can run artisan (may still fail on ionCube-encoded code).
for candidate in /opt/alt/php82/usr/bin/php /opt/alt/php83/usr/bin/php php; do
    if [ -x "${candidate}" ] || command -v "${candidate}" >/dev/null 2>&1; then
        echo "${candidate}"
        exit 0
    fi
done

echo "php"
