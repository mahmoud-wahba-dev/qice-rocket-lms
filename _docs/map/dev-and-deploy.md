# Development and deployment

## Local setup

| | |
|---|---|
| OS | Windows, XAMPP |
| URL | http://training.qiec.local/ (Apache vhost on port 80, docroot `public/`) |
| PHP | 8.2.12 ZTS with ionCube 15.5, at `C:\xampp\php\php.exe` |
| Database | MariaDB 10.4, DB `qiec` |
| Composer | `php composer.phar` |

Older docs describing Docker, port 8000 or `/tmp/qiec-docker` don't apply to this machine.

The license is bound to `training.qiec.local`. `localhost` redirects to `/purchase-code`. Opening `/purchase-code` directly shows the form, which is normal.

## Assets

```bash
npm install
npm run dev:full        # Vite dev server
npm run build:landing   # Vite production build to public/build
```

Copy Laravel Filemanager assets after a fresh install:

```bash
cp -r vendor/unisharp/laravel-filemanager/public/* public/vendor/laravel-filemanager/
```

## Database

There are 470+ migrations. Eleven were made idempotent; don't revert those. `2026_09_08_000001_create_purchase_code_table.php` is a local-only migration.

```bash
php artisan migrate
php artisan db:seed
php artisan qiec:seed-landing-demo
php artisan db:seed --class=StudentV1DemoDataSeeder   # test data only
```

## Environments

| Variable | Local | Testing | Production |
|---|---|---|---|
| File | `.env` | `.env.testing` (not created yet) | `.env` on the server |
| `APP_ENV` | `local` | `testing` | `production` |
| `APP_DEBUG` | `true` | `true` | **`false`** |
| `APP_URL` | `http://training.qiec.local` | `http://localhost` | `https://training.qiec.sa` (no trailing slash) |
| `MAIL_MAILER` | `log` | `array` | `smtp` |
| `DB_DATABASE` | `qiec` | `qiec_testing` | See server `.env` |

Run `php artisan config:clear` after editing `.env` on the server. Full checklist: `_docs/ENV_PRODUCTION_CHECKLIST.md`.

## Testing

`tests/Unit` and `tests/Feature/{Helpers,Http,Models,PanelV1,Quiz}`.

**Don't run PHPUnit yet.** `phpunit.xml` doesn't set a DB name and `.env.testing` doesn't exist, so tests fall back to `.env` and the dev `qiec` DB. `DatabaseTransactions` limits the damage but doesn't guarantee it.

One-time setup:

1. Create a `qiec_testing` database.
2. Create `.env.testing` (gitignored) with `APP_ENV=testing`, `DB_DATABASE=qiec_testing` and `MAIL_MAILER=array`.
3. Run `php artisan migrate --env=testing`.
4. Run `php vendor\bin\phpunit`, or add `--testsuite=Feature --testdox`.

Tests must use real queries (`Sale::where`, `Webinar::where`), not mock data. For manual checks, `StudentV1DemoDataSeeder` provides realistic data. On the original dev database, `User::find(2)` was a student with many purchases; confirm that before relying on it here.

## Deployment

**Every push to `master` deploys to production.** `.github/workflows/deploy.yml` also has a manual trigger.

The server is a CloudPanel VPS. The workflow:

1. Runs `npm ci` and `npm run build:landing` on GitHub, then packages `public/build`.
2. Uploads the package and connects over SSH.
3. Runs `artisan down`.
4. Runs `git fetch` and `git reset --hard origin/master`. Tracked files edited on the server are lost.
5. Runs `composer install`.
6. Removes `public/hot`, replaces `public/build`.
7. Syncs Laravel Filemanager and country flag assets to `public/vendor/`.
8. Runs `migrate --force`, `optimize:clear`, `view:cache`, `storage:link`.
9. Fixes file ownership, runs `artisan up`, and does a health check.

**CI doesn't do:**

- Run tests, so run them before pushing.
- Build Mix or deploy `public/assets/`. Upload changed legacy assets and images by hand.
- Skip anything for docs-only pushes. Any push runs the full deploy with maintenance mode.

**Local deploy:** `npm run deploy` runs `deploy.bat` (gitignored). It builds, runs `git add .`, pushes, deploys over SSH and uploads the build. The push also triggers CI, so both run. Check `git status` first, because `git add .` takes everything untracked.

**Before pushing:**

1. Show `git status` and the diff, and check for secrets.
2. Remember that new migrations run automatically in production.
3. Confirm the build passes: `npm run build:landing`.

Rollback: `_docs/PRODUCTION_ROLLBACK.md`. Mail: `_docs/PRODUCTION_MAIL.md`. Those files may contain sensitive details; don't copy them.

## Project status

All v1 areas are built and login redirects to them. What's left is final QA, and the items in `known-issues.md`.
