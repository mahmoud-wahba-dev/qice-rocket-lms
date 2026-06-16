# Siematplus vs QIEC — Comparison & Difference Analysis

## Overview

Both projects are built on **RocketLMS** (IonCube-encoded Laravel LMS platform) and hosted on **Hostinger**. Siematplus is the more mature project with a fully built custom landing layer, while qiec is still running on stock RocketLMS.

---

## High-Level Comparison

| Aspect | siematplus | qiec |
|--------|-----------|------|
| **Folder name** | `rocketlms` | `qice-project` (misspelled) |
| **RocketLMS version** | 2.0.1 | 2.1 (newer) |
| **Local domain** | http://siematplus.local | Not configured (points to production) |
| **Production domain** | https://siematplus.com | https://training.qiec.sa |
| **Custom landing layer** | ✅ Full `landing_v1/` layer | ❌ Stock RocketLMS only |
| **Git initialized** | ✅ Yes (with remote) | ❌ No `.git` directory |
| **Git remote** | GitHub (master branch) | None |
| **Deploy scripts** | ✅ `deploy.bat` + `deploy.ps1` | ❌ None |
| **`.env` in .gitignore** | ✅ Yes | ❌ **NO — security risk** |

---

## Custom Layer Status

| Component | siematplus | qiec |
|-----------|-----------|------|
| `LandingV1Controller.php` | ✅ 411 lines, 8 methods | ❌ Does not exist |
| `views/landing_v1/layouts/` | ✅ app, navbar, footer | ❌ Directory missing |
| `views/landing_v1/components/` | ✅ 6 components | ❌ Directory missing |
| `views/landing_v1/pages/` | ✅ 9 pages + auth/ | ❌ Directory missing |
| `resources/css/landing_v1.css` | ✅ With design tokens | ❌ No CSS directory at all |
| `resources/js/landing_v1.js` | ✅ 10.5 KB | ❌ Missing |
| Custom routes in `web.php` | ✅ 10 routes (landing.v1.*) | ❌ Stock routes only |

---

## Build Pipeline Comparison

| Feature | siematplus | qiec |
|---------|-----------|------|
| **Vite** | ✅ For landing_v1 assets | ❌ Not installed |
| **Laravel Mix** | ✅ For core RocketLMS assets | ✅ For core RocketLMS assets |
| **Tailwind CSS** | ✅ v3.4.19 | ❌ Not installed |
| **FlyonUI** | ✅ v1.3.0 | ❌ Not installed |
| **Iconify/Tabler** | ✅ Installed | ❌ Not installed |
| **PostCSS config** | ✅ Present | ❌ Missing |
| `npm run landing:dev` | ✅ | ❌ |
| `npm run landing:build` | ✅ | ❌ |
| `npm run deploy` | ✅ (runs deploy.bat) | ❌ |

---

## `package.json` Dependencies Diff

### Present in siematplus, missing from qiec
```json
{
    "@iconify-json/tabler": "^1.2.35",
    "@iconify/tailwind": "^1.2.0",
    "autoprefixer": "^10.5.0",
    "flyonui": "^1.3.0",
    "laravel-vite-plugin": "^0.7.2",  // exists in both but unused in qiec
    "postcss": "^8.5.14",
    "tailwindcss": "^3.4.19"
}
```

### Present in both (identical versions)
- `axios`, `bootstrap`, `jquery`, `laravel-mix`, `resolve-url-loader`, `sass`, `sass-loader`
- `agora-rtc-sdk`, `agora-rtc-sdk-ng`, `agora-rtm-sdk`, `feather-icons`
- `jquery-toast-plugin`, `jquery.toaster`, `select2`, `sweetalert2`, `tippy.js`

### NPM scripts difference
| Script | siematplus | qiec |
|--------|-----------|------|
| `landing:dev` | ✅ `vite` | ❌ Missing |
| `landing:build` | ✅ `vite build` | ❌ Missing |
| `deploy` | ✅ `deploy.bat` | ❌ Missing |

---

## `composer.json` Dependencies Diff

### Extra in qiec (not in siematplus)
| Package | Version | Purpose |
|---------|---------|---------|
| `alazzi-az/laravel-tamara` | ^1.2 | Tamara BNPL payment |
| `league/csv` | ^9.24 | CSV processing |
| `tabbyai/laravel` | ^2.1 | Tabby BNPL payment |

