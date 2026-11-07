# QIEC Training Platform

Shared instructions for every coding agent on this project (Claude Code, Cursor, OpenCode).

RocketLMS v2.1 (Laravel 9) rebranded for QIEC (Quality and Innovation in Education Center). Arabic-first, RTL.

- **Local:** http://training.qiec.local/ (Windows, XAMPP, `c:\xampp\htdocs\qiec-project`)
- **Production:** https://training.qiec.sa/
- **Repo:** `mahmoud-wahba-dev/qice-rocket-lms`, branch `master`

All new work is in the **v1 layer**: `routes/panel_v1.php`, `app/Http/Controllers/PanelV1/`, `resources/views/{panel_v1,landing_v1}/`. The `design_1` layer and `/panel/*` are legacy.

## Docs

All project docs live in `_docs/`, for every agent. Don't create docs in `.claude/`, `.cursor/`, `.opencode/` or the project root.

- `_docs/map/`: current reference docs. Update them when behaviour changes.
- `_docs/plans/`: plans written by any agent, one file per feature, named `<feature>.plan.md`.
- Tool entry files stay in the root because tools only load them from there. `AGENTS.md` holds the content; `CLAUDE.md` just imports it.

## Hard rules

1. **Pushing to `master` deploys to production** (maintenance mode, `git reset --hard`, `migrate --force`). No commit or push without explicit permission. Before committing, show `git status`/diff and check for secrets. Never `git add .`.
2. **Never touch ionCube-encoded or license code:** `app/Http/Kernel.php`, `routes/admin.php`, `app/Models/PurchaseCode.php`, `app/Services/*LicenseService.php`, `app/Http/Middleware/*LicenseCheck.php`, `app/Providers/*License*Provider.php`, `app/Http/Controllers/Web/PurchaseCodeController.php`, or anything containing `//00507`. Encoded files cannot be read or edited.
3. **Don't run PHPUnit until `.env.testing` exists.** It doesn't exist now, so tests would run against the dev `qiec` DB. Setup: `_docs/map/dev-and-deploy.md`.
4. **Never copy or commit secrets:** `.env`, `.env.testing`, `firebase-auth.json`, `*.pem`, `*.sql`, `_backups/`, `storage/logs/*`. Some files in `_docs/` and `README.md` hold credentials or server details; never repeat them.
5. **No mock data in production.** Numbers and lists come from the DB. `AdminMockData::shell()` is an exception: despite the name it is live admin nav code. `StudentV1DemoDataSeeder` is for test data only.
6. **`panel_v1` and `design_1` never import each other.** New screens go only in the v1 layer.
7. **Every v1 controller action must check the role.** The `panel` middleware admits all non-admin roles; isolation lives in the controllers (see `_docs/map/v1-conventions.md`).
8. **Ask first** when unsure about payments, permissions, or licensing. Don't change PHP/ionCube versions without discussion.
9. **Never use the browser's `alert()`, `confirm()` or `prompt()` (or `onsubmit="return confirm(...)"`).** Use our shared UI, which follows the brand colours and RTL:
   - **Confirm an action** (delete, archive, end session...): add `data-confirm="message"` to the `<form>` or button/link, optionally `data-confirm-title`, `data-confirm-ok` (button label) and `data-confirm-tone="danger|warning|primary"`. In `actions-dropdown` items use `confirm`, `confirm_title`, `confirm_ok` and `tone`. From JS: `if (await window.confirmModal({title, message, ok, tone})) {...}`. Component: `resources/views/components/v1/confirm-modal.blade.php` (already included in every v1 layout via `components/v1/flash`).
   - **Tell the user something** (success/error): the toast, `window.showCartToast(title, msg, 'success'|'error')`, or `session('toast')` from a controller.
   - **Show a bigger message** (like the instructor "awaiting approval" or "seats are full" dialogs): follow `landing_v1/components/instructor-pending-modal.blade.php`. Reuse these components; don't write a new dialog style. This applies to every AI agent (Claude, Cursor, OpenCode).

## Environment

PHP 8.2.12 + ionCube 15.5 and MariaDB 10.4 (DB `qiec`), Apache on port 80 with docroot `public/`. There is no Docker on this machine: ignore `docker exec` commands in older docs and run the commands directly.

`php` and `composer` may not be on PATH. Use `C:\xampp\php\php.exe` and `php composer.phar`.

```bash
php artisan view:clear            # after Blade changes that don't show
php artisan config:clear          # after .env changes
php -l path/to/File.php           # after adding a `use` import
npm run dev:full                  # Vite dev server (writes public/hot)
npm run build:landing             # production Vite build
```

- The license is bound to `training.qiec.local`. Using `localhost` redirects to `/purchase-code`, so always use the vhost.
- `php artisan route:list` crashes here (`ForumController` aborts in its constructor). Search `routes/` instead.
- If `public/hot` exists, `@vite` loads from the dev server. If styles vanish with Vite stopped, delete `public/hot`.

## Where to edit

| Area | Routes | Controller | Views |
|---|---|---|---|
| Public site, cart, auth | `routes/web.php` | `Web/LandingV1Controller`, `Auth/*` | `landing_v1/` |
| Student | `routes/panel_v1.php` | `PanelV1/StudentController` | `panel_v1/student/` |
| Course player | `routes/panel_v1.php` | `PanelV1/CoursePlayerController` | `panel_v1/student/course-player/` |
| Instructor | `routes/panel_v1.php` | `PanelV1/InstructorController` | `panel_v1/instructor/` |
| Organization | `routes/panel_v1.php` | `PanelV1/OrganizationController` | `panel_v1/organization/` |
| Admin | `routes/panel_v1.php` | `PanelV1/Admin/{Education,Sales,Marketing,System,Settings}Controller` | `panel_v1/admin/pages/{education,sales,marketing,system}/` |
| Login redirect | | `panelV1HomeUrl()` in `app/Helpers/helper.php` | |
| Certificates | | `app/Mixins/Certificate/MakeCertificate.php` | `panel_v1/certificates/` |

Controllers are under `app/Http/Controllers/`, views under `resources/views/`.

## Read when needed

Read only the file for the area you're working on, instead of re-exploring the code:

| Task | Read |
|---|---|
| Writing any v1 route, controller, view or style (incl. confirm/alert rule) | `_docs/map/v1-conventions.md` |
| How the layers, login and legacy overlap fit together | `_docs/map/architecture.md` |
| Adding or changing routes, controllers, pages | `_docs/map/routes-and-controllers.md` |
| Blade, Tailwind, Vite, RTL, translations | `_docs/map/frontend.md` |
| Models, tables, roles, helpers, integrations | `_docs/map/domain-model.md` |
| Local setup, tests, deployment, production env | `_docs/map/dev-and-deploy.md` |
| Risks, known bugs, stale docs | `_docs/map/known-issues.md` |

`README.md` and the older files directly in `_docs/` predate the current setup. Trust the code and `_docs/map/` over them.

## Working style

- Keep changes to the smallest scope; don't delete unrelated lines. Edit existing files rather than adding new ones.
- In Blade, use `@php $x = ...; @endphp`, not `@php($x = ...)` (causes `unexpected token "@"`).
- Verify before handing over, using realistic DB data. When evidence conflicts, state the possibilities and trust local evidence.
- Replies: short and direct, no praise, with file references.
