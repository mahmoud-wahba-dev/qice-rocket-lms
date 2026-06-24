# Migration & Reuse Plan — Siematplus → QIEC

> **Note:** This was the original migration plan written at project start. For **current progress and status**, see [CLIENT_QIEC.md](CLIENT_QIEC.md) and [CHANGELOG.md](CHANGELOG.md). For the reusable process on future clients, see [CLIENT_PLAYBOOK.md](CLIENT_PLAYBOOK.md).

## Goal
Replicate the proven custom landing layer architecture from siematplus into the qiec project, adapting branding/design while keeping the same technical structure and workflow.

---

## Phase 0: Pre-Migration Security Fixes (MANDATORY FIRST)

> ⚠️ **Do this BEFORE `git init`** — otherwise sensitive files get committed.

### 0.1 Update `.gitignore`
Add the following entries to `c:/xampp/htdocs/qice-project/.gitignore`:

```gitignore
# === SECURITY: Never commit these ===
/.env
/firebase-auth.json
*.pem
*.sql
app/logs/*.log

# === Build artifacts ===
/storage/framework/views

# === Deploy scripts (contain server paths) ===
/deploy.ps1
/deploy.bat
```

### 0.2 Move sensitive files out of project root
```
quice-prod-db-u873288737_markaz.sql  → move to a backup folder outside project
u873288737_markaz.sql                → move to a backup folder outside project
```

### 0.3 Create safe local `.env`
Create a new `.env` based on siematplus template with:
- `APP_NAME=qiec`
- `APP_ENV=local`
- `APP_DEBUG=true`
- `APP_URL=http://qiec.local`
- `DB_DATABASE=qiec` (empty local DB)
- `DB_USERNAME=root`
- `DB_PASSWORD=` (empty for local XAMPP)
- Keep all other keys as placeholders

### 0.4 Initialize Git
```bash
git init
git add .
git commit -m "initial: qiec project base (RocketLMS v2.1)"
```

---

## Phase 1: Install Build Dependencies

### 1.1 Add Tailwind + Vite + FlyonUI packages to `package.json`

**New devDependencies to add:**
```json
{
    "@iconify-json/tabler": "^1.2.35",
    "@iconify/tailwind": "^1.2.0",
    "autoprefixer": "^10.5.0",
    "postcss": "^8.5.14",
    "tailwindcss": "^3.4.19"
}
```

**New dependencies to add:**
```json
{
    "flyonui": "^1.3.0"
}
```

**New npm scripts to add:**
```json
{
    "landing:dev": "vite",
    "landing:build": "vite build",
    "deploy": "deploy.bat"
}
```

### 1.2 Run `npm install`

### 1.3 Create config files

#### `vite.config.js` (copy from siematplus, identical)
```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/landing_v1.css', 'resources/js/landing_v1.js'],
            refresh: true,
        }),
    ],
});
```

#### `tailwind.config.js` (copy from siematplus, identical structure — colors will change later)
```js
const { addDynamicIconSelectors } = require('@iconify/tailwind');

function buildPxScale(start, end, step = 1, unit = 'rem', factor = 16) {
    const scale = {};
    for (let value = start; value <= end; value += step) {
        scale[`${value}px`] = `${value / factor}${unit}`;
    }
    return scale;
}

module.exports = {
    content: [
        "./resources/views/landing_v1/**/*.blade.php",
        "./resources/js/landing_v1.js",
        "./node_modules/flyonui/dist/js/*.js",
    ],
    important: "#landing-v1-app",
    theme: {
        extend: {
            fontFamily: { ibm: ['var(--font-ibm)'] },
            fontSize: { ...buildPxScale(10, 90) },
            colors: {
                primary: 'var(--color-primary)',
                secondary: 'var(--color-secondary)',
                gold: 'var(--color-gold)',
                blue: 'var(--color-blue)',
                black: 'var(--color-black)',
                '77': 'var(--color-77)',
                'f7': 'var(--color-f7)',
                'e3': 'var(--color-e3)',
                'card-text': 'var(--color-card-text)',
            },
            borderRadius: { ...buildPxScale(4, 100) },
            boxShadow: { glow: "0 0 60px rgba(34, 211, 238, 0.15)" },
        },
    },
    plugins: [
        require('flyonui'),
        require('flyonui/plugin'),
        addDynamicIconSelectors({ prefix: 'icon' }),
    ],
};
```

#### `postcss.config.js` (copy from siematplus, identical)
```js
module.exports = {
    plugins: {
        tailwindcss: {},
        autoprefixer: {},
    },
};
```

---

## Phase 2: Create Custom Landing Layer Structure

