# Deploy Script Templates

These templates are safe to commit. Copy them to the project root as `deploy.bat` and `deploy.ps1` (both are gitignored).

**Customize before use:**

| Placeholder | QIEC value |
|-------------|------------|
| `SSH_HOST_ALIAS` | `hostinger-qiec` |
| `REMOTE_PATH` | `domains/training.qiec.sa/public_html` |
| `GIT_BRANCH` | `master` |
| `BUILD_SCRIPT` | `build:landing` |

**QIEC Hostinger SSH (confirmed):**

| Field | Value |
|-------|-------|
| IP | `82.197.83.145` |
| Port | `65002` |
| User | `u873288737` |

---

## deploy.bat

```bat
@echo off
echo ========================================================
echo Starting deployment workflow...
echo ========================================================

echo Step 1: Compiling landing assets locally...
call npm run build:landing
if %errorlevel% neq 0 (
    echo Asset compilation failed. Aborting deployment.
    pause
    exit /b %errorlevel%
)

echo Step 2: Pushing changes to GitHub...
git add .
git diff --cached --quiet
if %errorlevel% equ 0 (
    echo No staged changes to commit. Continuing to remote deploy...
) else (
    git commit -m "auto-deploy: updates"
    if %errorlevel% neq 0 (
        echo Git commit failed. Aborting deployment.
        pause
        exit /b %errorlevel%
    )
)

git push origin master
if %errorlevel% neq 0 (
    echo Git push failed. Aborting deployment.
    pause
    exit /b %errorlevel%
)

echo Step 3: Deploying on Hostinger...
ssh hostinger-qiec "cd domains/training.qiec.sa/public_html && git pull origin master && php artisan config:clear && php artisan cache:clear && php artisan view:clear"

echo ========================================================
echo Deployment completed.
echo ========================================================
pause
```

---

## deploy.ps1

```powershell
Write-Host "Starting deployment workflow..." -ForegroundColor Yellow

Write-Host "Step 1: Compiling landing assets via Vite..." -ForegroundColor Cyan
npm run build:landing

if ($LASTEXITCODE -ne 0) {
    Write-Host "Asset compilation failed. Aborting deployment." -ForegroundColor Red
    exit 1
}

Write-Host "Step 2: Staging, committing, and pushing to GitHub..." -ForegroundColor Cyan
git add .

$staged = git diff --cached --quiet
if ($LASTEXITCODE -eq 0) {
    Write-Host "No staged changes to commit. Continuing to remote deploy..." -ForegroundColor Yellow
} else {
    git commit -m "auto-deploy: client-side changes and assets"
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Git commit failed. Aborting deployment." -ForegroundColor Red
        exit 1
    }
}

git push origin master

if ($LASTEXITCODE -ne 0) {
    Write-Host "Git push failed. Aborting deployment." -ForegroundColor Red
    exit 1
}

Write-Host "Step 3: Connecting to Hostinger via SSH..." -ForegroundColor Cyan
ssh hostinger-qiec "cd domains/training.qiec.sa/public_html && git pull origin master && php artisan config:clear && php artisan cache:clear && php artisan view:clear"

if ($LASTEXITCODE -ne 0) {
    Write-Host "Remote commands finished with warnings or errors." -ForegroundColor Yellow
    exit 1
}

Write-Host "Deployment completed." -ForegroundColor Green
```

---

## package.json script

Ensure `package.json` includes:

```json
{
    "scripts": {
        "dev:landing": "vite",
        "build:landing": "vite build",
        "deploy": "deploy.bat"
    }
}
```

---

## New client adaptation

When starting a new RocketLMS client:

1. Change `SSH_HOST_ALIAS` in both scripts (or reuse if same Hostinger account)
2. Change `REMOTE_PATH` to `domains/<client-domain>/public_html`
3. Change git remote if using a different GitHub repo
4. Keep `build:landing` unless you rename the npm script

See [DEPLOYMENT.md](DEPLOYMENT.md) for full Hostinger setup steps.
