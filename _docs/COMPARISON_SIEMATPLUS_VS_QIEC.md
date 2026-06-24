# Siematplus vs QIEC — Comparison & Difference Analysis

> **Updated:** Reflects current state after landing_v1 migration. For QIEC-specific status see [CLIENT_QIEC.md](CLIENT_QIEC.md).

## Overview

Both projects are built on **RocketLMS** (IonCube-encoded Laravel LMS platform) and hosted on **Hostinger**. Siematplus was the reference implementation; QIEC reuses the same `landing_v1` architecture with different Figma design and branding.

---

## High-Level Comparison

| Aspect | siematplus | qiec |
|--------|-----------|------|
| **Folder name** | `rocketlms` | `qiec-project` |
| **RocketLMS version** | 2.0.1 | 2.1 (newer) |
| **Local domain** | http://siematplus.local | Configure as `http://qiec.local` |
| **Production domain** | https://siematplus.com | https://training.qiec.sa |
| **Custom landing layer** | ✅ Full `landing_v1/` | ✅ Full `landing_v1/` (+ extra prototype pages) |
| **Git initialized** | ✅ Yes | ✅ Yes |
| **Git remote** | GitHub (master) | `mahmoud-wahba-dev/qice-rocket-lms` |
| **Deploy scripts** | ✅ `deploy.bat` + `deploy.ps1` | ✅ Adapted for `training.qiec.sa` |
| **`.env` in .gitignore** | ✅ Yes | ✅ Yes (Phase 0) |

---

## Custom Layer Status

| Component | siematplus | qiec |
|-----------|-----------|------|
| `LandingV1Controller.php` | ✅ ~411 lines | ✅ ~451 lines (extra methods) |
| `views/landing_v1/layouts/` | ✅ app, navbar, footer | ✅ Same structure |
| `views/landing_v1/components/` | ✅ 6+ components | ✅ 10+ components |
| `views/landing_v1/pages/` | ✅ 9 pages + auth | ✅ 15+ pages + auth + prototypes |
| `resources/css/landing_v1.css` | ✅ Design tokens | ✅ QIEC tokens (`#0f4c45`, Cairo) |
| `resources/js/landing_v1.js` | ✅ | ✅ |
| Custom routes in `web.php` | ✅ landing.v1.* | ✅ landing.v1.* (+ QIEC-only routes) |

### QIEC-only landing pages (not in siematplus)

| Page | Route | Status |
|------|-------|--------|
| Workshops | `/workshops` | Static prototype |
| Blogs | `/blogs`, `/blog-details` | Static prototype |
| Paid courses listing | `/courses-paid` | Static prototype |
| Course detail layouts | `/course-details-free`, `/course-details-paid` | Static prototype |

---

## Build Pipeline Comparison

| Feature | siematplus | qiec |
|---------|-----------|------|
| **Vite** | ✅ landing_v1 | ✅ landing_v1 |
| **Laravel Mix** | ✅ design_1 | ✅ design_1 (prebuilt, no webpack.mix.js) |
| **Tailwind CSS** | ✅ v3.4 | ✅ v3.4 |
| **FlyonUI** | ✅ v1.3 | ✅ v1.3 |
| **Iconify/Tabler** | ✅ | ✅ |
| Vite dev script | `landing:dev` | `dev:landing` |
| Vite build script | `landing:build` | `build:landing` |
| `npm run deploy` | ✅ | ✅ |

---

## `composer.json` Dependencies Diff

### Extra in qiec (not in siematplus)
| Package | Purpose |
|---------|---------|
| `alazzi-az/laravel-tamara` | Tamara BNPL payment |
| `league/csv` | CSV processing |
| `tabbyai/laravel` | Tabby BNPL payment |

### Extra in siematplus (not in qiec)
| Package | Purpose |
|---------|---------|
| `mailchimp/marketing` | Mailchimp email marketing |

---

## Routes Differences

### Shared landing routes (both projects)
`/`, `/about`, `/contact`, `/courses`, `/instructors`, `/cart`, `/checkout`, `/webinar/{slug}`

### QIEC-only routes
| Route | Purpose |
|-------|---------|
| `/workshops` | Workshops page (prototype) |
| `/courses-paid` | Paid courses listing (prototype) |
| `/course-details-free`, `/course-details-paid` | Course detail layouts (prototype) |
| `/blogs`, `/blog-details` | News section (prototype) |
| `/events/*` | Stock RocketLMS events system |
| `/meeting-packages/*` | Meeting packages |

### siematplus-only routes
| Route | Purpose |
|-------|---------|
| `/mailchimp/webhook` | Mailchimp webhook |
| `/test`, `/test2` | Test routes |

---

## Configuration Files

| File | siematplus | qiec |
|------|-----------|------|
| `vite.config.js` | ✅ | ✅ |
| `tailwind.config.js` | ✅ | ✅ |
| `postcss.config.js` | ✅ | ✅ |
| `webpack.mix.js` | ✅ Present | ❌ Missing (prebuilt assets used) |
| `deploy.bat` / `deploy.ps1` | ✅ siematplus.com path | ✅ training.qiec.sa path |
| `_docs/` | ✅ | ✅ Expanded (playbook, deployment, issues) |
| `scripts/hostinger-first-setup.sh` | — | ✅ QIEC server setup |

---

## `.htaccess` Key Differences

| Setting | siematplus | qiec |
|---------|-----------|------|
| `X-Robots-Tag` | Not set | `noindex, follow` ⚠️ remove before launch |
| PHP session path | `/opt/alt/php82/` | `/opt/alt/php81/` |
| PHP handler | `ea-php82` | `x-lsphp82` (LiteSpeed) |

---

## Security Posture

| Issue | siematplus | qiec |
|-------|-----------|------|
| `.env` in `.gitignore` | ✅ | ✅ (Phase 0) |
| `firebase-auth.json` in `.gitignore` | ✅ | ✅ |
| `*.sql` in `.gitignore` | ✅ | ✅ (dumps in `_backups/`) |
| Deploy scripts gitignored | ✅ | ✅ |

---

## Branding differences

| Item | siematplus | qiec |
|------|-----------|------|
| Default page title | Siemat Plus | QIEC Training |
| Contact email | info@siematplus.com | contact@training.qiec.sa |
| Primary color | Client-specific | `#0f4c45` |
| Font | IBM Plex (typical) | Cairo |

---

## Summary

QIEC has completed the core migration from siematplus. Remaining work: wire prototype pages to DB, first Hostinger deploy, remove `noindex` before launch.

**Next steps:** [CLIENT_QIEC.md](CLIENT_QIEC.md) TODO list · [DEPLOYMENT.md](DEPLOYMENT.md)
