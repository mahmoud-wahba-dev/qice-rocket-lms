# Architecture

RocketLMS v2.1 is a commercial Laravel LMS. QIEC keeps its backend (models, payments, mixins) and replaces the whole UI with a new layer called **v1**.

## Layers

| Layer | What it is | Routes | Assets | Status |
|---|---|---|---|---|
| `design_1` | Stock RocketLMS UI (Bootstrap/Stisla, jQuery) | `routes/panel.php`, `routes/admin.php`, parts of `routes/web.php` | Mix, `public/assets/` | Legacy, still reachable |
| `landing_v1` | New public site, auth and account pages | `routes/web.php` (`landing.v1.*`) | Vite, Tailwind, FlyonUI | Live |
| `panel_v1` | New panels: student, instructor, organization, admin | `routes/panel_v1.php` (`/v1`, `panel.v1.*`) | Vite, shares landing CSS/JS | Live |

## Route loading

`RouteServiceProvider::map()` loads, in order:

1. `routes/api.php` (`/api`, includes `routes/api/{auth,guest,instructor,user}.php`)
2. `routes/web.php`
3. `routes/admin.php` (ionCube-encoded), then `routes/custom_admin.php`
4. `routes/panel.php`
5. `routes/panel_v1.php`

There's no route cache locally, so route edits apply immediately.

## Request flow

1. Apache serves `public/index.php`. The local vhost points at `public/` directly; the root `.htaccess` rewrites to `public/` for hosts that serve the project root.
2. The `web` group runs, including `UserLocale`, `SessionValidity`, `EdgeCachePrimer` and `LicenseCheck`. `app/Http/Kernel.php` is encoded, but its registered groups can be read at runtime through the router.
3. Route middleware runs (see `routes-and-controllers.md`).
4. The controller resolves the user and role, queries Eloquent, and returns `view()` with plain arrays.
5. Blade renders inside `#landing-v1-app`, with `@vite` assets.

## Login flow

1. `POST /login` goes to `Auth/LoginController`.
2. Admins go straight to `panelV1HomeUrl()`.
3. Other users first try `resolveLandingAuthRedirect()` from `LandingAuthRedirectTrait` (checkout, free enrolment or cart intent), then fall back to `panelV1HomeUrl()`.
4. Registration uses the same helper.

`panelV1HomeUrl()` in `app/Helpers/helper.php` **already sends every role to v1**:

| Role | Destination |
|---|---|
| Admin | `panel.v1.admin.education.home` |
| Teacher | `panel.v1.instructor.home` |
| Organization | `panel.v1.organization.home` |
| Student | `panel.v1.student.home` |
| None or unknown | `/panel` |

Older comments and docs saying redirects remain on legacy are out of date.

Logged-in users who open `/login` go to `/`, not their panel (`RedirectIfAuthenticated`).

Stock Laravel `auth` middleware is unsafe for guests here: `route('login')` resolves to the API login in `routes/api/auth.php`. Use `web.auth` or `panel`.

## Legacy and v1 overlap

There are no URI conflicts between `/v1` and the other readable route files. `routes/admin.php` is encoded and can't be inspected by reading it.

**Parallel pages (both work):**

| Legacy | v1 |
|---|---|
| `/panel/*` | `/v1/*` |
| `/blog` | `/blogs` |
| `/cart/checkout` | `/checkout` |

`/organizations` is still `design_1`.

**Redirects to v1:** `/course/learning/{slug}` goes to the v1 player (`LearningPageController`). `/course/{slug}` 301s to `/webinar/{slug}`.

**v1 depending on legacy:**

- Free enrolment posts to `/course/{slug}/free` (`LandingAuthRedirectTrait`).
- A few v1 views link to `panel/*` paths (notifications, meeting settings, registration packages).
- Non-teacher public profiles stay legacy (`User.php`).
- `PanelV1/Admin/SettingsController` wraps the legacy `Admin\SettingsController`.
- `PanelV1/Admin/SalesController` calls `Web\PaymentController::setPaymentAccounting`.

**Duplicate routes:** the admin extra-description routes exist in both `routes/web.php` and `routes/custom_admin.php`, using the same controller.

## Encoded code

ionCube-encoded files can't be read or edited, and include `app/Http/Kernel.php` and `routes/admin.php`. To learn what `Kernel.php` registers, query the router at runtime with `app('router')->getMiddleware()` and `getMiddlewareGroups()`.
