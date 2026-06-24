# QIEC – Project Architecture Summary

**Project:** qiec (Quality and Innovation in Education Center)
**Current folder name:** `qiec-project`
**Production domain:** https://training.qiec.sa
**Base platform:** RocketLMS v2.1 (IonCube-encoded core)
**Laravel version:** 9.x
**PHP version:** ^8.1 required (server uses PHP 8.2 handler)

---

## Project Structure Overview

```
qiec-project/
├── .editorconfig              – Editor settings (4-space indent, UTF-8, LF)
├── .env                       – ⚠️ PRODUCTION credentials (MUST NOT be committed)
├── .gitattributes             – Git attributes
├── .gitignore                 – Incomplete (missing .env, firebase-auth.json, etc.)
├── .htaccess                  – Root rewrite to /public/, PHP 8.2, X-Robots-Tag noindex
├── artisan                    – Laravel CLI entry point
├── composer.json              – PHP dependencies
├── firebase-auth.json         – ⚠️ Firebase service account key (SENSITIVE)
├── package.json               – NPM dependencies (Mix + Vite landing pipeline)
├── package-lock.json          – NPM lockfile
├── phpunit.xml                – PHPUnit config
├── quice-prod-db-u873288737_markaz.sql  – ⚠️ Production DB dump (1 MB)
├── u873288737_markaz.sql               – ⚠️ Another DB dump (1.6 MB)
│
├── app/                       – Laravel application code
│   └── Http/Controllers/
│       ├── Admin/             – Admin panel controllers
│       ├── Api/               – API controllers
│       ├── Auth/              – Authentication controllers
│       ├── LandingBuilder/    – Landing page builder
│       ├── MainTraits/        – Shared traits
│       ├── Panel/             – User panel controllers
│       ├── Web/               – 57 web controllers (stock RocketLMS)
│       └── Controller.php     – Base controller
│
├── bootstrap/                 – Laravel bootstrap
├── config/                    – 53 config files (payment gateways, services, etc.)
├── database/                  – Migrations, seeders, factories
├── lang/                      – Translations
├── public/                    – Web root
│   ├── .htaccess              – Laravel public .htaccess
│   ├── index.php              – Application entry
│   ├── mix-manifest.json      – Laravel Mix manifest (37 KB)
│   ├── assets/                – Compiled assets
│   ├── store/                 – User uploads
│   └── vendor/                – Published vendor assets
│
├── resources/
│   ├── js/
│   │   ├── admin/             – Admin JS
│   │   └── design_1/          – Frontend JS (stock RocketLMS)
│   ├── sass/
│   │   ├── admin/             – Admin SASS
│   │   ├── agora/             – Video conferencing styles
│   │   └── design_1/          – Frontend SASS (stock RocketLMS)
│   └── views/
│       ├── admin/             – Admin views
│       ├── api/               – API views
│       ├── components/        – Shared components
│       ├── design_1/          – Frontend views (stock RocketLMS theme)
│       ├── landing_v1/        – Custom public landing layer (Vite/Tailwind)
│       ├── errors/            – Error pages
│       ├── landingBuilder/    – Landing builder views
│       ├── purchase_code/     – License activation views
│       └── vendor/            – Published vendor views
│
├── routes/
│   ├── web.php                – Web routes (530 lines, stock + custom admin include)
│   ├── admin.php              – Admin routes (543 KB — very large!)
│   ├── panel.php              – User panel routes (36 KB)
│   ├── api.php                – API routes
│   ├── api/                   – API route subdirectory
│   ├── channels.php           – Broadcasting channels
│   ├── console.php            – Console commands
│   └── custom_admin.php       – Custom admin routes file (template, no active routes)
│
├── storage/                   – Laravel storage
├── tests/                     – PHPUnit tests
├── _docs/                     – Project documentation (see README.md)
├── scripts/                   – Server setup scripts
└── vendor/                    – ⚠️ Composer dependencies (should NOT be committed)
```

---

## Current State (landing_v1 implemented)

The custom **landing_v1** public layer is active. Stock RocketLMS routes for `/`, `/contact`, `/instructors`, `/cart` are replaced by `LandingV1Controller`. Auth uses `landing_v1` login/register views.

**Key files:**
- `app/Http/Controllers/Web/LandingV1Controller.php` — 15+ page methods
- `resources/views/landing_v1/` — layouts, components, pages
- `resources/css/landing_v1.css` — QIEC design tokens
- `vite.config.js`, `tailwind.config.js`, `postcss.config.js`
- `public/build/` — Vite production output

