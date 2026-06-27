# Deploy a specific git branch to Hostinger (git pull on server only).
# Usage: .\scripts\deploy-to-hostinger.ps1 feat/performance-homepage

param(
    [Parameter(Mandatory = $true)]
    [string]$Branch,

    [switch]$BuildAssets
)

$ErrorActionPreference = "Stop"

$SshHost = if ($env:QIEC_SSH_HOST) { $env:QIEC_SSH_HOST } else { "hostinger-qiec" }
$RemoteApp = if ($env:QIEC_REMOTE_APP) { $env:QIEC_REMOTE_APP } else { "domains/training.qiec.sa/public_html" }

Write-Host "=== Deploy branch '$Branch' to Hostinger ===" -ForegroundColor Cyan
Write-Host "SSH:  $SshHost"
Write-Host "Path: $RemoteApp"
Write-Host ""

if ($BuildAssets) {
    npm run build:landing
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
}

$remoteCmd = @"
set -e
cd ${RemoteApp}
git fetch origin
git checkout ${Branch}
git pull origin ${Branch}
echo 'Deployed commit:'
git log -1 --oneline
echo 'Branch:'
git branch --show-current
rm -f bootstrap/cache/config.php
"@

ssh $SshHost $remoteCmd

Write-Host ""
Write-Host "Done. Test: https://training.qiec.sa/" -ForegroundColor Green
Write-Host "Rollback to master: .\scripts\deploy-to-hostinger.ps1 -Branch master"
