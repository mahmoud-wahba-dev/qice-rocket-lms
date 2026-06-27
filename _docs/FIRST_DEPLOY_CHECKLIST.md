# First Deploy Checklist — QIEC

Use this checklist for the **first production deploy** to Hostinger. Routine deploys after setup use `npm run deploy` only.

---

## Local readiness (done)

- [x] `deploy.bat` and `deploy.ps1` adapted for `training.qiec.sa`
- [x] `npm run build:landing` verified locally
- [x] `scripts/hostinger-first-setup.sh` created for server
- [x] SSH config added: `hostinger-qiec` → `82.197.83.145:65002`
- [x] Documentation in [_docs/DEPLOYMENT.md](DEPLOYMENT.md)

---

## Hostinger SSH (confirmed)

| Field | Value |
|-------|-------|
| IP | `82.197.83.145` |
| Port | `65002` |
| Username | `u873288737` |
| Status | ACTIVE |
| Server path | `domains/training.qiec.sa/public_html` |

**Test connection:**

```powershell
ssh hostinger-qiec "pwd"
```

Or without alias:

```powershell
ssh -p 65002 u873288737@82.197.83.145
```

---

## Your action items (before first deploy)

### Step 1 — Add SSH key to hPanel (if not done)

Password login works manually, but `npm run deploy` needs **key-based auth** (no password prompt).

1. Copy public key:

```powershell
Get-Content C:\Users\devma\.ssh\id_ed25519.pub | Set-Clipboard
```

2. hPanel → **training.qiec.sa → SSH Access → أضف مفتاح SSH**
3. Paste and save

4. Test:

```powershell
ssh hostinger-qiec "pwd && ls domains/training.qiec.sa/public_html"
```

### Step 2 — Server repository

SSH into Hostinger and run:

```bash
cd ~/domains/training.qiec.sa/public_html
bash scripts/hostinger-first-setup.sh
```

If the repo is not cloned yet, the script clones from GitHub. You may need a **deploy key** on the server:

```bash
ssh-keygen -t ed25519 -C "hostinger-qiec-deploy"
cat ~/.ssh/id_ed25519.pub
# Add this key to GitHub → repo Settings → Deploy keys
```

### Step 3 — Server `.env`

In hPanel → **Databases**, get MySQL credentials. Edit `.env` on server:

```env
APP_NAME=QIEC
APP_ENV=production
APP_DEBUG=false
APP_URL=https://training.qiec.sa

DB_HOST=localhost
DB_DATABASE=u873288737_markaz
DB_USERNAME=u873288737_user_markaz
DB_PASSWORD=<from hPanel — never commit>
```

### Step 4 — Upload gitignored assets

Via hPanel File Manager or SFTP, upload from your local machine:

| Local path | Server path |
|------------|-------------|
| `public/assets/design_1/` | `public_html/public/assets/design_1/` |
| `public/assets/landing_v1/img/` | `public_html/public/assets/landing_v1/img/` |

Without these, admin panel and landing images will be broken.

### Step 5 — First deploy from local

```powershell
cd c:\xampp\htdocs\qiec-project
npm run deploy
```

This will: build landing assets → git push → SSH git pull → post-deploy optimize (`laravel-filemanager` assets, cache clear, PHP restart).

### Step 6 — Verify site

- [ ] https://training.qiec.sa loads homepage
- [ ] CSS/JS loaded (check `public/build/` on server)
- [ ] Images visible on landing pages
- [ ] `/courses` shows courses from database
- [ ] Login/register pages render

### Step 7 — Before public launch

- [ ] Remove `X-Robots-Tag: noindex` from root `.htaccess`
- [ ] Confirm `APP_DEBUG=false` on server
- [ ] Test cart/checkout flow

---

## If deploy fails

| Error | See |
|-------|-----|
| Permission denied (publickey) | Step 1 — add `id_ed25519.pub` to hPanel SSH keys |
| Git pull permission denied | Step 2 — add server deploy key to GitHub |
| 500 error on site | Check `storage/logs/laravel.log` on server |
| No styles | Run `npm run build:landing` locally and redeploy |
| Broken images | Step 4 — upload assets |

Full troubleshooting: [DEPLOYMENT.md](DEPLOYMENT.md)

---

## After first deploy

Routine updates (production on `master`):

```powershell
npm run deploy
```

Includes `optimize:production` automatically (Step 4). No need to repeat Steps 2–4 unless you change servers or wipe `public_html`.

### Merging a feature branch to `master`

1. Merge on GitHub (or locally) into `master`
2. On server, switch to `master` once:

```bash
ssh hostinger-qiec "cd domains/training.qiec.sa/public_html && git fetch origin && git checkout master && git pull origin master"
```

3. From local: `npm run deploy` (or at minimum `npm run optimize:production`)