See [CLIENT_QIEC.md](CLIENT_QIEC.md) for route map and page status.

---

## Routes Analysis (`routes/web.php`)

### Current State
Landing V1 is the **primary public layer**. Custom routes block (near end of `web.php`):

| Route | Controller | Notes |
|-------|------------|-------|
| `/` | `LandingV1Controller@index` | Home with DB courses/trainers |
| `/about`, `/contact`, `/workshops` | Static/marketing pages | |
| `/courses` | Dynamic listing + AJAX filters | |
| `/courses-paid`, `/course-details-free`, `/course-details-paid` | Figma prototypes | Static |
| `/blogs`, `/blog-details` | Prototypes | Static |
| `/instructors` | Dynamic teacher stats | |
| `/cart`, `/checkout` | Cart + payment flow | Guest cart supported |
| `/webinar/{slug}` | Full course details | Dynamic |

Auth: `/login`, `/register` → `landing_v1` auth views.

### Before migration (historical)
Stock RocketLMS routes were active: `HomeController@index`, `ContactController`, `InstructorsController`, `CartController`. These are now commented out or replaced.

### Additional Routes (not in siematplus)
- `/events/*` — Full event system with ticket validation, reviews
- `/meeting-packages/*` — Meeting package purchasing
- `custom_admin.php` — Empty custom admin routes template (ready for use)

### Routes that were replaced (now landing_v1)
- `GET /` → `LandingV1Controller@index`
- `GET /contact` → `LandingV1Controller@contact`
- `GET /instructors` → `LandingV1Controller@instructors`
- `GET /cart` → `LandingV1Controller@cart` (guest OK)
- `GET /courses` → `LandingV1Controller@courses`

---

## Controllers Analysis

### Web Controllers
- **`LandingV1Controller.php`** — custom public layer (~451 lines)
- 57 stock RocketLMS controllers remain for admin, panel, API, events, etc.
- `CartManagerController.php` — guest + auth cart handling
- Auth controllers return `landing_v1` views for login/register

### Before migration (historical)
- **No `LandingV1Controller.php`** existed — created during Phase 2 migration

---

## Asset Pipeline

### Dual pipeline

| Layer | Tool | npm scripts | Output |
|-------|------|-------------|--------|
| `landing_v1` | Vite + Tailwind + FlyonUI | `dev:landing`, `build:landing` | `public/build/` |
| `design_1` (stock) | Laravel Mix (prebuilt) | `dev`, `prod` | `public/assets/design_1/` |

**Note:** `webpack.mix.js` is missing from repo — stock Mix assets are prebuilt in `public/assets/design_1/` (gitignored). Do not delete.

### landing_v1 config files
- `vite.config.js` — inputs: `landing_v1.css`, `landing_v1.js`
- `tailwind.config.js` — scoped to `#landing-v1-app`, FlyonUI plugin
- `postcss.config.js` — tailwindcss + autoprefixer

### package.json scripts
```json
{
    "dev:landing": "vite",
    "build:landing": "vite build",
    "deploy": "deploy.bat",
    "dev": "mix",
    "prod": "mix --production"
}
```

### Before migration (historical)
Missing compared to siematplus at project start:
- ❌ No vite.config.js (now ✅ present)
- ❌ No tailwind.config.js (now ✅ present)
- ❌ No landing_v1 views (now ✅ present)

---

## Dependencies Comparison

### Extra packages in qiec (not in siematplus `composer.json`)
| Package | Purpose |
|---------|---------|
| `alazzi-az/laravel-tamara` | Tamara payment gateway |
| `league/csv` | CSV import/export |
| `tabbyai/laravel` | Tabby payment gateway |

### Missing packages from qiec (present in siematplus)
| Package | Purpose |
|---------|---------|
| `mailchimp/marketing` | Mailchimp API integration |

### Same core packages
Both share identical RocketLMS dependency sets (90+ packages for payment gateways, social login, SMS, etc.)

---

## Environment Configuration (`.env`)

### ⚠️ CRITICAL SECURITY ISSUES
1. **`.env` is NOT in `.gitignore`** — production credentials are exposed
2. **`firebase-auth.json`** with private key is tracked
3. **Two SQL dump files** with production data are present in the project root
4. **`APP_ENV=production`** with **`APP_DEBUG=true`** — debug should be off in production

