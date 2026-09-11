# QICE — QIEC Training Platform (RocketLMS v2.1)

> منصة تدريب احترافية مبنية على **RocketLMS v2.1** — طبقة تسويقية `landing_v1` ولوحات `panel_v1` معاد تصميمها بالكامل فوق النظام الأساسي `design_1`، مع دعم كامل للعربية والإنجليزية و RTL.

<p align="center">
  <img src="public/assets/admin/img/qiec-logo.svg" width="72" alt="QIEC logo"><br>
  <strong>QIEC للتدريب — Quality & Excellence Center for Training</strong>
</p>

<p align="center">
  <a href="#المكدس-التقني"><img src="https://img.shields.io/badge/Laravel-9.52-red?style=flat-square&logo=laravel" alt="Laravel"></a>
  <a href="#المكدس-التقني"><img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php" alt="PHP"></a>
  <a href="#الاختبارات"><img src="https://img.shields.io/badge/Tests-52%20passing-brightgreen?style=flat-square" alt="Tests"></a>
  <a href="#الترخيص"><img src="https://img.shields.io/badge/License-Envato-blue?style=flat-square" alt="License"></a>
  <a href="https://training.qiec.sa"><img src="https://img.shields.io/badge/Production-training.qiec.sa-success?style=flat-square" alt="Production"></a>
</p>

<p align="center">
  <strong>البيئة:</strong> Laravel 9 · PHP 8.2 + ionCube 15.5 · MySQL 8 · Vite 5 + Tailwind 3 · Docker<br>
  <strong>الترخيص:</strong> Envato Purchase Code — مربوط بدومين <code>training.qiec.local</code> (محلي) و <code>training.qiec.sa</code> (إنتاج)
</p>

---

## الفهرس

