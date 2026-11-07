# Known issues

Found during the November 2026 project review. Nothing here has been fixed. Remove items as they're resolved.

## Security

- **Possible credentials in tracked docs.** `_docs/ENV_PRODUCTION_CHECKLIST.md`, `_docs/PRODUCTION_MAIL.md` and `README.md` were flagged by a pattern check and need a manual review. Several other `_docs/` files hold server or hosting details. `_docs/` is pushed to GitHub, so rotate anything real rather than just deleting it.

## Deployment

- **No checks before production.** `deploy.yml` deploys every `master` push without running tests.
- **Overlapping deploys.** `npm run deploy` deploys over SSH and also pushes, which triggers CI.
- **`deploy.bat` runs `git add .`,** which commits anything untracked in the working tree.
- **PHP version mismatch.** CI runs `php8.4`, while local is 8.2 and the project rule was 8.2. ionCube loaders are version-specific. Production's real version is unconfirmed; check it on the server.
- **`public/assets` isn't deployed** by CI.

## Testing

- `.env.testing` and `qiec_testing` don't exist, so PHPUnit would use the dev DB. See `dev-and-deploy.md`.
- `.phpunit.result.cache` from 2026-11-01 records 10 defects. That run probably used `qiec`. Current status is unknown.

## Build

- **Mix is likely broken.** `webpack.mix.js` needs `moment`, `daterangepicker`, `jquery.nicescroll` and fontawesome, which aren't in `package.json`, and `/tmp/stisla*`. The build itself wasn't run.
- **`vite` isn't declared directly** in `package.json`; it installs as a peer dependency of `laravel-vite-plugin`, so its version is set by the lockfile.
- **Tailwind `content` gaps:** see `frontend.md`.

## Behaviour

- `php artisan route:list` crashes (see `routes-and-controllers.md`).
- `/panel/*` remains reachable, with no redirect to v1.
- v1 has no language switcher, even though `POST /locale` exists.
- `dir="rtl"` is hardcoded, and much of the UI is hardcoded Arabic, so English is incomplete.
- `#0F3D36` is hardcoded in place of the `primary` token.
- Admin extra-description routes are duplicated in `routes/web.php` and `routes/custom_admin.php`.

## Stale docs

Trust the code over these:

| File | What's wrong |
|---|---|
| `routes/panel_v1.php` header comment | Says redirects remain on old dashboards |
| `README.md` | Docker setup, port 8000, says admin stays on legacy |
| `_docs/DEPLOYMENT.md` | Mixes retired shared hosting with the VPS |
| `_docs/*_CHECKLIST.md` | Retired hosting paths |

## Unverified

- Whether `routes/admin.php` overlaps other routes (encoded).
- How locale is applied to v1 admin pages.
