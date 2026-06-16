# Phase 0 — Security Fixes & Project Setup Changelog

## 1. Folder Rename
- Renamed `c:\xampp\htdocs\qice-project` to `c:\xampp\htdocs\qiec-project` via PowerShell to fix typo and align with domain `training.qiec.sa`.

## 2. Security Setup
- **`.gitignore` update:** Added explicit rules to ignore:
  - `_backups/` directory (for storing sensitive files safely)
  - `firebase-auth.json`
  - `.env` and `.env.*.bak`
  - `*.pem`, `*.sql`, `app/logs/*.log`
  - `deploy.ps1`, `deploy.bat`
  - `public/assets/`, `public/vendor/`, `public/mix-manifest.json` (auto-generated files)
  - `/database/regions/cities.json` (to respect GitHub 50MB limits)
- **Environment variables:** 
  - Copied the exposed production `.env` to `_backups/.env.production.bak`.
  - Replaced `.env` with a safe, local development version containing no sensitive credentials (empty DB password, log mail driver).
- **SQL Dumps:** 
  - Moved `quice-prod-db-u873288737_markaz.sql` and `u873288737_markaz.sql` from the project root into the `_backups/` directory.
- **Code Secrets Fix:** 
  - Removed an exposed Mapbox API key that was commented out in `app/Helpers/helper.php` (line 2460) which triggered GitHub's Secret Scanning push protection.

## 3. Git Initialization
- Ran `git init`.
- Committed the clean project base (RocketLMS v2.1) without any sensitive files or large assets.
- Added remote origin: `git@github.com:mahmoud-wahba-dev/qice-rocket-lms.git`.
- Pushed successfully to the `master` branch.

## 4. Documentation Updates
- Updated references in `ARCHITECTURE_SUMMARY.md` from `qice-project` to `qiec-project`.
- Logged all actions taken in this file (`PHASE0_CHANGELOG.md`).
