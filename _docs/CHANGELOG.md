# Project Changelog

Rolling log of major changes to the QIEC project. For security setup details see [PHASE0_CHANGELOG.md](PHASE0_CHANGELOG.md).

---

## Phase 0 — Security and project setup

**Status:** Complete

- Renamed folder `qice-project` → `qiec-project`
- Updated `.gitignore` (`.env`, SQL dumps, firebase, deploy scripts, `public/assets/`)
- Moved sensitive files to `_backups/`
- Created safe local `.env`
- Removed exposed API key from `helper.php` (GitHub secret scanning)
- Initialized git, pushed to `git@github.com:mahmoud-wahba-dev/qice-rocket-lms.git`

Details: [PHASE0_CHANGELOG.md](PHASE0_CHANGELOG.md)

---

## Phase 1 — Build pipeline

**Status:** Complete

- Added Vite, Tailwind CSS 3, FlyonUI, Iconify
- Created `vite.config.js`, `tailwind.config.js`, `postcss.config.js`
- Added npm scripts: `dev:landing`, `build:landing`, `deploy`
- Created `resources/css/landing_v1.css` and `resources/js/landing_v1.js`

---

## Phase 2 — Landing v1 layer

**Status:** Complete (core pages); prototypes remain

- Copied and adapted `landing_v1` views from siematplus
- Created `LandingV1Controller.php` (~451 lines)
- Added landing routes block in `routes/web.php`
- Pointed auth controllers to `landing_v1` login/register views
- Commented out stock routes replaced by landing layer

### QIEC-specific pages (beyond siematplus)

- `/workshops` — workshops listing (prototype)
- `/blogs`, `/blog-details` — news section (prototype)
- `/courses-paid` — paid courses listing (prototype)
- `/course-details-free`, `/course-details-paid` — course detail layouts (prototype)

---

## Phase 3 — QIEC branding

**Status:** In progress

- Applied QIEC color tokens (`#0f4c45` primary, Cairo font)
- Updated navbar/footer with QIEC copy (Arabic)
- Removed siematplus title and email references from layout, footer, contact
- Footer copyright: "Quality & Excellence Center for Training"

### Remaining

- Remove `X-Robots-Tag: noindex` before public launch
- Wire prototype pages to database

---

## Phase 4 — Deployment setup

**Status:** Scripts ready; first server deploy pending

- Created `deploy.bat` and `deploy.ps1` (gitignored) for `training.qiec.sa`
- Fixed script issues from siematplus copy (path, npm script name, corrupted bat file)
- Documented Hostinger setup in [DEPLOYMENT.md](DEPLOYMENT.md)
- Added [DEPLOY_TEMPLATE.md](DEPLOY_TEMPLATE.md) for future clients

### Pending user action

- Complete Hostinger SSH verification checklist ([DEPLOYMENT.md](DEPLOYMENT.md))
- First-time server setup (git clone, `.env`, composer, asset upload)
- Test `npm run deploy` end-to-end

---

## Phase 5 — Documentation refresh

**Status:** Complete

- Created `_docs/README.md` (master index)
- Created `CLIENT_PLAYBOOK.md`, `CLIENT_QIEC.md`, `ISSUES_AND_LESSONS.md`
- Created `DEPLOYMENT.md`, `DEPLOY_TEMPLATE.md`
- Updated `ARCHITECTURE_SUMMARY.md` and `COMPARISON_SIEMATPLUS_VS_QIEC.md`

---

## Git history note

Recent commits use generic messages (`update`). Consider more descriptive messages going forward, e.g.:

- `feat(landing): add courses-paid prototype page`
- `fix(branding): replace siematplus emails with QIEC contact`
- `docs: add deployment guide for Hostinger`

---

## Related docs

- [README.md](README.md) — documentation index
- [CLIENT_QIEC.md](CLIENT_QIEC.md) — current client status and TODOs
- [MIGRATION_REUSE_PLAN.md](MIGRATION_REUSE_PLAN.md) — original migration plan