### 2.1 Create directory structure
```
resources/
├── css/
│   └── landing_v1.css          ← NEW (copy from siematplus, change colors)
├── js/
│   └── landing_v1.js           ← NEW (copy from siematplus, adapt)
└── views/
    └── landing_v1/             ← NEW
        ├── layouts/
        │   ├── app.blade.php   ← Copy from siematplus, change title/branding
        │   ├── navbar.blade.php← Copy from siematplus, change logo/links
        │   └── footer.blade.php← Copy from siematplus, change branding
        ├── components/
        │   ├── cart-drawer-body.blade.php   ← Copy
        │   ├── cart-drawer-empty.blade.php  ← Copy
        │   ├── course-card.blade.php        ← Copy
        │   ├── page-hero.blade.php          ← Copy
        │   ├── partners-carousel.blade.php  ← Copy (change partner logos)
        │   └── stats.blade.php              ← Copy
        └── pages/
            ├── home.blade.php               ← Copy, redesign content
            ├── about.blade.php              ← Copy, rewrite content
            ├── contact.blade.php            ← Copy, update contact info
            ├── courses.blade.php            ← Copy (logic reusable as-is)
            ├── courses_list.blade.php       ← Copy (AJAX partial, reusable)
            ├── course-details.blade.php     ← Copy (logic reusable as-is)
            ├── cart.blade.php               ← Copy (reusable as-is)
            ├── checkout.blade.php           ← Copy (reusable as-is)
            ├── instructors.blade.php        ← Copy (reusable as-is)
            └── auth/
                ├── login.blade.php          ← Copy, change branding
                └── register.blade.php       ← Copy, change branding
```

### 2.2 Files that can be copied as-is (no changes needed)
These are logic-heavy files with no client-specific branding:
- `courses.blade.php` — filtering/listing logic
- `courses_list.blade.php` — AJAX partial
- `course-details.blade.php` — course detail with chapters/reviews
- `cart.blade.php` — cart logic with guest support
- `checkout.blade.php` — payment channel logic
- `instructors.blade.php` — instructor listing logic
- All components in `components/` (except partners-carousel content)

### 2.3 Files that need branding changes
- `app.blade.php` — Change `<title>` default, meta description, possibly font
- `navbar.blade.php` — Logo, navigation links, brand name
- `footer.blade.php` — Brand info, contact details, social links
- `landing_v1.css` — Color tokens in `:root` (design-specific)
- `home.blade.php` — Hero section content, marketing copy
- `about.blade.php` — Company-specific content
- `contact.blade.php` — Contact information, address, phone
- `login.blade.php` / `register.blade.php` — Logo and branding only

---

## Phase 3: Create Controller

### 3.1 Copy `LandingV1Controller.php`
From: `c:/xampp/htdocs/rocketlms/app/Http/Controllers/Web/LandingV1Controller.php`
To: `c:/xampp/htdocs/qice-project/app/Http/Controllers/Web/LandingV1Controller.php`

**Changes needed:**
- Arabic text strings (page titles) — adapt to qiec branding if different
- That's it. All model queries are generic RocketLMS queries.

### 3.2 Add `use` statement to `routes/web.php`
```php
use App\Http\Controllers\Web\LandingV1Controller;
```

---

## Phase 4: Add Custom Routes to `web.php`

### 4.1 Comment out stock routes that will be replaced
In the main `Web` namespace group, comment out:
```php
// Route::get('/', 'HomeController@index');           // replaced by landing_v1
// Route::get('/classes', 'ClassesController@index'); // replaced by /courses
// Route::group(['prefix' => 'contact'], ...);        // replaced by landing_v1
// Route::group(['prefix' => 'instructors'], ...);    // replaced by landing_v1
```

Also comment out the stock cart route inside the `web.auth` group:
```php
// Route::get('/', 'CartController@index');  // replaced by landing_v1 cart
```

### 4.2 Add Landing V1 routes block
Append before the closing `});` of the main Web group:

```php
// ── Landing V1 — primary public routes ──────────────────────────────────
Route::get('/',            [LandingV1Controller::class, 'index'])->name('landing.v1.index');
Route::get('/about',       [LandingV1Controller::class, 'about'])->name('landing.v1.about');
Route::get('/contact',     [LandingV1Controller::class, 'contact'])->name('landing.v1.contact');
Route::get('/courses',     [LandingV1Controller::class, 'courses'])->name('landing.v1.courses');
Route::get('/instructors', [LandingV1Controller::class, 'instructors'])->name('landing.v1.instructors');
Route::get('/cart',        [LandingV1Controller::class, 'cart'])->name('landing.v1.cart');
Route::match(['get', 'post'], '/checkout', [LandingV1Controller::class, 'checkout'])->name('landing.v1.checkout');
Route::get('/webinar/{slug}', [LandingV1Controller::class, 'courseDetails'])->name('landing.v1.course-details');

Route::get('/landing-v1/login',    fn() => redirect('/login'))->name('landing.v1.login');
Route::get('/landing-v1/register', fn() => redirect('/register'))->name('landing.v1.register');
```

