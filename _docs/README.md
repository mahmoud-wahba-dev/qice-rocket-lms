# QIEC Project Documentation

**Quality and Innovation in Education Center (QIEC)** — custom public website on RocketLMS v2.1.

| | |
|---|---|
| **Production** | https://training.qiec.sa |
| **Local folder** | `c:\xampp\htdocs\qiec-project` |
| **GitHub** | `git@github.com:mahmoud-wahba-dev/qice-rocket-lms.git` |
| **Reference project** | siematplus (same architecture, different Figma/branding) |

---

## What this project is

RocketLMS is a Laravel LMS with a stock theme (`design_1`) for admin, user panel, and legacy pages. We added a **custom `landing_v1` layer** — a separate public marketing site built with **Vite + Tailwind CSS + FlyonUI**, wired to the same Laravel backend (courses, cart, checkout, auth).

```
RocketLMS Laravel 9
├── design_1          → prebuilt assets in public/assets/design_1/ (admin, panel)
└── landing_v1        → Vite/Tailwind/FlyonUI (new public site)
    ├── LandingV1Controller
    ├── resources/views/landing_v1/
    └── public/build/ (Vite output)
```

---

## Quick start (local dev)

```powershell
cd c:\xampp\htdocs\qiec-project
composer install
npm install
cp .env.example .env   # configure local DB (XAMPP: root, empty password)
php artisan key:generate
php artisan migrate    # if needed

# Landing dev server (hot reload)
npm run dev:landing

# Production asset build
npm run build:landing
```

Open the site via your local vhost (e.g. `http://qiec.local`) or XAMPP default.

---

## Deploy to Hostinger

**Routine (production on `master`):**

```powershell
npm run deploy
```

This runs:

1. `build:landing` (local Vite build)
2. Git commit + push to `master`
3. SSH `git pull` on server + artisan cache clear
4. `optimize:production` — sync admin file-manager assets, clear caches, restart PHP

**Feature branch** (before merge): push branch, SSH `git pull origin <branch>`, then `npm run optimize:production`. See [DEPLOYMENT.md](DEPLOYMENT.md).

Merging to `master` on GitHub does **not** auto-deploy — you still run `npm run deploy` locally.

---

## Documentation index

| Doc | Purpose |
|-----|---------|
| [CLIENT_PLAYBOOK.md](CLIENT_PLAYBOOK.md) | **Reusable process** for any new RocketLMS client with a new Figma |
| [CLIENT_QIEC.md](CLIENT_QIEC.md) | **This client** — routes, colors, pages, TODOs |
| [ISSUES_AND_LESSONS.md](ISSUES_AND_LESSONS.md) | Problems faced and how to avoid them next time |
| [DEPLOYMENT.md](DEPLOYMENT.md) | Hostinger SSH setup, first deploy, troubleshooting |
| [PRODUCTION_ROLLBACK.md](PRODUCTION_ROLLBACK.md) | Rollback tags, server backups, emergency restore |
| [ENV_PRODUCTION_CHECKLIST.md](ENV_PRODUCTION_CHECKLIST.md) | Local vs production `.env` validation |
| [PRODUCTION_MAIL.md](PRODUCTION_MAIL.md) | Hostinger SMTP + registration verification emails |
| [LOCAL_TO_PRODUCTION.md](LOCAL_TO_PRODUCTION.md) | Sync local DB + images to production |
| [FIRST_DEPLOY_CHECKLIST.md](FIRST_DEPLOY_CHECKLIST.md) | Step-by-step first production deploy |
| [DEPLOY_TEMPLATE.md](DEPLOY_TEMPLATE.md) | Copy-paste deploy script templates |
| [CHANGELOG.md](CHANGELOG.md) | Rolling project log |
| [ARCHITECTURE_SUMMARY.md](ARCHITECTURE_SUMMARY.md) | Technical architecture reference |
| [COMPARISON_SIEMATPLUS_VS_QIEC.md](COMPARISON_SIEMATPLUS_VS_QIEC.md) | Side-by-side with reference project |
| [MIGRATION_REUSE_PLAN.md](MIGRATION_REUSE_PLAN.md) | Original migration plan (historical) |
| [PHASE0_CHANGELOG.md](PHASE0_CHANGELOG.md) | Security fixes and git init |
| [FOLDER_RENAME_RECOMMENDATION.md](FOLDER_RENAME_RECOMMENDATION.md) | Why folder was renamed qice → qiec |

---

## When starting a new client (checklist)

1. Read [CLIENT_PLAYBOOK.md](CLIENT_PLAYBOOK.md) end-to-end
2. Clone RocketLMS base → run Phase 0 security (`.gitignore`, move `.env`/SQL dumps)
3. Copy `landing_v1` layer from siematplus or previous client
4. Update CSS tokens in `resources/css/landing_v1.css` from new Figma
5. Replace branding in navbar, footer, auth, page copy
6. Grep for old client name (`siematplus`, etc.) — see [ISSUES_AND_LESSONS.md](ISSUES_AND_LESSONS.md)
7. Add routes + `LandingV1Controller` methods for any new pages
8. Export images to `public/assets/landing_v1/img/` (not in git — upload to server manually)
9. Create `deploy.bat` / `deploy.ps1` from [DEPLOY_TEMPLATE.md](DEPLOY_TEMPLATE.md)
10. Complete Hostinger checklist in [DEPLOYMENT.md](DEPLOYMENT.md)

---

## Key files (landing_v1)

| Area | Path |
|------|------|
| Controller | `app/Http/Controllers/Web/LandingV1Controller.php` |
| Routes | `routes/web.php` (Landing V1 block near end) |
| Layout | `resources/views/landing_v1/layouts/app.blade.php` |
| Styles | `resources/css/landing_v1.css` |
| JS | `resources/js/landing_v1.js` |
| Vite config | `vite.config.js` |
| Built assets | `public/build/` |
