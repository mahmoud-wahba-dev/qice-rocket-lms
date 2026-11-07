# Frontend (v1)

## Stack

Tailwind 3.4, FlyonUI 1.3 (`HS*` JS plugins), `@iconify/tailwind` (`icon-[tabler--x]`), vanilla JS modules, Vite. No Alpine, Livewire or jQuery in v1.

## Views

```
resources/views/
├─ landing_v1/
│  ├─ layouts/       app, navbar, footer
│  ├─ components/    anonymous cards and sections
│  └─ pages/         home, courses, cart, checkout, blogs, auth/, account/
├─ panel_v1/
│  ├─ student/       layouts/ (navbar + footer, no sidebar), pages/, components/
│  │  └─ course-player/   own layouts/, pages/, components/
│  ├─ instructor/    layouts/ (app, course), pages/, components/
│  ├─ admin/         layouts/, components/, pages/{education,sales,marketing,system}/
│  ├─ organization/  layouts/, pages/, components/ (sidebar only)
│  ├─ components/    shared: actions-dropdown, file-upload, rich-editor, impersonation-banner
│  └─ certificates/  qiec-template
└─ components/v1/    global flash and toast
```

**Shells:** admin and instructor use `layouts/app` with `components/header` + `components/sidebar`. The organization layout reuses the instructor header and has its own sidebar.

## Components and data

- Anonymous components only, registered in `AppServiceProvider`.
- Landing uses `<x-landing_v1::course-card>` with `@props`.
- Panels use `@include` with arrays, such as `actions-dropdown` and `admin.components.page-header`.
- Naming is kebab-case.
- Controllers pass plain arrays containing Eloquent models. There are no ViewModels or DTOs.
- View composers in `AppServiceProvider` provide `$paidCourseCategories`, `$landingImg`, `$panelStudentImg` and `$panelInstructorImg`.

## Builds

| | Vite | Mix |
|---|---|---|
| Config | `vite.config.js` | `webpack.mix.js` |
| Covers | landing_v1, panel student/instructor/admin | legacy admin and design_1 |
| Dev | `npm run dev:full` | `npm run dev` |
| Production | `npm run build:landing` | `npm run production` |
| Output | `public/build` | `public/assets/` |

- There's one shared `tailwind.config.js`.
- Every panel layout loads landing CSS and JS first, then its role bundle.
- Organization has no Vite entry and loads the instructor bundle.
- `public/build`, `public/assets`, `public/vendor` and `mix-manifest.json` are gitignored, including the images under `public/assets/{landing_v1,panel_v1}`.
- **`public/hot`:** while it exists, `@vite` loads from the dev server. If Vite isn't running, pages lose their styles until you delete it.

## Tailwind gotchas

- `important: "#landing-v1-app"` scopes all utilities. Markup outside that id gets no utility styling. Layouts provide the wrapper, so watch modals appended to `body`.
- Plain CSS classes lose to utilities for the same reason (`resources/css/landing_v1.css`).
- `content` scans student, instructor, admin, organization, `panel_v1/components`, `landing_v1` and `views/components/v1`; it still omits `panel_v1/certificates`. Classes must appear as full strings in those files (icon classes like `icon-[tabler--trash]` too), including ones that JS applies later.
- `AdminMockData.php` is in `content` so its icon classes get generated.

## Design tokens

Defined in `resources/css/landing_v1.css`, mapped in `tailwind.config.js`:

| Token | Value |
|---|---|
| `primary` | `#0f4c45` |
| `secondary` | `#faf8f4` |
| `gold` | `#e8d9c0` |
| `color2` | `#c99c69` |
| Greys | `77`, `e3`, `d9`, `fa` |

- `#0F3D36` is **not** a token; it's hardcoded across student and admin views. Use `primary` in new work unless matching an existing screen.
- `--color-fa` and `--font-ibm` are mapped in Tailwind but weren't found in the token block in `landing_v1.css`. Check before relying on `fa`.
- Common page backgrounds are hardcoded: `#FAFAF4`, `#F9FAF5`.
- Font: Cairo 400/600/700 from Google Fonts, loaded in each layout.
- Root font size is `clamp(9px, ..., 13px)`, so `rem` values scale.
- `buildPxScale` generates px utilities. Prefer `rounded-12px` and `text-14px` over `rounded-[12px]`.

## RTL

`dir="rtl"` is hardcoded in all layouts, even in English; only `lang` follows the locale. Use logical `ps-`/`ms-` utilities, `rtl:` variants, and `[--placement:bottom-end]` for FlyonUI dropdowns.

## Translations

- Files: `lang/ar/*.php` and `lang/en/*.php`. Most v1 keys are in `update.php`, which is very large, so grep it.
- Views use `trans()` only, not `__()` or `@lang`.
- Much of the v1 UI is hardcoded Arabic and controllers set Arabic `pageTitle`, so English is partial.
- Locale: `UserLocale` (user language, then `user_locale` cookie, then optional GeoIP) and `Share` (`session('locale')`, then the default from settings).
- `POST /locale` exists, but no v1 view exposes a language switcher.

## Design references

There are no Figma links in the repo. Notes are in `_docs/CLIENT_PLAYBOOK.md` (Figma to Blade workflow) and `_docs/CLIENT_QIEC.md` (colours, prototypes). The legacy admin theme is `resources/sass/admin/qiec-theme.scss`, compiled to `custom.css`; don't edit Stisla's `style.css`.
