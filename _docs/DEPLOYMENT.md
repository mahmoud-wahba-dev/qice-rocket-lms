# QIEC — Hostinger Deployment Guide

**Production domain:** https://training.qiec.sa  
**Git remote:** `git@github.com:mahmoud-wahba-dev/qice-rocket-lms.git`  
**Deploy method:** Local scripts only (`npm run deploy` → build, git push, SSH pull, post-deploy optimize)

No GitHub Actions or auto-deploy — merging to `master` does not update the server by itself.

---

## Before You Deploy — Hostinger Verification Checklist

Complete this checklist once. Save the answers in a secure note (not in git).

### 1. SSH access (confirmed from hPanel)

| Field | Value |
|-------|-------|
| SSH status | **ACTIVE** |
| IP | `82.197.83.145` |
| Port | `65002` (not 22 — Hostinger uses custom port) |
| Username | `u873288737` |
| Domain path | `domains/training.qiec.sa/public_html` |

**Direct login command:**

```powershell
ssh -p 65002 u873288737@82.197.83.145
```

**Configured alias** (in `C:\Users\devma\.ssh\config`):

```
Host hostinger-qiec
    HostName 82.197.83.145
    User u873288737
    Port 65002
    IdentityFile ~/.ssh/id_ed25519
```

Then use: `ssh hostinger-qiec`

> **Note:** QIEC is on a **different server** than siematplus (`82.197.83.145` vs `153.92.220.73`). Do not reuse the `hostinger` alias from siematplus.

### 1b. Add SSH key to hPanel (required for passwordless deploy)

1. Copy your public key:

```powershell
Get-Content C:\Users\devma\.ssh\id_ed25519.pub
```

2. hPanel → **training.qiec.sa → SSH Access → أضف مفتاح SSH (Add SSH Key)**
3. Paste the key and save

Current public key fingerprint: `devma@MW` (ed25519)

### 2. Confirm document root path

1. Go to **Websites → training.qiec.sa → File Manager**
2. Open the site root folder
3. Confirm path — typically:

```
~/domains/training.qiec.sa/public_html
```

4. Check whether `public_html` is **empty**, has **existing Laravel files**, or already has a **`.git` folder**

| Question | Answer |
|----------|--------|
| Path to public_html | `domains/training.qiec.sa/public_html` |
| Repo already cloned? | Yes / No |
| `.env` exists on server? | Yes / No |

### 3. Same server as siematplus?

**No — different Hostinger server.**

| Project | IP | SSH user | Alias |
|---------|-----|----------|-------|
| siematplus | `153.92.220.73` | `u632024281` | `hostinger` |
| QIEC | `82.197.83.145` | `u873288737` | `hostinger-qiec` |

Both use port `65002`.

### 4. Database credentials (hPanel → Databases)

Record for server `.env` only — **never commit**:

| Key | Source |
|-----|--------|
| `DB_HOST` | Usually `localhost` on Hostinger |
| `DB_DATABASE` | e.g. `u873288737_markaz` |
| `DB_USERNAME` | e.g. `u873288737_user_markaz` |
| `DB_PASSWORD` | From hPanel MySQL panel |

### 5. Test SSH from your machine

After configuring SSH (see below):

```powershell
ssh hostinger-qiec "pwd && ls -la domains/training.qiec.sa/public_html"
```

Expected: prints home directory and lists files in `public_html`.

---

## One-Time SSH Setup (Windows)

### Generate a key (if you don't have one)

```powershell
ssh-keygen -t ed25519 -C "qiec-deploy"
```

### Add public key to Hostinger

1. Copy `C:\Users\<you>\.ssh\id_ed25519.pub`
2. hPanel → SSH Access → **Manage SSH Keys** → Add key

### SSH config

Already configured at `C:\Users\devma\.ssh\config`:

```
Host hostinger-qiec
    HostName 82.197.83.145
    User u873288737
    Port 65002
    IdentityFile C:\Users\devma\.ssh\id_ed25519
    IdentitiesOnly yes
```

