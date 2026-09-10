# QICE — QIEC Training Platform (RocketLMS)

> منصة تدريب مبنية على **RocketLMS v2.1** — طبقة تسويقية مخصصة `landing_v1` + لوحات `panel_v1` معاد تصميمها، فوق نظام إدارة التعلم الأساسي `design_1`.

<p align="center">
  <img src="public/assets/admin/img/qiec-logo.svg" width="72" alt="QIEC logo">
</p>

<p align="center">
  <strong>البيئة:</strong> Laravel 9 · PHP 8.2 · MySQL 8 · Vite + Tailwind · Docker &nbsp;|&nbsp;
  <strong>الترخيص:</strong> Envato Purchase Code — مربوط بدومين <code>training.qiec.local</code>
</p>

---

## الفهرس

- [نظرة عامة](#نظرة-عامة)
- [المكدس التقني](#المكدس-التقني)
- [المعمارية](#المعمارية)
- [هيكل المجلدات](#هيكل-المجلدات)
- [المتطلبات](#المتطلبات)
- [التشغيل السريع — Docker (موصى به)](#التشغيل-السريع--docker-موصى-به)
- [التشغيل اليدوي (بدون Docker)](#التشغيل-اليدوي-بدون-docker)
- [متغيرات البيئة](#متغيرات-البيئة)
- [بناء الأصول](#بناء-الأصول)
- [تفعيل الترخيص](#تفعيل-الترخيص)
- [قاعدة البيانات والـ seeders](#قاعدة-البيانات-والـ-seeders)
- [الأدوار والمسارات](#الأدوار-والمسارات)
- [السكربتات المفيدة](#السكربتات-المفيدة)
- [الاختبارات](#الاختبارات)
- [النشر للإنتاج](#النشر-للإنتاج)
- [استكشاف الأخطاء](#استكشاف-الأخطاء)
- [الأمان — لا ترفع أسراراً](#الأمان--لا-ترفع-أسرارا)
- [الوثائق الإضافية](#الوثائق-الإضافية)

---

## نظرة عامة

هذا المشروع هو تخصيص لمنصة RocketLMS لصالح **QIEC للتدريب**. يحافظ على لوحة الإدارة والـ panel القديم (`design_1`)، ويضيف:

- **الواجهة التسويقية `landing_v1`**: صفحات عامة (`/`, `/about`, `/courses`, `/course-details`, `/instructors`, `/workshops`, `/blogs`, `/contact`, `/cart`, `/checkout`) بتصميم Tailwind + FlyonUI ومُجمّعة بـ Vite.
- **لوحات `panel_v1` الجديدة**: للطالب والمدرب والإدارة والمنظمة (`/v1/student`, `/v1/instructor`, `/v1/organization`, `/v1/admin`) — بديلة تدريجياً للوحة `design_1` القديمة على `/panel`.
- **ثيم إدارة QIEC** (`resources/sass/admin/qiec-theme.scss`): سايدبار أخضر غامق `#0f4c45` + ذهبي `#c99c69` + Cairo.
- **توافق عربي/إنجليزي** مع دعم RTL.

> **قاعدة ذهبية:** أي صفحة تعرض رقماً أو قائمة، فبياناتها من قاعدة البيانات — لا بيانات وهمية في الإنتاج.

---

## المكدس التقني

| طبقة | التقنية | ملاحظات |
|---|---|---|
| Backend | Laravel 9.52 · PHP 8.2 + **ionCube Loader 15.5** | ملفات (`PurchaseCode`, `LicenseService`) مشفرة — تتطلب `php:8.2-apache-bookworm` |
| قاعدة البيانات | MySQL 8.0 · 471 migration | تشمل إصلاحات توافق محلية + `purchase_code` |
| Frontend legacy | Laravel Mix + Bootstrap 4 + jQuery + Stisla | يبني `public/assets/admin` و`public/assets/design_1` — مجلدان `gitignored` |
| Frontend الجديد | Vite 5 + Tailwind 3 + FlyonUI | يبني `public/build` — `gitignored` |
| الاختبارات | PHPUnit 9.6 — 43 اختبار (73 assertion) | DB اختبار منفصلة `qiec_testing` |

---

## المعمارية

```
RocketLMS Laravel 9
├── design_1          → ثيم Stisla الأصلي (admin + panel قديم) → public/assets/design_1  [Mix]
├── landing_v1        → الواجهة التسويقية (Vite + Tailwind)    → public/build            [Vite]
│   ├── LandingV1Controller
│   ├── resources/views/landing_v1/
│   └── resources/css+js/landing_v1.*
└── panel_v1          → اللوحات المعاد تصميمها (student/instructor/admin/org) → public/build [Vite]
    ├── PanelV1/*Controller
    ├── resources/views/panel_v1/
    └── resources/css+js/panel_v1/*
```

- **الراوتات:** `routes/web.php` (الواجهة + التفعيل)، `routes/panel_v1.php` (`/v1/*`)، `routes/panel.php` (القديم `/panel`)، `routes/admin.php` (مشفرة — لا تُعدّل بنيتها).
- **التحويل بعد الدخول** مركزي في `app/Helpers/helper.php:panelV1HomeUrl()` ويُستدعى من `LoginController` و`RegisterController` — الأدمن يبقى على القديم، الباقون على V1.
- **الأصول:** مصدران — Mix للقديم وVite للجديد. كلاهما `gitignored` ويجب بناؤهما محلياً وعلى السيرفر.

---

## هيكل المجلدات

```
qice-rocket-lms/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/                 # لوحة الإدارة (Stisla) — قديم
│   │   ├── Panel/                 # لوحة المستخدم القديمة — قديم
│   │   ├── PanelV1/               # ★ اللوحات الجديدة (V1)
│   │   │   ├── StudentController.php
│   │   │   ├── InstructorController.php   # + wizard + quizzes + finance
│   │   │   ├── CoursePlayerController.php # quiz assignment forum
│   │   │   ├── OrganizationController.php
│   │   │   └── Support/*.php
│   │   ├── Web/LandingV1Controller.php    # ★ الواجهة التسويقية
│   │   └── Auth/                  # تسجيل/دخول + panelV1HomeUrl
│   ├── Models/ + User.php         # 100+ موديل (Webinar, Sale, Quiz, ...)
│   ├── Helpers/{helper,settings}.php
│   └── Providers/                 # AppServiceProvider (namespaces + composers)
├── config/                        # 53 ملف (دفع، تخزين، خدمات)
├── database/
│   ├── migrations/                # 471 — تشمل إصلاحات التوافق المحلي
│   │   └── 2026_09_08_000001_create_purchase_code_table.php  # لوكال
│   ├── seeders/                   # DatabaseSeeder → DefaultGeneral/Financial
│   └── factories/                 # UserFactory فقط
├── resources/
│   ├── views/
│   │   ├── admin/                 # لوحة الإدارة القديمة
│   │   ├── design_1/              # ثيم Panel/Web القديم
│   │   ├── landing_v1/            # ★ الواجهة الجديدة
│   │   ├── panel_v1/{admin,instructor,student,organization}
│   │   └── purchase_code/         # تفعيل الترخيص
│   ├── sass/{admin,design_1}/     # مصادر Mix
│   ├── css/{landing_v1,panel_v1}/ # مصادر Vite
│   └── js/{admin,design_1,landing_v1,panel_v1}/
├── routes/{web,panel_v1,panel,admin}.php
├── public/
│   ├── assets/{admin,design_1,landing_v1,default}  # مُولّدة — gitignored
│   ├── build/                                     # Vite — gitignored
│   ├── vendor/laravel-filemanager/                # منسوخة من vendor
│   └── mix-manifest.json                          # gitignored
├── storage/ + bootstrap/cache/    # يجب أن تكون قابلة للكتابة لـ www-data
├── tests/{Unit,Feature}/          # PHPUnit
├── _docs/                         # دليل المشروع (README, DEPLOYMENT, ...)
├── scripts/                       # سكربتات السيرفر
├── vite.config.js / tailwind.config.js / postcss.config.js
├── webpack.mix.js                 # مُستعاد — يبني القديم + vendors
├── phpunit.xml / .gitignore / artisan
└── README.md / AGENTS.md
```

> مجلدات `vendor/`, `node_modules/`, `public/build`, `public/assets/`, `public/vendor/`, `storage/logs/*`, `.env` — كلها `gitignored` ويجب توليدها (انظر أدناه).

---

## المتطلبات

| الأداة | الإصدار |
|---|---|
| Docker Engine + Compose plugin | 24+ |
| Node.js | 22 (لبناء الأصول فقط) |
| Git | أي إصدار حديث |

> PHP/MySQL غير مطلوبين على الجهاز — داخل الحاويات. `php:8.2-apache-bookworm` + `ionCube 15.5` إجباري (صور `webdevops` تسبب `segfault` مع الملفات المشفرة).

---

## التشغيل السريع — Docker (موصى به)

### 1) ملفات الـ Docker

موجودة **خارج المشروع** في `/tmp/qiec-docker/` (تبقى كما هي — لا تُنقل):

- `Dockerfile` — `php:8.2-apache-bookworm` + `pdo_mysql, mbstring, zip, exif, pcntl, bcmath, gd, intl` + ionCube + Composer. الـ DocumentRoot → `public`.
- `docker-compose.yml` — خدمتان + volume `qiec-mysql-data`.

```yaml
# المنافذ
8000 → 80   (التطبيق)
3307 → 3306 (MySQL من الجهاز)
# داخل الحاويات
db:3306 — قاعدة qiec / user qiec / pass qiec123 / root root
```

### 2) الدومين الإجباري

رمز الشراء مربوط بـ `training.qiec.local` — أي دخول بـ `localhost` يُحول لصفحة التفعيل.

```bash
echo '127.0.0.1 training.qiec.local' | sudo tee -a /etc/hosts
# ثم افتح
http://training.qiec.local:8000
```

### 3) التشغيل اليومي

```bash
cd /tmp/qiec-docker
docker compose up -d          # تشغيل
docker ps --filter name=qiec  # تحقق
docker compose stop           # إيقاف (يحتفظ بالداتا)

# ⚠️ يمسح الداتابيز — لا تستخدمه إلا لبداية صفرية
docker compose down -v
```

### 4) أول إعداد من الصفر (موثق كمرجع — منفذ بالفعل)

```bash
cd /tmp/qiec-docker && docker compose up -d --build

# .env — أهم القيم محلياً
# APP_ENV=local APP_DEBUG=true APP_URL=http://training.qiec.local:8000
# DB_HOST=db DB_DATABASE=qiec DB_USERNAME=qiec DB_PASSWORD=qiec123
# MAIL_MAILER=log ADMIN_EMAIL=admin@demo.com ADMIN_PASSWORD=123456

# 1) اعتمادات PHP (مؤقتاً ignore-platform-reqs إلى حين إصلاح composer.lock)
docker exec -w /var/www/html qiec-app composer install --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs

# 2) APP_KEY يدوياً (artisan key:generate يفشل قبل المايجريشن لأن الـ providers تقرأ جداول لم تُنشأ)
docker exec -w /var/www/html qiec-app php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
# ضع الناتج في APP_KEY داخل .env

# 3) مجلدات التخزين
docker exec -w /var/www/html qiec-app bash -c "mkdir -p storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache && chmod -R 775 storage bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache"

# 4) قاعدة البيانات
docker exec -w /var/www/html -e APP_ENV=testing qiec-app php artisan migrate --force
docker exec -w /var/www/html qiec-app php artisan migrate --force
docker exec -w /var/www/html qiec-app php artisan db:seed --force
docker exec -w /var/www/html qiec-app php artisan "db:seed" --class="Database\Seeders\UsersTableSeeder" --force
docker exec -w /var/www/html qiec-app php artisan qiec:reset-admin-password

# 5) JWT (إجباري)
docker exec -w /var/www/html qiec-app php artisan jwt:secret --force

# 6) الأصول (من الجهاز نفسه)
npm install
npx mix --production                         # يبني admin + design_1 (gitignored)
npm run build:landing                        # يبني landing_v1 + panel_v1
docker exec -w /var/www/html qiec-app bash -c "mkdir -p public/vendor/laravel-filemanager && cp -r vendor/unisharp/laravel-filemanager/public/* public/vendor/laravel-filemanager/"

# 7) التفعيل (أونلاين — يتطلب APP_URL الصحيح)
docker exec -w /var/www/html qiec-app php artisan tinker --execute="\$s=app(App\Services\LicenseService::class); print_r(\$s->func3847291650('PUT-KEY-HERE', true));"
docker exec -w /var/www/html qiec-app php artisan tinker --execute="App\Models\PurchaseCode::updatePurchaseCode('PUT-KEY-HERE', 'main', 'Regular License');"

# 8) ديمو الواجهة (اختياري)
docker exec -w /var/www/html qiec-app php artisan qiec:seed-landing-demo

# مهم: نفّذ أوامر artisan كـ www-data بعد الإعداد لتجنب ملفات cache بملكية root
docker exec -u www-data -w /var/www/html qiec-app php artisan config:clear
```

**بيانات الدخول التجريبية:** `admin@demo.com` / `user@gmail.com` / `teacher@gmail.com` / `organ@gmail.com` — كلمة المرور `123456`.

**تحقق سريع:**
```bash
for p in "/" "/about" "/courses" "/contact" "/instructors" "/login" "/register" "/cart" "/admin/login"; do
  code=$(curl -s -o /dev/null -w "%{http_code}" -H "Host: training.qiec.local" "http://127.0.0.1:8000$p")
  echo "$p -> $code"  # المتوقع 200
done
```

---

## التشغيل اليدوي (بدون Docker)

يتطلب `php 8.2` + `ionCube Loader 15.5` + `MySQL 8` + `Composer 2` + `Node 22` مثبتة محلياً. نفس خطوات `.env` والمايجريشن أعلاه، مع `composer install` و`php artisan serve`.

> غير موصى به محلياً بسبب ionCube وتعقيد الإصدارات — Docker هو المسار المدعوم.

---

## متغيرات البيئة

`.env.example` يحتوي فقط `ADMIN_EMAIL/PASSWORD`. القالب الكامل المطلوب محلياً:

```
APP_NAME=qiec  APP_ENV=local  APP_DEBUG=true  APP_URL=http://training.qiec.local:8000
APP_KEY=base64:...
DB_CONNECTION=mysql  DB_HOST=db  DB_DATABASE=qiec  DB_USERNAME=qiec  DB_PASSWORD=qiec123
MAIL_MAILER=log  MAIL_HOST=smtp.hostinger.com  MAIL_PORT=465  MAIL_ENCRYPTION=ssl
JWT_SECRET=...  JWT_TTL=60
ADMIN_EMAIL=admin@demo.com  ADMIN_PASSWORD=123456
```

SEE `_docs/ENV_PRODUCTION_CHECKLIST.md` لقواعد الإنتاج (`APP_DEBUG=false`، بلا `/` في `APP_URL`، اقتباس الباسوردات، `rm bootstrap/cache/config.php` بعد التعديل).

---

## بناء الأصول

| الأصول | الأداة | الأمر | المخرجات |
|---|---|---|---|
| القديم `admin` + `design_1` | **Mix** | `npx mix --production` | `public/assets/{admin,design_1}` + vendors |
| الجديد `landing_v1` + `panel_v1` | **Vite** | `npm run build:landing` | `public/build` |
| إضافي | — | `npm run dev:landing` | وضع المراقبة (HMR) |

- `tailwind.config.js` محصور بـ `#landing-v1-app` + `#panel-v1-app` لتجنب التسريب.
- `webpack.mix.js` مُستعاد (كان مفقوداً) — يبني القديم + ينسخ vendors (`jquery`, `bootstrap`, `moment`, `daterangepicker`, `nicescroll`, `select2`, `sweetalert2`, `filemanager`, `fontawesome`, `stisla.js`).

---

## تفعيل الترخيص

الصفحة `/purchase-code` تتحقق عبر `PurchaseCodeController` → `LicenseService` (ionCube) ضد `https://crm.rocket-soft.org`:

```bash
# تحقق (بحاجة إنترنت + APP_URL الصحيح)
docker exec -w /var/www/html qiec-app php artisan tinker --execute="print_r(app(App\Services\LicenseService::class)->func3847291650('KEY', true));"
# حفظ عند النجاح
docker exec -w /var/www/html qiec-app php artisan tinker --execute="App\Models\PurchaseCode::updatePurchaseCode('KEY','main','Regular License');"
```

- الكود الحالي مربوط بـ `training.qiec.local` — دخول `localhost` يُحوّل تلقائياً.
- الجدول `purchase_code` غير موجود في المايجريشن الأصلي؛ تُنشئه `2026_09_08_000001_create_purchase_code_table.php`.

---

## قاعدة البيانات والـ seeders

- **المايجريشن:** 471 ملف. 11 منها مُصلحة محلياً لتكون idempotent على تنصيب جديد (FK وهمية/أعمدة ناقصة). كلها داخل `php artisan migrate`.
- **الـ seeders الأساسية:** `DatabaseSeeder` → `Sections`, `PaymentChannels`, `LandingBuilderComponents`, `ThemeHeaderFooter`, `DefaultTheme` + `DefaultGeneralSeeder` (لغات) + `DefaultFinancialSeeder` (عملة SAR). المستخدمون عبر `UsersTableSeeder`. الديمو عبر `qiec:seed-landing-demo`.

```bash
docker exec -w /var/www/html qiec-app php artisan migrate:status | tail -n 5
docker exec qiec-db mysql -uqiec -pqiec123 qiec -e "SHOW TABLES;" | wc -l
```

---

## الأدوار والمسارات

| الدور | تسجيل دخول يحوّل إلى | لوحة V1 | لوحة قديمة |
|---|---|---|---|
| متدرب | `/v1/student` | `panel_v1/student/*` | `/panel` |
| مدرب | `/v1/instructor` | `panel_v1/instructor/*` | `/panel` |
| منظمة | `/v1/organization` | `panel_v1/organization/*` | `/panel` |
| إدارة | `/admin` | `/v1/admin` (shell) | `/admin` (كاملة ~55 قسم) |

- الحماية: كل كنترولر V1 يحوي `resolve{Role}()` + `teacherWebinarOrFail()` + `teacherQuizOrFail()` — عبر أدوار يذهب لصفحته، دخول بكورس غريب → `404`.
- `routes/panel_v1.php` معزولة عن القديم. تعليق أعلى الملف يوضح أنها shells حتى التحويل لاحقاً.

**المنظمة:** قسم جديد كامل (`/v1/organization` + أعضاء + دورات) — الإدارة تُدار من القديم.

---

## السكربتات المفيدة

| السكربت | الوصف |
|---|---|
| `php artisan qiec:reset-admin-password` | يعيد ضبط باسورد الأدمن من `.env` |
| `php artisan qiec:platform-status` | حالة البريد والتحقق والبوابات |
| `php artisan qiec:test-mail you@example.com` | اختبار SMTP |
| `php artisan qiec:seed-landing-demo` | ديمو (6 مدربين + 14 دورة) |
| `php artisan jwt:secret` | توليد `JWT_SECRET` |
| `bash scripts/optimize-production.sh` | ما بعد النشر: مزامنة أصول الملفات + مسح الكاش |

---

## الاختبارات

```bash
# داخل الحاوية (PHP 8.2 + ionCube)
docker exec -w /var/www/html -e APP_ENV=testing qiec-app vendor/bin/phpunit
docker exec -w /var/www/html -e APP_ENV=testing qiec-app vendor/bin/phpunit --testsuite=Unit
docker exec -w /var/www/html -e APP_ENV=testing qiec-app vendor/bin/phpunit --testsuite=Feature
```

- قاعدة اختبار منفصلة `qiec_testing` (يُنشئها السكربت) — `.env.testing` `gitignored`. تُهاجر مرة واحدة ثم تُدار بـ `DatabaseTransactions`.
- **43 اختبار (73 assertion)** — Unit: حراس الأسعار، Feature: `panelV1HomeUrl`، `Setting` fallback، أدوار `User`، علاقة `ReserveMeeting`, guards الأدوار الأربعة، حماية الملكية، منطق الاختبارات (نجاح/رسوب/انتظار/عدم-سلبية)، تحقق purchase code.
- `phpunit.xml` يستخدم `CACHE_DRIVER=array`, `SESSION_DRIVER=array`.

> إصلاحان اكتشفهما الاختبار: `convertPriceToDefaultCurrency` غير رقمي و`User::isAdmin()` مع relation `null`.

---

## النشر للإنتاج

انظر `_docs/DEPLOYMENT.md` و`_docs/FIRST_DEPLOY_CHECKLIST.md` و`_docs/ENV_PRODUCTION_CHECKLIST.md`.

**الخلاصة:**
1. `npm run build:landing && npx mix --production` محلياً.
2. `git push origin master`.
3. SSH `git pull` على السيرفر (`domains/training.qiec.sa/public_html`) + نقل `public/assets/` + `public/build/` + `public/vendor/` (كلها `gitignored` — تُرفع عبر File Manager) أو إعادة البناء على السيرفر إن توفر Node/PHP.
4. `php artisan migrate --force && php artisan storage:link && php artisan config:clear` (نفّذ كـ `www-data` لتجنب ملكية `root`).
5. `APP_DEBUG=false` + `APP_URL=https://training.qiec.sa` (بلا `/`) + إزالة `X-Robots-Tag: noindex` من `.htaccess` قبل الإطلاق.

---

## استكشاف الأخطاء

| العرض | السبب | الحل |
|---|---|---|
| تحويل لـ `/purchase-code` | دومين غير `training.qiec.local` | راجع hosts + `APP_URL` |
| `DNS_PROBE_POSSIBLE` | سطر hosts ناقص | `127.0.0.1 training.qiec.local` |
| `Vite manifest not found` | `public/build` غير مبني | `npm run build:landing` |
| `JWT Secret is not set` | `JWT_SECRET` فاضي | `jwt:secret --force` |
| `purchase_code doesn't exist` | migration لوكال | `migrate --force` |
| `Please provide a valid cache path` | مجلدات storage ناقصة | `mkdir -p ... && chown www-data` |
| `exit 139 segfault` | صورة PHP غير متوافقة | استخدم `Dockerfile` الرسمي bookworm |
| `composer install` يرفض | `symfony 8.x` يشترط 8.4 | `composer update` على 8.2 نظيف (أو `--ignore-platform-reqs` مؤقتاً) |

> **تنبيه ionCube:** `php artisan` عبر SSH قد يفشل على Hostinger CLI — الموقع يعمل رغم ذلك. السكربت ينسخ الأصول يدوياً بدل `vendor:publish`.

---

## الأمان — لا ترفع أسراراً

- `.env`, `firebase-auth.json`, `*.pem`, `*.sql` — كلها `gitignored`. لا تضعها في الكود أو التذاكر.
- `_docs/ARCHITECTURE_SUMMARY.md` وغيرها تحتوي باسوردات إنتاج تاريخية — **مؤجلة بقرار المشروع** لكن يجب تدويرها قبل أي نشر عام (DB + `contact@training.qiec.sa`). `git log -S "CBKc"` يُظهرها.
- لا تضع `APP_DEBUG=true` في الإنتاج — يكشف المسارات والاستعلامات.
- البريد المحلي `MAIL_MAILER=log` (يكتب ل`storage/logs`) — الإنتاج يحتاج `smtp.hostinger.com` فعّال وإلا لا تصل أكواد التحقق.

---

## الوثائق الإضافية

| الوثيقة | الغرض |
|---|---|
| `_docs/README.md` | فهرس الوثائق |
| `_docs/CLIENT_QIEC.md` | خريطة الراوتات والألوان والصفحات |
| `_docs/ARCHITECTURE_SUMMARY.md` | مرجع معماري مفصل |
| `_docs/DEPLOYMENT.md` | دليل Hostinger + SSH |
| `_docs/QA_FULL_TEST_REPORT.md` | تقرير اختبار شامل (55 طلب) |
| `_docs/CLIENT_PLAYBOOK.md` | عملية عميل جديد قابلة لإعادة الاستخدام |

---

## الترخيص والمكونات

- **المنصة الأساسية:** RocketLMS (مقفلة بـ ionCube — الملفات المشفرة لا تُعدّل).
- **الطبقات المضافة:** `landing_v1` + `panel_v1` + `qiec-theme` — مفتوحة للتعديل.
- **التبعيات:** 90+ حزمة (دفع، اجتماعات، SMS) — راجع `composer.json` و`package.json`.

---

> **ملاحظة تشغيلية:** أي أمر `artisan` داخل الحاوية نفّذه كـ `www-data` بعد الإعداد:
> `docker exec -u www-data -w /var/www/html qiec-app php artisan config:clear`
