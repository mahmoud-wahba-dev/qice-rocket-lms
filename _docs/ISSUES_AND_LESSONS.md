# Issues and Lessons Learned

Problems encountered during the siematplus → QIEC migration. Read this before starting the next client project.

---

## Security

### Exposed `.env` and SQL dumps in project root

**What happened:** Production `.env` with database passwords and two MySQL dump files (`*.sql`) were in the project root before git init.

**Impact:** Credentials could be committed to GitHub.

**Fix:** Phase 0 — updated `.gitignore`, moved files to `_backups/`, created safe local `.env`.

**Prevention:** Always run security checklist in [CLIENT_PLAYBOOK.md](CLIENT_PLAYBOOK.md) Phase 0 before `git init`.

---

### GitHub secret scanning blocked push

**What happened:** A commented Mapbox API key in `app/Helpers/helper.php` triggered GitHub push protection.

**Fix:** Removed the exposed key from source.

**Prevention:** Run `git grep -i "api_key\|secret\|password"` before first push.

---

## Project organization

### Folder name typo (`qice-project`)

**What happened:** Folder was named `qice-project` instead of `qiec-project`, mismatching domain `training.qiec.sa`.

**Fix:** Renamed before git init. See [FOLDER_RENAME_RECOMMENDATION.md](FOLDER_RENAME_RECOMMENDATION.md).

**Prevention:** Align folder name, repo name, and domain from day one.

---

### Documentation drift

**What happened:** `_docs/ARCHITECTURE_SUMMARY.md` and `COMPARISON` still described "no landing layer" after landing_v1 was built.

**Fix:** This documentation refresh (CHANGELOG, CLIENT_QIEC, updated architecture docs).

**Prevention:** Update `CHANGELOG.md` after each major phase; link docs from `_docs/README.md`.

---

## Migration from siematplus

### Siematplus branding left in views

**What happened:** After copying Blade files, default page title was still `Siemat Plus` and emails were `info@siematplus.com`.

**Files affected:** `layouts/app.blade.php`, `layouts/footer.blade.php`, `pages/contact.blade.php`

**Fix:** Replaced with QIEC branding (`QIEC Training`, `contact@training.qiec.sa`).

**Prevention:** After every client copy, run:

```powershell
git grep -i "siematplus\|siemat plus"
```

Replace all matches before deploy.

---

### Copied deploy scripts pointed to wrong project

**What happened:** `deploy.bat` and `deploy.ps1` from siematplus still used:
- `domains/siematplus.com/public_html`
- `npm run landing:build` (wrong script name)

**Additional issue:** `deploy.bat` had PowerShell content appended — corrupted file.

**Fix:** Split into clean `.bat` and `.ps1`; updated path to `training.qiec.sa`; use `build:landing`.

**Prevention:** Use [DEPLOY_TEMPLATE.md](DEPLOY_TEMPLATE.md) and customize placeholders per client.

---

### npm script name mismatch

| siematplus | QIEC |
|------------|------|
| `landing:dev` / `landing:build` | `dev:landing` / `build:landing` |

**Prevention:** After copying `package.json` scripts, grep deploy scripts for the correct names.

---

## Assets and build

### `public/assets/` is gitignored

**What happened:** Landing images (`public/assets/landing_v1/img/`) and stock theme (`public/assets/design_1/`) are not in git.

**Impact:** Fresh clone or deploy via git alone shows broken images and broken admin panel.

**Fix:** Upload assets manually to Hostinger via File Manager or SFTP after first deploy.

**Prevention:** Document in [DEPLOYMENT.md](DEPLOYMENT.md); keep a local backup of assets outside git.

---

### `webpack.mix.js` missing

**What happened:** `npm run prod` (Laravel Mix) cannot rebuild `design_1` assets — `webpack.mix.js` is not in the repo.

**Impact:** Cannot recompile stock RocketLMS theme from source.

**Workaround:** Keep existing prebuilt `public/assets/design_1/` — do not delete.

**Prevention:** Document dual pipeline: Vite for landing_v1 only; Mix assets are frozen/prebuilt.

---

### Vite build output must be committed

**What happened:** `public/build/` contains hashed CSS/JS from `npm run build:landing`. Server deploy pulls from git — if build output is not pushed, production has stale or missing styles.

**Prevention:** `npm run deploy` runs `build:landing` before push.

---

## SEO and launch

### `X-Robots-Tag: noindex` in root `.htaccess`

**What happened:** Root `.htaccess` sets `Header always set X-Robots-Tag "noindex, follow"` — blocks search engine indexing.

**Status:** Still present — intentional during development.

**Action before launch:** Remove or comment out the `X-Robots-Tag` block in `.htaccess`.

---

## Figma / UI development

### Static prototype pages without DB wiring

**What happened:** Figma designs for `courses-paid`, `course-details-free/paid`, `blogs`, `workshops` were built as static Blade pages with hardcoded content.

**Impact:** Pages look correct but show placeholder data.

**Next step:** Wire to `Webinar` model or add blog CMS when content is ready. Mark prototypes in [CLIENT_QIEC.md](CLIENT_QIEC.md).

**Prevention:** In client doc, label each route as **dynamic** or **prototype** from the start.

---

### Tailwind vs Bootstrap conflict

**What happened:** RocketLMS stock pages use Bootstrap; landing uses Tailwind.

**Fix:** `#landing-v1-app` wrapper + `important: "#landing-v1-app"` in Tailwind config scopes styles.

**Prevention:** Never load landing Tailwind on admin/panel pages; never use stock Bootstrap classes inside landing Blade without scoping.

---

## Deployment

### No CI/CD — local scripts only

**Decision:** QIEC uses `npm run deploy` (local build + git push + SSH pull), not GitHub Actions.

**Requirement:** Developer machine must have SSH key configured for Hostinger.

**See:** [DEPLOYMENT.md](DEPLOYMENT.md) for SSH verification checklist.

---

### Hostinger server

**Confirmed:** QIEC uses a **separate server** from siematplus.

| | siematplus | QIEC |
|---|-----------|------|
| IP | 153.92.220.73 | 82.197.83.145 |
| Port | 65002 | 65002 |
| SSH user | u632024281 | u873288737 |
| Config alias | `hostinger` | `hostinger-qiec` |

---

## Quick reference: grep before deploy

```powershell
# Old client branding
git grep -i "siematplus"

# Wrong deploy paths
git grep -i "siematplus.com"

# Secrets
git grep -iE "api_key|secret_key|password\s*="

# Wrong npm script in deploy
git grep "landing:build"
```

---

## Related docs

- [CLIENT_PLAYBOOK.md](CLIENT_PLAYBOOK.md) — prevention checklist
- [DEPLOYMENT.md](DEPLOYMENT.md) — deploy troubleshooting
- [PHASE0_CHANGELOG.md](PHASE0_CHANGELOG.md) — security fixes log