### 4.3 Keep qiec-specific routes
Do NOT remove:
- `/events/*` routes — keep them active
- `/meeting-packages/*` routes — keep them active
- `custom_admin.php` include — keep it

---

## Phase 5: Design Adaptation

### 5.1 Update CSS color tokens
In `resources/css/landing_v1.css`, update `:root` variables:
```css
:root {
    --color-primary:   #______;  /* ← qiec primary color (from Figma) */
    --color-secondary: #______;  /* ← qiec secondary color */
    --color-gold:      #______;  /* ← accent color */
    /* ... other tokens ... */
}
```

### 5.2 Update layout `<title>` default
In `app.blade.php`:
```html
<title>{{ $pageTitle ?? 'QIEC Training' }}</title>
```

### 5.3 Update font (if different)
If qiec uses a different font, update both:
- The `<link>` tag in `app.blade.php`
- The `--font-ibm` variable (rename if needed)
- The `fontFamily` key in `tailwind.config.js`

---

## Phase 6: Deploy Pipeline

### 6.1 Create `deploy.bat`
Copy from siematplus, change:
```diff
-ssh hostinger "cd domains/siematplus.com/public_html && ..."
+ssh hostinger "cd domains/training.qiec.sa/public_html && ..."
```

### 6.2 Create `deploy.ps1`
Same changes as deploy.bat.

### 6.3 Add SSH config
Add to `~/.ssh/config` a qiec-specific alias if the Hostinger server is different.

---

## Phase 7: Verification Checklist

- [ ] `npm run landing:dev` starts Vite without errors
- [ ] Homepage loads at `http://qiec.local` with custom design
- [ ] All 10 landing pages render correctly
- [ ] Cart works for both guest and authenticated users
- [ ] Checkout payment flow works
- [ ] `npm run landing:build` produces production assets
- [ ] Git repo has no sensitive files committed
- [ ] Deploy script works to Hostinger

---

## Reusable Components from Siematplus

### Fully Reusable (copy without changes)
| Component | Why |
|-----------|-----|
| `LandingV1Controller.php` | Generic RocketLMS model queries, no client-specific code |
| `cart.blade.php` | Pure shopping cart logic |
| `checkout.blade.php` | Payment channel rendering |
| `courses.blade.php` | Category filtering + AJAX |
| `courses_list.blade.php` | AJAX partial |
| `course-details.blade.php` | Chapter/review rendering |
| `instructors.blade.php` | Instructor listing |
| All `components/*.blade.php` | Reusable UI components |
| `vite.config.js` | Identical config |
| `postcss.config.js` | Identical config |
| `tailwind.config.js` | Same structure (only colors change) |

### Needs Branding Changes Only
| Component | What to change |
|-----------|---------------|
| `app.blade.php` | Title, font link, meta |
| `navbar.blade.php` | Logo, brand name |
| `footer.blade.php` | Brand info, links |
| `landing_v1.css` | Color tokens in `:root` |
| `home.blade.php` | Hero content, marketing copy |
| `about.blade.php` | Company info |
| `contact.blade.php` | Contact details |
| `login.blade.php` | Logo/branding |
| `register.blade.php` | Logo/branding |

### Architecture Patterns to Reuse
| Pattern | Description |
|---------|-------------|
| `#landing-v1-app` wrapper | Scopes Tailwind to avoid Bootstrap conflicts |
| Dual build pipeline (Vite + Mix) | Custom layer uses Vite, core uses Mix |
| CSS custom properties for colors | Easy per-client theming |
| `landing.v1.*` route naming | Consistent, conflict-free route names |
| Deploy script workflow | Build → Push → SSH Pull |
| `_docs/` folder structure | PROJECT_MEMORY + PROJECT_RULES + CLIENT doc |

---

## Estimated Effort

| Phase | Time Estimate |
|-------|---------------|
| Phase 0: Security fixes | 15 min |
| Phase 1: Install dependencies | 15 min |
| Phase 2: Copy + create landing layer | 30 min |
| Phase 3: Controller + routes | 15 min |
| Phase 4: Route changes | 15 min |
| Phase 5: Design adaptation | 1–3 hours (depends on Figma) |
| Phase 6: Deploy pipeline | 15 min |
| Phase 7: Verification | 30 min |
| **Total** | **~3–5 hours** |