If siematplus uses the same server, you may alias both:

```
Host hostinger-qiec hostinger
    HostName <same IP>
    User <username>
    Port 22
    IdentityFile ~/.ssh/id_ed25519
```

---

## Local Deploy Scripts

Scripts live in project root and are **gitignored** (contain server paths).

| File | Purpose |
|------|---------|
| `deploy.bat` | Windows CMD — run via `npm run deploy` |
| `deploy.ps1` | PowerShell alternative |

Safe templates are in [DEPLOY_TEMPLATE.md](DEPLOY_TEMPLATE.md).

### Deploy workflow

```
npm run build:landing
  → git add / commit / push origin master
  → ssh git pull + artisan cache clear (if vendor present)
  → npm run optimize:production
```

| Step | Command | Where it runs |
|------|---------|---------------|
| 1 | `npm run build:landing` | Local |
| 2 | `git push origin master` | Local → GitHub |
| 3 | `git pull` + cache clear | Hostinger (SSH) |
| 4 | `npm run optimize:production` | Hostinger (SSH via local script) |

Run:

```powershell
npm run deploy
```

### Post-deploy optimization (`optimize:production`)

Implemented in [`scripts/optimize-production.sh`](../scripts/optimize-production.sh). Step 4 of `deploy.bat` calls this automatically.

- Copies Laravel File Manager assets from `vendor/unisharp/laravel-filemanager/public/` to `public/vendor/laravel-filemanager/` (required for admin image uploads; `public/vendor/` is gitignored)
- Copies language flag SVGs from `vendor/stijnvanouplines/blade-country-flags/resources/svg/` to `public/vendor/blade-country-flags/` (required for language dropdown flags in admin, panel, and web)
- Clears bootstrap config/route cache files and framework cache data
- Restarts LiteSpeed PHP workers (`pkill lsphp`)

> **ionCube:** `php artisan` over SSH often fails on Hostinger CLI. The website still works. Do not rely on `php artisan vendor:publish` on the server — the optimize script copies LFM and flag assets instead.

### Feature branch vs `master`

| Situation | Command |
|-----------|---------|
| Production on `master` (routine) | `npm run deploy` |
| Production on a feature branch | Manual `git push` + SSH `git pull origin <branch>` + `npm run optimize:production` |
| After merging feat → `master` | One-time: checkout `master` on server, then `npm run deploy` |

Example — switch production to `master` after merge:

```bash
ssh hostinger-qiec "cd domains/training.qiec.sa/public_html && git fetch origin && git checkout master && git pull origin master"
npm run optimize:production
```

---

## First-Time Server Setup

Run these commands **once** on Hostinger via SSH.

### Option A — Empty public_html

```bash
cd ~/domains/training.qiec.sa/public_html
git clone git@github.com:mahmoud-wahba-dev/qice-rocket-lms.git .
composer install --no-dev --optimize-autoloader
```

### Option B — Site already exists (Laravel on server)

```bash
cd ~/domains/training.qiec.sa/public_html
git init
git remote add origin git@github.com:mahmoud-wahba-dev/qice-rocket-lms.git
git fetch origin
git checkout -b master origin/master
composer install --no-dev --optimize-autoloader
```

### Environment file

```bash
# If .env does not exist on server:
cp .env.example .env
nano .env   # or use hPanel File Manager editor
php artisan key:generate
```

**Production `.env` minimum:**

```env
APP_NAME=QIEC
APP_ENV=production
APP_DEBUG=false
APP_URL=https://training.qiec.sa

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=<from hPanel>
DB_USERNAME=<from hPanel>
DB_PASSWORD=<from hPanel>
```

### Laravel setup

```bash
php artisan storage:link
php artisan migrate --force    # only if DB schema needs updating
chmod -R 775 storage bootstrap/cache
```

### Upload assets NOT in git

These folders are gitignored and must be uploaded manually (File Manager or SFTP):

