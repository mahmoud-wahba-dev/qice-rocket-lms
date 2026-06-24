# Client: QIEC (Quality and Innovation in Education Center)

**Domain:** https://training.qiec.sa  
**Migrated from:** siematplus architecture  
**Status:** Landing layer implemented; some pages are Figma prototypes; deployment scripts ready

---

## Branding

### Colors (`resources/css/landing_v1.css`)

| Token | Value | Usage |
|-------|-------|-------|
| `--color-primary` | `#0f4c45` | Header, footer, buttons |
| `--color-secondary` | `#faf8f4` | Page backgrounds |
| `--color-gold` | `#e8d9c0` | Accents, borders |
| `--color-blue` | `#3d455d` | Secondary text |
| `--color-card-text` | `#255977` | Card typography |
| `--color-card-border` | `#e0d4bc` | Card borders |

### Typography

- **Font:** Cairo (Google Fonts)
- **Direction:** RTL (`dir="rtl"` in layout)
- **Tailwind scope:** `#landing-v1-app`

### Contact

| Channel | Value |
|---------|-------|
| Email | `contact@training.qiec.sa` |
| Production mail | `smtp.hostinger.com` |

---

## Routes map

| Route | Name | Controller method | Status |
|-------|------|-------------------|--------|
| `/` | `landing.v1.index` | `index` | Dynamic — courses + trainers from DB |
| `/about` | `landing.v1.about` | `about` | Static content |
| `/workshops` | `landing.v1.workshops` | `workshops` | Static prototype |
| `/contact` | `landing.v1.contact` | `contact` | Static content |
| `/courses` | `landing.v1.courses` | `courses` | Dynamic — filters, AJAX |
| `/courses-paid` | `landing.v1.courses-paid` | `coursesPaid` | Static prototype |
| `/course-details-free` | `landing.v1.course-details-free` | `courseDetailsFree` | Static prototype |
| `/course-details-paid` | `landing.v1.course-details-paid` | `courseDetailsPaid` | Static prototype |
| `/blogs` | `landing.v1.blogs` | `blogs` | Static prototype |
| `/blog-details` | `landing.v1.blog-details` | `blogDetails` | Static prototype |
| `/instructors` | `landing.v1.instructors` | `instructors` | Dynamic — teacher stats |
| `/cart` | `landing.v1.cart` | `cart` | Dynamic — guest + auth |
| `/checkout` | `landing.v1.checkout` | `checkout` | Dynamic — payment channels |
| `/webinar/{slug}` | `landing.v1.course-details` | `courseDetails` | Dynamic — full course page |
| `/login` | (auth) | `LoginController` | Uses `landing_v1` auth view |
| `/register` | (auth) | `RegisterController` | Uses `landing_v1` auth view |

### QIEC-only routes (stock RocketLMS, kept active)

- `/events/*` — Events system
- `/meeting-packages/*` — Meeting packages

---

## Pages: dynamic vs prototype

### Fully wired to database

- Home (featured courses, trainers)
- Courses listing (category filters, sort, AJAX partial)
- Course details (`/webinar/{slug}`)
- Instructors (course count, student count per teacher)
- Cart (guest cookie + authenticated DB cart)
- Checkout (payment channels, offline banks, discounts)
- Auth login/register

### Static Figma prototypes (need DB wiring later)

- `courses-paid.blade.php`
- `course-details-free.blade.php`
- `course-details-paid.blade.php`
- `workshops.blade.php`
- `blogs.blade.php`
- `blog-details.blade.php`

**Next step for prototypes:** connect to `Webinar` model or add CMS/blog models when content is ready.

---

## Views structure

```
resources/views/landing_v1/
├── layouts/
│   ├── app.blade.php
│   ├── navbar.blade.php
│   └── footer.blade.php
├── components/
│   ├── blog-card.blade.php
│   ├── course-card.blade.php
│   ├── instructor-card.blade.php
│   ├── page-hero.blade.php
│   ├── prefooter-cta.blade.php
│   ├── sec-header.blade.php
│   ├── stats.blade.php
│   └── workshop-card.blade.php
└── pages/
    ├── home, about, contact, courses, courses_list
    ├── courses-paid, course-details, course-details-free, course-details-paid
    ├── cart, checkout, instructors, workshops, blogs, blog-details
    └── auth/login, auth/register
```

Images: `public/assets/landing_v1/img/` (gitignored — must upload to Hostinger manually).

---

## TODO (remaining work)

- [x] Landing layer structure and routes
- [x] Vite/Tailwind/FlyonUI pipeline
- [x] QIEC color tokens in CSS
- [x] Deploy scripts adapted for `training.qiec.sa`
- [x] Siematplus branding removed from layout/contact/footer
- [ ] **First production deploy** — add SSH key to hPanel, then follow [FIRST_DEPLOY_CHECKLIST.md](FIRST_DEPLOY_CHECKLIST.md)
- [ ] Wire prototype pages to real data
- [ ] Remove `X-Robots-Tag: noindex` from `.htaccess` before public launch
- [ ] Upload `public/assets/design_1/` and `public/assets/landing_v1/img/` to server
- [ ] Set server `.env` with `APP_DEBUG=false`

---

## Differences from siematplus

| Item | siematplus | QIEC |
|------|-----------|------|
| Extra pages | — | workshops, blogs, courses-paid, course-details-free/paid |
| Payment gateways | MyFatoorah, Mailchimp | Tamara, Tabby (in composer.json) |
| Font | IBM Plex (siematplus) | Cairo |
| Primary color | Client-specific | `#0f4c45` |
| Events system | Not present | Active `/events/*` routes |

Full comparison: [COMPARISON_SIEMATPLUS_VS_QIEC.md](COMPARISON_SIEMATPLUS_VS_QIEC.md)

---

## Related docs

- [CLIENT_PLAYBOOK.md](CLIENT_PLAYBOOK.md) — reusable process
- [DEPLOYMENT.md](DEPLOYMENT.md) — Hostinger deploy guide
- [ISSUES_AND_LESSONS.md](ISSUES_AND_LESSONS.md) — problems encountered
