# Production Rollback & Restore Guide

**Site:** https://training.qiec.sa  
**SSH alias:** `hostinger-qiec`  
**App path:** `~/domains/training.qiec.sa/public_html`

Use this guide only when a deploy breaks production or you need to return to a known-good state.

---

## Known-good restore points

| Type | Reference | When to use |
|------|-----------|-------------|
| **Git tag** | `production-stable-2026-06-26` → commit `24afcdf` | Code rollback (preferred) |
| **Full folder backup** | `~/domains/training.qiec.sa/public_html_backup_20260624` (1.6 GB) | Code + `.env` + vendor + media snapshot from first install |
| **Partial backup** | `~/.qiec-deploy-backup` (943 MB) | `.env`, `vendor`, partial `store`/`assets` |
| **Env backup** | `public_html/.env.bak-20260625-110929` | Mail/password fix rollback only |
| **Older copy** | `~/domains/training.qiec.sa/public_html_old_20260624` (62 MB) | Partial / older state |

Check what is live on the server before acting:

```bash
ssh hostinger-qiec "cd domains/training.qiec.sa/public_html && git log -1 --oneline && git describe --tags 2>/dev/null || echo 'no tag checked out'"
```

---

## Rollback A — Git (code only, recommended)

Rolls back **application code** from GitHub. Does **not** remove uploaded media in `public/store/` or change `.env`.

```bash
ssh hostinger-qiec "cd domains/training.qiec.sa/public_html && git fetch origin && git checkout production-stable-2026-06-26"
```

Or pin a specific commit:

```bash
ssh hostinger-qiec "cd domains/training.qiec.sa/public_html && git fetch origin && git checkout 24afcdf"
```

Return to latest `master` after fixing forward:

```bash
ssh hostinger-qiec "cd domains/training.qiec.sa/public_html && git fetch origin && git checkout master && git pull origin master"
```

**Note:** `php artisan` may fail over SSH (ionCube CLI). The website PHP runtime is unaffected. Avoid running cache commands on production unless you know they work on this host.

After any git rollback or checkout, run from local:

```bash
npm run optimize:production
```

This syncs `public/vendor/laravel-filemanager/` (gitignored), clears stale caches, and restarts PHP workers — without using `php artisan` on SSH.

---

## Rollback B — Full folder restore (emergency)

Use only if the site is broken and git rollback is not enough (corrupt files, bad `.env`, missing vendor).

```bash
ssh hostinger-qiec
cd ~/domains/training.qiec.sa
mv public_html "public_html_broken_$(date +%Y%m%d_%H%M%S)"
cp -a public_html_backup_20260624 public_html
```

Then verify:

1. Site loads: https://training.qiec.sa  
2. Admin login: https://training.qiec.sa/admin  
3. `.env` has correct DB and mail settings  

To restore only `.env` from backup without full folder swap:

```bash
ssh hostinger-qiec "cp domains/training.qiec.sa/public_html/.env domains/training.qiec.sa/public_html/.env.before-restore && cp domains/training.qiec.sa/public_html/.env.bak-20260625-110929 domains/training.qiec.sa/public_html/.env"
```

---

## Rollback C — Restore `.env` from deploy backup

```bash
ssh hostinger-qiec "cp ~/.qiec-deploy-backup/.env domains/training.qiec.sa/public_html/.env"
```

Clear cached config if the site still reads old values (delete cached config file; safe on Hostinger web PHP):

```bash
ssh hostinger-qiec "rm -f domains/training.qiec.sa/public_html/bootstrap/cache/config.php"
```

---

## What NOT to do on production (unless emergency)

- Do not run `git reset --hard` on a live server without a backup path above.
- Do not commit or overwrite `.env` via git.
- Do not delete `public_html_backup_20260624` until a newer full backup exists.
- Do not run `config:cache` / `route:cache` via CLI if ionCube blocks artisan — test locally first.

---

## Slowness diagnosis (2026-06-26)

Read-only checks found:

| Check | Result |
|-------|--------|
| Server load | ~18 on 64 cores (shared host busy) |
| QIEC PHP workers | ~4 (not overloading the server) |
| Homepage TTFB | ~1.5–2 s |
| `APP_DEBUG` | `false` |
| `storage/logs` | ~300 KB |
| `public/store` | ~504 MB |

**Conclusion:** Slowness is mainly **shared hosting load + heavy RocketLMS/Laravel stack**, not from large uploads or a broken install. See plan notes in project history; no production tuning was applied during diagnosis.

---

## Health check (read-only)

Run locally (Git Bash or PowerShell):

```bash
# Bash (Git Bash / Linux / macOS)
./scripts/check-production-health.sh

# Windows PowerShell
./scripts/check-production-health.ps1
```

This only reads server uptime, log size, and HTTP timing. It does **not** modify production.

---

## Related docs

- [DEPLOYMENT.md](DEPLOYMENT.md) — SSH and deploy workflow  
- [ENV_PRODUCTION_CHECKLIST.md](ENV_PRODUCTION_CHECKLIST.md) — `.env` validation  
- [PRODUCTION_MAIL.md](PRODUCTION_MAIL.md) — SMTP troubleshooting  