| Path | Contents |
|------|----------|
| `public/assets/design_1/` | Stock RocketLMS theme (admin, panel, legacy web) |
| `public/assets/landing_v1/img/` | QIEC images exported from Figma |
| `public/vendor/blade-country-flags/` | Language dropdown flag SVGs (synced by `optimize:production` from Composer vendor) |

Without `design_1` assets, admin panel and stock pages break. Without `landing_v1/img`, landing images show broken links. Without `blade-country-flags`, language dropdown flags show broken images in admin, panel, and web headers.

### Add GitHub deploy key on server (if git pull fails)

On the server:

```bash
ssh-keygen -t ed25519 -C "hostinger-qiec-deploy"
cat ~/.ssh/id_ed25519.pub
```

Add the public key to GitHub → repo **Settings → Deploy keys** (read-only is enough for pull).

---

## Every Deploy (routine)

From your local machine (production must be on `master`):

```powershell
npm run deploy
```

This runs all four steps: build → push → SSH pull → `optimize:production`.

Or manually:

```powershell
npm run build:landing
git add .
git commit -m "deploy: description of changes"
git push origin master
ssh hostinger-qiec "cd domains/training.qiec.sa/public_html && git pull origin master && php artisan config:clear && php artisan cache:clear && php artisan view:clear"
npm run optimize:production
```

---

## Pre-Launch Checklist

- [ ] SSH test passes
- [ ] Server `.env` has `APP_DEBUG=false`
- [ ] `public/assets/design_1/` uploaded
- [ ] `public/assets/landing_v1/img/` uploaded
- [ ] Remove `X-Robots-Tag: noindex` from root `.htaccess` when ready for Google indexing
- [ ] QIEC branding verified (no siematplus references)
- [ ] `npm run build:landing` output committed in `public/build/`

---

## Troubleshooting

| Problem | Likely cause | Fix |
|---------|--------------|-----|
| `ssh: Could not resolve hostname` | SSH config missing | Add `hostinger-qiec` to `~/.ssh/config` |
| `Permission denied (publickey)` | Key not added to Hostinger | Add public key in hPanel SSH Access |
| `git pull` asks for password | No deploy key on server | Add server SSH key to GitHub deploy keys |
| Site loads but no CSS | Vite build not deployed | Run `npm run build:landing` and push `public/build/` |
| Images broken on landing | `landing_v1/img` not uploaded | Upload via File Manager |
| 500 error after deploy | `.env` or permissions | Check `storage/logs/laravel.log`, fix `storage` permissions |
| `npm run landing:build` fails | Wrong script name | Use `npm run build:landing` |
| Admin upload broken (`filemanager is not a function`) | Missing `public/vendor/laravel-filemanager/` | Run `npm run optimize:production` (or full `npm run deploy`) |
| Language dropdown flags broken (admin, panel, web) | Missing `public/vendor/blade-country-flags/` | Run `npm run optimize:production` (or full `npm run deploy`) |
| Reset admin password | Need to change `admin@demo.com` credentials | Set `ADMIN_EMAIL` and `ADMIN_PASSWORD` in `.env`, then run `php artisan qiec:reset-admin-password` (on Hostinger use `PHP_BIN=$(bash scripts/hostinger-php.sh)` first) |
| `php artisan` fails over SSH | ionCube not in CLI PHP | Use optimize script; delete `bootstrap/cache/config.php` manually if needed |

---

### First-time server setup (run on Hostinger via SSH)

```bash
cd ~/domains/training.qiec.sa/public_html
bash scripts/hostinger-first-setup.sh
```

Or follow the manual steps in the "First-Time Server Setup" section above.

Script location: [`scripts/hostinger-first-setup.sh`](../scripts/hostinger-first-setup.sh)

---

## Related docs

- [DEPLOY_TEMPLATE.md](DEPLOY_TEMPLATE.md) — copy-paste deploy script templates
- [CLIENT_PLAYBOOK.md](CLIENT_PLAYBOOK.md) — reusable client workflow
- [ISSUES_AND_LESSONS.md](ISSUES_AND_LESSONS.md) — deployment pitfalls from this project