- [نظرة عامة ورؤية المشروع](#نظرة-عامة-ورؤية-المشروع)
- [المزايا الأساسية](#المزايا-الأساسية)
- [المكدس التقني](#المكدس-التقني)
- [المعمارية والتقسيم](#المعمارية-والتقسيم)
- [هيكل المجلدات](#هيكل-المجلدات)
- [المتطلبات](#المتطلبات)
- [التشغيل السريع — Docker](#التشغيل-السريع--docker-موصى-به)
- [التشغيل اليدوي](#التشغيل-اليدوي-بدون-docker)
- [متغيرات البيئة](#متغيرات-البيئة)
- [بناء الأصول](#بناء-الأصول)
- [تفعيل الترخيص](#تفعيل-الترخيص)
- [قاعدة البيانات والـ Seeders](#قاعدة-البيانات-والـ-seeders)
- [الأدوار والمسارات](#الأدوار-والمسارات)
- [واجهات برمجة التطبيقات (APIs)](#واجهات-برمجة-التطبيقات-apis)
- [السكربتات المفيدة](#السكربتات-المفيدة)
- [الاختبارات والجودة](#الاختبارات-والجودة)
- [النشر للإنتاج](#النشر-للإنتاج)
- [استكشاف الأخطاء](#استكشاف-الأخطاء)
- [الأمان](#الأمان--لا-ترفع-أسرارا)
- [المساهمة وأسلوب العمل](#المساهمة-وأسلوب-العمل)
- [خارطة الطريق v1](#خارطة-الطريق-v1)
- [الوثائق الإضافية](#الوثائق-الإضافية)

---

## نظرة عامة ورؤية المشروع

هذا المشروع هو **تخصيص استراتيجي** لمنصة RocketLMS لصالح **QIEC للتدريب** — يهدف لتحويل المنصة من ثيم قديم `design_1/Stisla` إلى هوية `QIEC` العصرية مع الحفاظ على استقرار النظام الأساسي.

**ما يضيفه المشروع:**

- **الواجهة التسويقية `landing_v1`**: 12 صفحة عامة (`/`, `/about`, `/courses`, `/course-details`, `/courses-paid`, `/instructors`, `/instructors/{username}`, `/workshops`, `/blogs`, `/blog/{slug}`, `/contact`, `/cart`, `/checkout`, `/account/settings`) بتصميم Tailwind + FlyonUI ومُجمّعة بـ Vite — بديل كامل لصفحات `design_1/web`.
- **لوحات `panel_v1` الجديدة**: أربع لوحات معاد تصميمها — **طالب** (`/v1/student` 23 مسار)، **مدرب** (`/v1/instructor` 40+ مسار)، **منظمة** (`/v1/organization` 8 مسارات)، **إدارة** (`/v1/admin` 4 لوحات) — بديلة تدريجياً للوحة `design_1` القديمة على `/panel`.
- **مشغل الدورة `course-player`**: واجهة تعلم غامرة (`/v1/student/courses/{slug}/watch|forum|assignment|quiz`) مع تتبع تقدم حقيقي.
- **ثيم إدارة QIEC** (`resources/sass/admin/qiec-theme.scss`): سايدبار أخضر غامق `#0f4c45` + ذهبي `#c99c69` + خط Cairo.
- **توافق عربي/إنجليزي** كامل مع دعم RTL والهجرة.

> **قاعدة ذهبية:** أي صفحة تعرض رقماً أو قائمة، فبياناتها من قاعدة البيانات — لا بيانات وهمية في الإنتاج (`StudentV1DemoDataSeeder` للاختبار فقط).

**الجمهور:** متدربون، مدربون معتمدون، منظمات، وإدارة QIEC.

---

## المزايا الأساسية

| الفئة | المزايا |
|---|---|
| **للمتدرب** | لوحة تقدم، شهادات، تكليفات، اختبارات، تعليقات، جلسات استشارية، إعلانات، نقاط ومكافآت، حضور، منتديات، أقساط، دعم فني |
| **للمدرب** | إنشاء دورة 5 خطوات، منهج (فصول/جلسات/ملفات/نصوص)، إدارة تكليفات واختبارات، أداء الدورة، استشارات، مالية وأرباح، تسويق وقسائم، دعم |
| **للمنظمة** | إدارة مدربين وطلاب، دورات المنظمة |
| **للإدارة** | لوحات التعليم والمبيعات والتسويق والنظام (قيد النقل لـ v1) |
| **عام** | سلة وتخفيضات، اشتراكات، إشعارات، بحث، تصفية، تحميل شهادات |

---

## المكدس التقني

| طبقة | التقنية | الإصدار | ملاحظات |
|---|---|---|---|
| Backend | Laravel | 9.52 | `php:8.2-apache-bookworm` إجباري |
| PHP | PHP + ionCube Loader | 8.2 + 15.5 | ملفات (`PurchaseCode`, `LicenseService`) مشفرة — `webdevops` تسبب `segfault` |
| قاعدة البيانات | MySQL | 8.0 | 471 migration + `purchase_code` |
| Frontend legacy | Laravel Mix | 6 | Bootstrap 4 + jQuery + Stisla → `public/assets/{admin,design_1}` |
| Frontend الجديد | Vite + Tailwind + FlyonUI | 5 + 3 | → `public/build` — scoped بـ `#landing-v1-app` |
| الاختبارات | PHPUnit | 9.6 | 52 اختبار (93 assertion) — DB منفصلة `qiec_testing` |
| البنية | Docker Compose | 24+ | `qiec-app:8000` + `qiec-db:3307` |

---

## المعمارية والتقسيم

```
RocketLMS Laravel 9
├── design_1          → ثيم Stisla الأصلي (admin + panel قديم) → public/assets/design_1  [Mix] — يُحافظ عليه
├── landing_v1        → الواجهة التسويقية (Vite + Tailwind)    → public/build            [Vite]
│   ├── LandingV1Controller (12 action)
│   ├── resources/views/landing_v1/ (12 صفحة + components)
│   └── resources/css+js/landing_v1.*
└── panel_v1          → اللوحات المعاد تصميمها → public/build [Vite]
    ├── PanelV1/StudentController (23 route) + CoursePlayerController (8)
    ├── PanelV1/InstructorController (40+ route) + Organization/Admin
    ├── PanelV1/Support/ProfileSettingsTrait (إعدادات موحدة)
    ├── resources/views/panel_v1/{student,instructor,organization,admin}
    └── resources/css+js/panel_v1/*
```

- **الراوتات:** `routes/web.php` (الواجهة + التفعيل)، `routes/panel_v1.php` (`/v1/*` معزولة)، `routes/panel.php` (القديم `/panel`)، `routes/admin.php` (مشفرة — لا تُعدّل بنيتها).
- **التحويل بعد الدخول** مركزي في `app/Helpers/helper.php:panelV1HomeUrl()` ويُستدعى من `LoginController` و`RegisterController` — الأدمن يبقى على القديم، الباقون على V1 (حتى التفعيل النهائي).
- **الأصول:** مصدران — Mix للقديم وVite للجديد. كلاهما `gitignored` ويجب بناؤهما محلياً وعلى السيرفر.
- **الحماية:** كل كنترولر V1 يحوي `resolve{Role}() + teacherWebinarOrFail()` — عبر أدوار يذهب لصفحته، دخول بكورس غريب → `404`.

---

## هيكل المجلدات

```
qice-rocket-lms/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/                 # لوحة الإدارة (Stisla) — قديم
│   │   ├── Panel/                 # لوحة المستخدم القديمة — قديم (89 كنترولر)
│   │   ├── PanelV1/               # ★ اللوحات الجديدة (V1)
│   │   │   ├── StudentController.php (23 route + 7 هامشية)
│   │   │   ├── InstructorController.php (40+ route + wizard)
│   │   │   ├── CoursePlayerController.php (watch حقيقي buildPlayerData)
│   │   │   ├── OrganizationController.php
│   │   │   ├── Admin*.php
│   │   │   └── Support/ProfileSettingsTrait + InstructorMockData
│   │   ├── Web/LandingV1Controller.php    # ★ الواجهة (12 action + account/settings)
│   │   └── Auth/                  # تسجيل/دخول + panelV1HomeUrl
│   ├── Models/ + User.php         # 100+ موديل (Webinar, Sale, Quiz, Certificate, Support, Discount...)
│   ├── Helpers/{helper,settings}.php
│   └── Providers/
├── config/                        # 53 ملف (دفع، تخزين، خدمات)
├── database/
│   ├── migrations/                # 471 — تشمل إصلاحات التوافق + purchase_code
│   ├── seeders/                   # DatabaseSeeder + StudentV1DemoDataSeeder (اختبار)
│   └── factories/                 # UserFactory
├── resources/
│   ├── views/
│   │   ├── admin/ + design_1/     # قديم
│   │   ├── landing_v1/            # ★ 12 صفحة + account/settings
│   │   ├── panel_v1/{admin,instructor,student,organization}
│   │   └── purchase_code/         # تفعيل الترخيص
│   ├── sass/{admin,design_1}/     # Mix
│   ├── css/{landing_v1,panel_v1}/ # Vite
│   └── js/{landing_v1,panel_v1}/
├── routes/{web,panel_v1,panel,admin}.php
├── public/
│   ├── assets/{admin,design_1}  # مُولّدة — gitignored
│   ├── build/                   # Vite — gitignored
│   └── vendor/laravel-filemanager/
├── storage/ + bootstrap/cache/    # قابلة للكتابة لـ www-data
├── tests/{Unit,Feature}/          # 52 اختبار
├── _docs/                         # 18 وثيقة (DEPLOYMENT, QA, ...)
├── scripts/                       # سكربتات السيرفر + hostinger-php.sh
├── vite.config.js / tailwind.config.js / webpack.mix.js
└── README.md / AGENTS.md / phpunit.xml
```

> `vendor/`, `node_modules/`, `public/build`, `public/assets/`, `public/vendor/`, `storage/logs/*`, `.env` — كلها `gitignored`.

---

## المتطلبات

| الأداة | الإصدار | ملاحظة |
|---|---|---|
| Docker Engine + Compose | 24+ | المسار المدعوم الوحيد محلياً |
| Node.js | 22 | لبناء الأصول فقط |
| Git | حديث |  |
| المتصفح | حديث | لاختبار RTL |

> PHP/MySQL غير مطلوبين على الجهاز — داخل الحاويات. `php:8.2-apache-bookworm` + `ionCube 15.5` إجباري.

---

## التشغيل السريع — Docker (موصى به)

### 1) ملفات الـ Docker

خارج المشروع في `/tmp/qiec-docker/` (لا تُنقل):

- `Dockerfile` — `php:8.2-apache-bookworm` + `pdo_mysql, mbstring, zip, exif, pcntl, bcmath, gd, intl` + ionCube + Composer. DocumentRoot → `public`.
- `docker-compose.yml` — `qiec-app:8000` + `qiec-db:3307` (داخلياً `db:3306`) + volume `qiec-mysql-data`.

### 2) الدومين الإجباري

```bash
echo '127.0.0.1 training.qiec.local' | sudo tee -a /etc/hosts
# افتح http://training.qiec.local:8000
```

### 3) التشغيل اليومي

```bash
cd /tmp/qiec-docker && docker compose up -d
docker ps --filter name=qiec
docker compose stop          # إيقاف مع الاحتفاظ بالداتا
docker compose down -v       # ⚠️ يمسح الداتابيز — لبداية صفرية فقط
```

### 4) أول إعداد (مرجع — منفذ)

```bash
docker exec -w /var/www/html qiec-app composer install --ignore-platform-reqs
docker exec -w /var/www/html qiec-app php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;" # ضع في APP_KEY
docker exec -w /var/www/html qiec-app bash -c "mkdir -p storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache && chmod -R 775 storage bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache"
docker exec -w /var/www/html -e APP_ENV=testing qiec-app php artisan migrate --force
docker exec -w /var/www/html qiec-app php artisan migrate --force && php artisan db:seed --force
docker exec -w /var/www/html qiec-app php artisan jwt:secret --force
npm install && npx mix --production && npm run build:landing
docker exec -w /var/www/html qiec-app bash -c "mkdir -p public/vendor/laravel-filemanager && cp -r vendor/unisharp/laravel-filemanager/public/* public/vendor/laravel-filemanager/"
docker exec -w /var/www/html qiec-app php artisan qiec:seed-landing-demo
docker exec -u www-data -w /var/www/html qiec-app php artisan config:clear
```

بيانات الدخول: `admin@demo.com` / `user@gmail.com` / `teacher@gmail.com` / `organ@gmail.com` — `123456`.

---

## التشغيل اليدوي (بدون Docker)

يتطلب `php 8.2` + `ionCube 15.5` + `MySQL 8` + `Composer 2` + `Node 22` محلياً — غير موصى به.

---

## متغيرات البيئة

```ini
APP_NAME=qiec  APP_ENV=local  APP_DEBUG=true  APP_URL=http://training.qiec.local:8000
APP_KEY=base64:...
DB_CONNECTION=mysql  DB_HOST=db  DB_DATABASE=qiec  DB_USERNAME=qiec  DB_PASSWORD=qiec123
MAIL_MAILER=log  JWT_SECRET=...  ADMIN_EMAIL=admin@demo.com  ADMIN_PASSWORD=123456
```

انظر `_docs/ENV_PRODUCTION_CHECKLIST.md` للإنتاج (`APP_DEBUG=false`, `APP_URL=https://training.qiec.sa` بلا `/`).

---

## بناء الأصول

| الأصول | الأداة | الأمر | المخرجات |
|---|---|---|---|
| `admin` + `design_1` | Mix | `npx mix --production` | `public/assets/{admin,design_1}` |
| `landing_v1` + `panel_v1` | Vite | `npm run build:landing` | `public/build` |
| تطوير | Vite | `npm run dev:landing` | HMR |

`tailwind.config.js` محصور بـ `#landing-v1-app` لتجنب التسريب. `webpack.mix.js` مُستعاد.

---

## تفعيل الترخيص

```bash
docker exec -w /var/www/html qiec-app php artisan tinker --execute="print_r(app(App\Services\LicenseService::class)->func3847291650('KEY', true));"
docker exec -w /var/www/html qiec-app php artisan tinker --execute="App\Models\PurchaseCode::updatePurchaseCode('KEY','main','Regular License');"
```

الكود مربوط بـ `training.qiec.local` — `localhost` يُحوّل تلقائياً. الجدول `purchase_code` تُنشئه `2026_09_08_000001_create_purchase_code_table.php`.

---

## قاعدة البيانات والـ Seeders

- **471 migration** — 11 مُصلحة idempotent.
- **Seeders:** `DatabaseSeeder` → `DefaultGeneral/Financial` + `UsersTableSeeder` + `qiec:seed-landing-demo` + `StudentV1DemoDataSeeder` (للاختبار: مناطق/بنوك/مشتريات/محاضرات/اختبارات).
- **اختبار:** `qiec_testing` تُهاجر مرة واحدة ثم `DatabaseTransactions`.

---

## الأدوار والمسارات

| الدور | دخول يحوّل إلى | V1 | قديم |
|---|---|---|---|
| متدرب | `/v1/student` | `panel_v1/student/*` (23) | `/panel` |
| مدرب | `/v1/instructor` | `panel_v1/instructor/*` (40+) | `/panel` |
| منظمة | `/v1/organization` | `panel_v1/organization/*` | `/panel` |
| إدارة | `/admin` | `/v1/admin` (shell) | `/admin` (55 قسم) |

حماية `resolve{Role}() + teacherWebinarOrFail()` — وصول غريب → `404`.

---

## واجهات برمجة التطبيقات (APIs)

- **Web:** `GET /`, `/about`, `/courses`, `/courses-paid?category_id=&search=&sort=`, `/webinar/{slug}`, `/cart`, `/checkout`, `/blogs`, `/blog/{slug}`, `/instructors`, `/instructor/{username}`, `POST /cart/{id}/store`, `GET /regions/provincesByCountry/{id}` (للمناطق المتسلسلة)
- **Panel V1 Student:** `GET /v1/student` (home + 6 تابات), `GET /v1/student/{certificates|assignments|quizzes|comments|meetings|noticeboards|rewards|attendances|upcoming|forums|installments|purchases|favorites|notes|notifications|settings}`, `POST` للتحديثات، `GET /v1/student/courses/{slug}/watch|forum|assignment|quiz`
- **Panel V1 Instructor:** `GET /v1/instructor`, `GET|POST /v1/instructor/courses/create/{step}`, `POST /curriculum/*`, `GET /courses/{slug}/watch|performance|assignments`, `GET /assignments|quizzes|finance|payouts|marketing|support|settings`
- **Auth:** `POST /login`, `POST /register` → `panelV1HomeUrl()` يحدد الوجهة.

---

## السكربتات المفيدة

| السكربت | الوصف |
|---|---|
| `qiec:reset-admin-password` | من `.env` |
| `qiec:platform-status` | بريد/تحقق/بوابات |
| `qiec:seed-landing-demo` | 6 مدربين + 14 دورة |
| `qiec:seed-student-demo` | داتا طالب واقعية للاختبار |
| `jwt:secret` | توليد `JWT_SECRET` |

---

## الاختبارات والجودة

```bash
docker exec -w /var/www/html -e APP_ENV=testing qiec-app vendor/bin/phpunit
docker exec -w /var/www/html -e APP_ENV=testing qiec-app vendor/bin/phpunit --testsuite=Unit
docker exec -w /var/www/html -e APP_ENV=testing qiec-app vendor/bin/phpunit --testsuite=Feature --testdox
```

- **52 اختبار (93 assertion)** — `StudentV1FeaturesDirectTest` يغطي `Student` 9 حالات + `CoursePlayer` + `Instructor` أساسيات — Unit: أسعار، Feature: `panelV1HomeUrl`, `Setting`, أدوار, `ReserveMeeting`, guards, ملكية, اختبارات.
- إصلاحان: `convertPriceToDefaultCurrency` + `User::isAdmin()` null-safe.

---

## النشر للإنتاج

انظر `_docs/DEPLOYMENT.md` + `FIRST_DEPLOY_CHECKLIST.md`:

1. `npm run build:landing && npx mix --production` محلياً
2. `git push origin master`
3. SSH `git pull` + نقل `public/assets|build|vendor` (gitignored) + `php artisan migrate --force && storage:link && config:clear` (كـ `www-data`)
4. `APP_DEBUG=false` + `APP_URL=https://training.qiec.sa` + إزالة `noindex` من `.htaccess`

---

## استكشاف الأخطاء

| العرض | السبب | الحل |
|---|---|---|
| تحويل لـ `/purchase-code` | دومين غير `training.qiec.local` | hosts + `APP_URL` |
| `Vite manifest not found` | `public/build` غير مبني | `npm run build:landing` |
| `storage/framework/views` Permission | ملكية `root` | `chown www-data` |
| `DB getaddrinfo for db failed` | `qiec-db` ساقط | `docker compose up -d` |
| `syntax @` في Blade | `@php($var)` قديم | `@php $var; @endphp` |

---

## الأمان — لا ترفع أسراراً

- `.env`, `firebase-auth.json`, `*.pem`, `*.sql`, `storage/logs/*` — `gitignored`.
- `_docs/` تحتوي باسوردات تاريخية — لا تنسخها.
- `APP_DEBUG=false` في الإنتاج.
- `MAIL_MAILER=log` محلياً فقط.

---

## المساهمة وأسلوب العمل

- اقرأ `AGENTS.md` قبل أي تعديل — لا `commit/push` بدون إذن، لا تلمس `ionCube`.
- استخدم أدوات القراءة قبل `edit`، وتحقق عبر التنفيذ قبل التسليم — رسائل قصيرة مع `file:line`.
- أي رقم/قائمة من DB — لا Mock في الإنتاج.

---

## خارطة الطريق v1

- **تم 100%:** `Student` + `CoursePlayer` حقيقي + `Landing` + تصميم Figma `home` — `bccbd78`
- **تم 100%:** `Instructor` (`support/marketing/assignments/courseWatch/Performance` → حقيقي) + `Organization settings` (6 تابات) — `96b9f11` + `680ce34`
- **تم 100% (هذه الجلسة):** `Admin` (`education/sales/marketing/system` كلها `DB` حقيقي `production` — `59/59 tests`) — `v1` أصبح بديل حرفي كامل 1:1
- **المتبقي:** تفعيل `panelV1HomeUrl()` + `build` `public/build|assets`

---

## الوثائق الإضافية

| الوثيقة | الغرض |
|---|---|
| `_docs/README.md` | فهرس |
| `_docs/CLIENT_QIEC.md` | راوتات وألوان |
| `_docs/DEPLOYMENT.md` | Hostinger + SSH |
| `_docs/QA_FULL_TEST_REPORT.md` | تقرير 55 طلب |
| `AGENTS.md` | تعليمات الوكلاء |

---

## الترخيص والمكونات

- **الأساسي:** RocketLMS (ionCube — لا يُعدّل)
- **المضاف:** `landing_v1` + `panel_v1` + `qiec-theme` — مفتوح
- **التبعيات:** 90+ حزمة — `composer.json` `package.json`

> **ملاحظة:** `artisan` داخل الحاوية كـ `www-data` بعد الإعداد: `docker exec -u www-data -w /var/www/html qiec-app php artisan config:clear`
