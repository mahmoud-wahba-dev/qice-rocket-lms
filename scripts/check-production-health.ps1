# Read-only production health check for training.qiec.sa
# Does not modify the server or application.

$ErrorActionPreference = "Stop"

$SshHost = if ($env:QIEC_SSH_HOST) { $env:QIEC_SSH_HOST } else { "hostinger-qiec" }
$SiteUrl = if ($env:QIEC_SITE_URL) { $env:QIEC_SITE_URL } else { "https://training.qiec.sa" }
$RemoteApp = if ($env:QIEC_REMOTE_APP) { $env:QIEC_REMOTE_APP } else { "domains/training.qiec.sa/public_html" }

Write-Host "=== QIEC production health check ===" -ForegroundColor Cyan
Write-Host "Time: $((Get-Date).ToUniversalTime().ToString('yyyy-MM-dd HH:mm:ss')) UTC"
Write-Host "SSH:  $SshHost"
Write-Host "URL:  $SiteUrl"
Write-Host ""

Write-Host "--- Server (read-only) ---" -ForegroundColor Yellow
$remoteCmd = @"
echo 'Load:'; uptime
echo 'Log size:'; du -sh ${RemoteApp}/storage/logs 2>/dev/null || echo 'n/a'
echo 'Git:'; cd ${RemoteApp} && git log -1 --oneline
echo 'Debug:'; grep -E '^APP_DEBUG|^APP_ENV' .env 2>/dev/null || echo 'n/a'
"@
ssh -o ConnectTimeout=15 $SshHost $remoteCmd

Write-Host ""
Write-Host "--- HTTP timing (read-only) ---" -ForegroundColor Yellow
foreach ($path in @("/", "/login")) {
    $url = "$SiteUrl$path"
    $result = curl.exe -s -o NUL -w "%{time_starttransfer} %{time_total} %{http_code}" $url
    $parts = $result -split " "
    Write-Host "$url  ttfb:$($parts[0])s  total:$($parts[1])s  http:$($parts[2])"
}

Write-Host ""
Write-Host "Done. No changes were made on the server." -ForegroundColor Green