### Current Values
| Key | Value | Issue |
|-----|-------|-------|
| `APP_NAME` | `training` | Should be `qiec` |
| `APP_ENV` | `production` | Contains prod credentials locally |
| `APP_DEBUG` | `true` | Should be `false` in production |
| `APP_URL` | `https://training.qiec.sa/` | Trailing slash, needs local domain for dev |
| `DB_DATABASE` | `u873288737_markaz` | Production DB name |
| `DB_USERNAME` | `u873288737_user_markaz` | Production DB user |
| `DB_PASSWORD` | `CBKc;6@z` | ⚠️ EXPOSED production password |
| `MAIL_HOST` | `smtp.hostinger.com` | Hostinger SMTP |
| `MAIL_USERNAME` | `contact@training.qiec.sa` | Production mail account |

### Missing Keys (present in siematplus `.env`)
- `MYFATOORAH_API_KEY` / `MYFATOORAH_BASE_URL`
- `MAILCHIMP_API_KEY` / `MAILCHIMP_SERVER_PREFIX` / `MAILCHIMP_LIST_ID`
- `MANDRILL_API_KEY`
- `WEBHOOK_SECRET`

---

## `.gitignore` Comparison

| Entry | siematplus | qiec | Issue |
|-------|-----------|------|-------|
| `/.env` | ✅ | ❌ MISSING | **Critical: .env with passwords is tracked** |
| `/firebase-auth.json` | ✅ | ❌ MISSING | Sensitive service account key exposed |
| `*.pem` | ✅ | ❌ MISSING | SSL certificates could leak |
| `*.sql` | ✅ | ❌ MISSING | Two SQL dumps exist in root |
| `app/logs/*.log` | ✅ | ❌ MISSING | Log files could leak |
| `/deploy.ps1` | ✅ | N/A | No deploy script exists yet |
| `/deploy.bat` | ✅ | N/A | No deploy script exists yet |
| `/vendor` | ✅ | ✅ | Both ignore vendor |
| `/node_modules` | ✅ | ✅ | Both ignore node_modules |
| `/public/store/*` | ❌ | ✅ | qiec has this, siematplus doesn't |
| `/old` | ✅ | ❌ | siematplus-specific |
| `/storage/framework/views` | ✅ | ❌ MISSING | Compiled views could bloat repo |

---

## `.htaccess` Differences

| Feature | siematplus | qiec |
|---------|-----------|------|
| X-Robots-Tag | ❌ Not set | ✅ `noindex, follow` (blocking indexing) |
| Rewrite to `/public/` | ✅ | ✅ |
| PHP session path | `/opt/alt/php82/` | `/opt/alt/php81/` (older path) |
| PHP handler | `ea-php82` (mod_mime) | `x-lsphp82` (LiteSpeed) |

**Note:** The `.htaccess` has `X-Robots-Tag: noindex, follow` which will prevent search engines from indexing the site. This needs to be reviewed before launch.

---

## Git Status

- **Git initialized** — remote: `git@github.com:mahmoud-wahba-dev/qice-rocket-lms.git`
- Branch: `master`
- Deploy scripts (`deploy.bat`, `deploy.ps1`) are gitignored — templates in `_docs/DEPLOY_TEMPLATE.md`
- Documentation: `_docs/README.md` is the master index

---

## Database

- SQL dumps moved to `_backups/` (not in project root)
- Database name (production): `u873288737_markaz` (Hostinger format)
- Local dev: configure in `.env` (XAMPP: `root`, empty password)

---

## Deployment

- **Method:** Local `npm run deploy` (build → git push → SSH git pull)
- **Server path:** `domains/training.qiec.sa/public_html`
- **Guide:** [_docs/DEPLOYMENT.md](DEPLOYMENT.md)
- **First setup script:** `scripts/hostinger-first-setup.sh`

---

## Files to Clean Up Before Git Init

> Historical — Phase 0 completed. See [PHASE0_CHANGELOG.md](PHASE0_CHANGELOG.md).

| File / Pattern | Reason |
|----------------|--------|
| `.env` | Contains production credentials |
| `firebase-auth.json` | Contains private key |
| `quice-prod-db-u873288737_markaz.sql` | Production DB dump |
| `u873288737_markaz.sql` | Production DB dump |
| `vendor/` | Should be installed via `composer install` |
| `node_modules/` | Should be installed via `npm install` |
| `package-lock.json` | Can be regenerated (823 KB) |
