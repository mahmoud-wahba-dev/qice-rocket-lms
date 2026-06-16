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
├── package.json               – NPM dependencies (Mix only, no Vite)
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
└── vendor/                    – ⚠️ Composer dependencies (should NOT be committed)
```

---

## Routes Analysis (`routes/web.php`)

### Current State
The web routes are **stock RocketLMS** — no custom landing layer has been added yet.

**Key differences from siematplus:**
- Home route: `Route::get('/', 'HomeController@index')` (stock RocketLMS HomeController)
- Contact: Uses stock `ContactController@index` under `/contact`
- Instructors: Uses stock `InstructorsController@instructors` under `/instructors`
- Cart: Uses stock `CartController@index` under `/cart`
- **No LandingV1Controller** — does not exist in the project
- **No `landing_v1` view directory** exists
- **No custom route block** or route naming convention

### Additional Routes (not in siematplus)
- `/events/*` — Full event system with ticket validation, reviews
- `/meeting-packages/*` — Meeting package purchasing
- `custom_admin.php` — Empty custom admin routes template (ready for use)

### Routes that siematplus customized/commented out
These are still active in qiec and will need to be replaced:
- `GET /` → `HomeController@index` (stock)
- `GET /classes` → `ClassesController@index` (stock)
- `GET /contact` → `ContactController@index` (stock)
- `GET /instructors` → `InstructorsController@instructors` (stock)
- `GET /cart` → `CartController@index` (stock, inside auth middleware)

---

## Controllers Analysis

### Web Controllers (57 files)
All are **stock RocketLMS controllers**. Notable:
- **No `LandingV1Controller.php`** — this needs to be created
- `CartManagerController.php` exists (21 KB) — slightly different size from siematplus (21.3 KB vs 21.1 KB)
- `PaymentController.php` — smaller than siematplus (12.5 KB vs 13.8 KB), missing `myfatoorah` and `payment-request GET` routes
- `PurchaseCodeController.php` — slightly larger (3.8 KB vs 3.7 KB)

---

## Asset Pipeline

### Current: Laravel Mix Only
```json
{
    "scripts": {
        "dev": "npm run development",
        "development": "mix",
        "watch": "mix watch",
        "prod": "npm run production",
        "production": "mix --production"
    }
}
```

### Missing (compared to siematplus)
- ❌ No `vite.config.js`
- ❌ No `tailwind.config.js`
- ❌ No `postcss.config.js`
- ❌ No `landing:dev` / `landing:build` npm scripts
- ❌ No `resources/css/` directory at all
- ❌ No `resources/js/landing_v1.js`
- ❌ No Tailwind CSS dependency
- ❌ No FlyonUI dependency
- ❌ No Iconify dependencies

### Present (stock)
- `webpack.mix.js` (not present in root — assets compiled to `public/assets/` via Mix manifest)
- `laravel-vite-plugin` in `devDependencies` but **not used** (no vite.config.js)

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

- **No `.git` directory** — Git is NOT initialized in this project
- No remote repository configured
- No commit history

---

## Database

- Two SQL dump files exist in the project root:
  - `quice-prod-db-u873288737_markaz.sql` (1.07 MB)
  - `u873288737_markaz.sql` (1.64 MB)
- Database name: `u873288737_markaz` (Hostinger format)
- These are production database dumps and should be removed from the project directory

---

## Files to Clean Up Before Git Init

| File / Pattern | Reason |
|----------------|--------|
| `.env` | Contains production credentials |
| `firebase-auth.json` | Contains private key |
| `quice-prod-db-u873288737_markaz.sql` | Production DB dump |
| `u873288737_markaz.sql` | Production DB dump |
| `vendor/` | Should be installed via `composer install` |
| `node_modules/` | Should be installed via `npm install` |
| `package-lock.json` | Can be regenerated (823 KB) |
