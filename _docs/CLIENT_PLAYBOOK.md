# Client Playbook — RocketLMS Custom Landing Layer

Reusable workflow for building a custom public website on RocketLMS for any client. Each client gets different Figma designs but the **same technical architecture**.

**Reference implementations:** siematplus → QIEC (this project)

---

## Architecture pattern

### Dual frontend

| Layer | Build tool | Used for |
|-------|------------|----------|
| `design_1` | Laravel Mix (prebuilt) | Admin panel, user panel, stock RocketLMS pages |
| `landing_v1` | Vite + Tailwind + FlyonUI | New public marketing site |

**Rule:** Never mix Bootstrap (design_1) with Tailwind on the same page. Wrap landing pages in `#landing-v1-app` — Tailwind is scoped via `important: "#landing-v1-app"` in `tailwind.config.js`.

### Data flow

```
Browser → routes/web.php → LandingV1Controller → RocketLMS models (Webinar, User, Cart, Order)
                         → landing_v1 Blade views → @vite CSS/JS
```

Cart and checkout reuse existing RocketLMS controllers (`CartController`, `CartManagerController`, `PaymentController`) — do not reimplement payment logic in the landing layer.

---

## Phase 0 — Security (mandatory first)

Before `git init` or first push:

1. Add to `.gitignore`: `.env`, `firebase-auth.json`, `*.sql`, `*.pem`, `deploy.bat`, `deploy.ps1`, `public/assets/`
2. Move production `.env` and SQL dumps to `_backups/` outside git
3. Create local `.env` with safe dev credentials
4. Grep codebase for hardcoded API keys before push (GitHub secret scanning will block)

See [PHASE0_CHANGELOG.md](PHASE0_CHANGELOG.md) for QIEC example.

---

## Phase 1 — Build pipeline

### Install dependencies

```json
// package.json devDependencies
"@iconify-json/tabler", "@iconify/tailwind", "autoprefixer", "postcss", "tailwindcss", "laravel-vite-plugin"

// dependencies
"flyonui"
```

### Config files (copy structure, adapt colors)

| File | Purpose |
|------|---------|
| `vite.config.js` | Inputs: `landing_v1.css`, `landing_v1.js` → output `public/build/` |
| `tailwind.config.js` | Content paths, FlyonUI plugin, CSS variable colors |
| `postcss.config.js` | tailwindcss + autoprefixer |

### npm scripts

```json
"dev:landing": "vite",
"build:landing": "vite build",
"deploy": "deploy.bat"
```

---

## Phase 2 — Copy landing layer

### Directory structure

```
resources/
├── css/landing_v1.css       ← design tokens (:root variables)
├── js/landing_v1.js         ← minimal JS (FlyonUI, cart drawer, etc.)
└── views/landing_v1/
    ├── layouts/             app.blade.php, navbar, footer
    ├── components/          course-card, page-hero, stats, etc.
    └── pages/               home, courses, cart, checkout, auth, ...

app/Http/Controllers/Web/LandingV1Controller.php
public/assets/landing_v1/img/   ← Figma exports (gitignored)
```

### Copy as-is (logic-heavy, no branding)

| File | Why reusable |
|------|--------------|
| `LandingV1Controller.php` | Generic RocketLMS queries |
| `courses.blade.php` + `courses_list.blade.php` | AJAX filtering |
| `course-details.blade.php` | Chapters, reviews, dynamic data |
| `cart.blade.php` | Guest + auth cart |
| `checkout.blade.php` | Payment channels |
| `instructors.blade.php` | Teacher listing with stats |
| `vite.config.js`, `postcss.config.js` | Identical across clients |

### Change per Figma (branding only)

| File | What to change |
|------|----------------|
| `landing_v1.css` | `:root` color tokens, font family |
| `app.blade.php` | `<title>`, meta, font link |
| `navbar.blade.php` | Logo, nav links |
| `footer.blade.php` | Contact info, social links, copy |
| `home.blade.php`, `about.blade.php`, `contact.blade.php` | Marketing copy, hero images |
| `auth/login.blade.php`, `auth/register.blade.php` | Logo, branding |
| `public/assets/landing_v1/img/` | All images from Figma export |

---

## Phase 3 — Routes and controller

### Route naming convention

Use `landing.v1.*` prefix — avoids conflicts with stock RocketLMS routes.

```php
use App\Http\Controllers\Web\LandingV1Controller;

Route::get('/', [LandingV1Controller::class, 'index'])->name('landing.v1.index');
Route::get('/courses', [LandingV1Controller::class, 'courses'])->name('landing.v1.courses');
// ... etc
```

### Comment out replaced stock routes

In `routes/web.php`, comment out stock routes that landing_v1 replaces:

- `HomeController@index` (/)
- `ContactController` (/contact)
- `InstructorsController` (/instructors)
- `CartController@index` inside auth group (/cart)

### Auth views

Point `LoginController` and `RegisterController` to `landing_v1.pages.auth.login` / `register`.

### Keep client-specific RocketLMS features

Do not remove routes the client needs (e.g. QIEC keeps `/events/*` and `/meeting-packages/*`).

---

## Phase 4 — Figma → Blade workflow

1. **Extract design tokens** — primary, secondary, accent colors → `landing_v1.css` `:root`
2. **Export images** — SVG logos, WebP heroes → `public/assets/landing_v1/img/`
3. **Build page by page** — start with layout (navbar/footer), then home, then data pages
4. **Prototype pages** — static Blade first if Figma shows layouts not yet wired to DB; mark as prototype in [CLIENT_*.md](CLIENT_QIEC.md)
5. **Wire dynamic pages last** — courses, course-details, instructors use existing controller methods

### CSS token pattern

```css
:root {
    --color-primary: #0f4c45;    /* from Figma */
    --color-secondary: #faf8f4;
    --color-gold: #e8d9c0;
    --font-cairo: "Cairo", sans-serif;
}
```

Tailwind uses these via `tailwind.config.js`:

```js
colors: {
    primary: 'var(--color-primary)',
    secondary: 'var(--color-secondary)',
    // ...
}
```

---

## Phase 5 — Deploy

1. Copy templates from [DEPLOY_TEMPLATE.md](DEPLOY_TEMPLATE.md)
2. Set SSH alias and `domains/<client>/public_html` path
3. Complete [DEPLOYMENT.md](DEPLOYMENT.md) checklist
4. Upload gitignored assets to server once
5. Run `npm run deploy`

---

## Per-client documentation

For each new client, create `_docs/CLIENT_<NAME>.md` with:

- Domain and hosting path
- Brand colors and font
- Route → Figma screen mapping
- Pages: dynamic vs static prototype
- TODO list
- Client-specific issues

Update [CHANGELOG.md](CHANGELOG.md) as work progresses.

---

## Common pitfalls

See [ISSUES_AND_LESSONS.md](ISSUES_AND_LESSONS.md) for the full list. Top 5:

1. Forgetting to grep for old client branding after copy
2. Wrong npm script name in deploy (`landing:build` vs `build:landing`)
3. Not uploading `public/assets/` to server (gitignored)
4. Leaving `X-Robots-Tag: noindex` in `.htaccess` at launch
5. Committing `.env` or SQL dumps before updating `.gitignore`

---

## Related docs

- [CLIENT_QIEC.md](CLIENT_QIEC.md) — this client's specifics
- [MIGRATION_REUSE_PLAN.md](MIGRATION_REUSE_PLAN.md) — original siematplus → QIEC plan
- [ARCHITECTURE_SUMMARY.md](ARCHITECTURE_SUMMARY.md) — full technical reference
