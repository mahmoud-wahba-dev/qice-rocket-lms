#!/usr/bin/env bash
# Deploy a specific git branch to Hostinger (read-only git pull on server).
# Does NOT commit locally. Build assets first if you changed CSS/JS.
#
# Usage:
#   ./scripts/deploy-to-hostinger.sh feat/performance-homepage
#   ./scripts/deploy-to-hostinger.sh master          # back to production default
#
# Env overrides:
#   QIEC_SSH_HOST=hostinger-qiec
#   QIEC_REMOTE_APP=domains/training.qiec.sa/public_html

set -euo pipefail

BRANCH="${1:-}"
if [[ -z "${BRANCH}" ]]; then
  echo "Usage: $0 <branch-name>"
  echo "Example: $0 feat/performance-homepage"
  exit 1
fi

SSH_HOST="${QIEC_SSH_HOST:-hostinger-qiec}"
REMOTE_APP="${QIEC_REMOTE_APP:-domains/training.qiec.sa/public_html}"

echo "=== Deploy branch '${BRANCH}' to Hostinger ==="
echo "SSH:    ${SSH_HOST}"
echo "Path:   ${REMOTE_APP}"
echo ""

read -r -p "Build landing assets first? [y/N] " BUILD_ASSETS
if [[ "${BUILD_ASSETS}" =~ ^[Yy]$ ]]; then
  npm run build:landing
fi

echo ""
echo "Pulling on server..."
ssh "${SSH_HOST}" "
  set -e
  cd ${REMOTE_APP}
  git fetch origin
  git checkout ${BRANCH}
  git pull origin ${BRANCH}
  echo 'Deployed commit:'
  git log -1 --oneline
  echo 'Branch:'
  git branch --show-current
  rm -f bootstrap/cache/config.php
"

echo ""
echo "Done. Test: https://training.qiec.sa/"
echo "Rollback to master: $0 master"