### Extra in siematplus (not in qiec)
| Package | Version | Purpose |
|---------|---------|---------|
| `mailchimp/marketing` | ^3.0 | Mailchimp email marketing |

---

## Routes Differences

### Routes that siematplus **replaced/overrode** (still active in qiec)
| Route | qiec (stock) | siematplus (custom) |
|-------|-------------|-------------------|
| `GET /` | `HomeController@index` | `LandingV1Controller@index` |
| `GET /contact` | `ContactController@index` | `LandingV1Controller@contact` |
| `GET /instructors` | `InstructorsController@instructors` | `LandingV1Controller@instructors` |
| `GET /cart` | `CartController@index` (auth required) | `LandingV1Controller@cart` (guest OK) |
| `GET /classes` | `ClassesController@index` | Removed (redirected to /courses) |
| `GET /courses` | Not present | `LandingV1Controller@courses` |

### Routes in qiec NOT in siematplus
| Route | Controller | Purpose |
|-------|-----------|---------|
| `/events/*` | `EventsController` | Full events system |
| `/meeting-packages/*` | `MeetingPackagesController` | Meeting packages |
| `/cron-jobs/*` | `CronJobsController` | Cron jobs (different prefix than siematplus) |

### Routes in siematplus NOT in qiec
| Route | Controller | Purpose |
|-------|-----------|---------|
| `/about` | `LandingV1Controller@about` | About page |
| `/courses` | `LandingV1Controller@courses` | Courses listing |
| `/webinar/{slug}` | `LandingV1Controller@courseDetails` | Course details |
| `/checkout` (GET+POST) | `LandingV1Controller@checkout` | Custom checkout |
| `/mailchimp/webhook` | `WebhookController@handle` | Mailchimp webhook |
| `/test`, `/test2` | Test controllers | Testing routes |

---

## Configuration Files Differences

| File | siematplus | qiec |
|------|-----------|------|
| `vite.config.js` | ✅ Present | ❌ Missing |
| `tailwind.config.js` | ✅ Present | ❌ Missing |
| `postcss.config.js` | ✅ Present | ❌ Missing |
| `webpack.mix.js` | ✅ Present | ❌ Missing (but mix-manifest.json exists) |
| `deploy.bat` | ✅ Present | ❌ Missing |
| `deploy.ps1` | ✅ Present | ❌ Missing |
| `.env.example` | ✅ Present | ❌ Missing |
| `.user.ini` | ✅ Present | ❌ Missing |
| `_docs/` directory | ✅ With 3 docs | ✅ Being created now |
| `docs/` directory | ✅ Present (separate) | ❌ Missing |
| `support/` directory | ✅ Present | ❌ Missing |
| `build_output.txt` | ✅ Present (109 KB) | ❌ Missing |

---

## `.htaccess` Key Differences

| Setting | siematplus | qiec |
|---------|-----------|------|
| `X-Robots-Tag` | Not set | `noindex, follow` ⚠️ |
| PHP session path | `/opt/alt/php82/` | `/opt/alt/php81/` |
| PHP handler type | `mod_mime` (`ea-php82`) | `LiteSpeed` (`x-lsphp82`) |

---

## Security Posture Comparison

| Issue | siematplus | qiec |
|-------|-----------|------|
| `.env` in `.gitignore` | ✅ | ❌ **EXPOSED** |
| `firebase-auth.json` in `.gitignore` | ✅ | ❌ **EXPOSED** |
| `*.sql` in `.gitignore` | ✅ | ❌ SQL dumps in root |
| `*.pem` in `.gitignore` | ✅ | ❌ Missing |
| `APP_DEBUG` | `true` (local only) | `true` (with prod credentials!) |
| DB credentials in `.env` | Local (root/no password) | ⚠️ Production credentials |
| Mail credentials in `.env` | Production SMTP | Production SMTP |

---

## Summary of Work Needed for QIEC

1. **Security fixes** — `.gitignore` must be updated before git init
2. **Custom landing layer** — Full `landing_v1/` structure needs to be replicated
3. **Build pipeline** — Vite + Tailwind + FlyonUI need to be installed and configured
4. **Deploy scripts** — Need new scripts pointing to `training.qiec.sa`
5. **Environment setup** — Local `.env` with safe dev credentials
6. **Route customization** — Add `LandingV1Controller` and custom routes block
7. **Design adaptation** — New color tokens, branding, fonts for qiec
