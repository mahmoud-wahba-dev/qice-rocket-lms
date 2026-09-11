# AGENTS — تعليمات وكلاء التطوير لمشروع QICE

> للوكلاء (AI agents) والأدوات الآلية — الأولوية: **عدم كسر الإنتاج، عدم تسريب أسرار، عدم الرفع بدون إذن**.

---

## 1. ما هذا المشروع

- **QIEC للتدريب** على RocketLMS v2.1 — Laravel 9 · PHP 8.2 + ionCube Loader 15.5 · MySQL 8.
- ثلاث طبقات: `design_1` (Stisla قديم — Mix) + `landing_v1` (تسويقية — Vite+Tailwind) + `panel_v1` (لوحات Student/Instructor/Admin/Organization — Vite).
- الحالة: **Student/CoursePlayer/Landing/Instructor/Organization/Admin 100% حقيقي** (`v1` بديل حرفي كامل 1:1) — صفر Mock في الإنتاج (تمت إزالة `InstructorMockData` و `CoursePlayerMockData` من `PanelV1` — `AdminMockData` باقٍ للـ `shell` فقط).
- الترخيص مربوط بـ `training.qiec.local` — `localhost` يُحول لـ `/purchase-code`.

---

## 2. القواعد الصلبة — لا تتجاوزها

1. **لا `git commit` ولا `git push` بدون إذن صريح** — العمل في `working tree` فقط.
2. **لا تلمس `ionCube`** (`PurchaseCode`, `LicenseService`, `routes/admin.php`, أي `//00507`).
3. **لا ترفع أسراراً:** `.env`, `.env.testing`, `firebase-auth.json`, `*.pem`, `*.sql`, `storage/logs/*`, `vendor/`, `node_modules/`, `public/build`, `public/assets/`, `public/vendor/` — كلها `gitignored`.
4. **لا `docker compose down -v`** إلا بأمر صريح — يمسح `qiec-mysql-data`.
5. **لا تغيير PHP/ionCube** خارج `8.2 + 15.5` إلا بعد مناقشة.
6. **أي رقم/قائمة من DB** — لا Mock في الإنتاج (المسموح `StudentV1DemoDataSeeder` للاختبار فقط).

---

## 3. كيف تشغّل المشروع

### المسار المدعوم: Docker — الملفات في `/tmp/qiec-docker/` لا تُنقل

```bash
echo '127.0.0.1 training.qiec.local' | sudo tee -a /etc/hosts
cd /tmp/qiec-docker && docker compose up -d   # qiec-app:8000 + qiec-db:3307 (db:3306)
docker ps --filter name=qiec
```

### أوامر artisan — كـ www-data بعد الإعداد

```bash
docker exec -u www-data -w /var/www/html qiec-app php artisan config:clear
docker exec -u www-data -w /var/www/html qiec-app php artisan migrate --force
docker exec -u www-data -w /var/www/html qiec-app php artisan db:seed --force
```

كـ `root` فقط لتثبيت حزم، ثم صحح:

```bash
docker exec -w /var/www/html qiec-app chown -R www-data:www-data storage bootstrap/cache
```

### الأصول

```bash
npm install
npx mix --production        # admin + design_1 → public/assets/* (gitignored)
npm run build:landing       # landing_v1 + panel_v1 → public/build (gitignored)
docker exec -w /var/www/html qiec-app bash -c "mkdir -p public/vendor/laravel-filemanager && cp -r vendor/unisharp/laravel-filemanager/public/* public/vendor/laravel-filemanager/"
```

---

## 4. بيئات العمل

| المتغير | محلي | اختبار | إنتاج |
|---|---|---|---|
| ملف | `.env` | `.env.testing` (`APP_ENV=testing`, DB `qiec_testing`) | `.env` على السيرفر |
| `APP_DEBUG` | `true` | `true` | **`false`** |
| `APP_URL` | `http://training.qiec.local:8000` | `http://localhost` | `https://training.qiec.sa` بلا `/` |
| `MAIL_MAILER` | `log` | `array` | `smtp` (hostinger) |
| `DB_DATABASE` | `qiec` | `qiec_testing` | `u873288737_markaz` |

> بعد تعديل `.env` على السيرفر: `rm ~/domains/training.qiec.sa/public_html/bootstrap/cache/config.php`.

---

## 5. قاعدة البيانات

- 471 migration — 11 مُصلحة idempotent — لا ترجعها.
- لوكال: `2026_09_08_000001_create_purchase_code_table.php`.
- `migrate:status` + `SHOW TABLES` للتحقق — `qiec_testing` تُهاجر مرة واحدة ثم `DatabaseTransactions`.

**Seeders:**
```bash
docker exec -w /var/www/html qiec-app php artisan db:seed --force
docker exec -w /var/www/html qiec-app php artisan qiec:seed-landing-demo      # 6 مدربين + 14 دورة
docker exec -w /var/www/html qiec-app php artisan db:seed --class=StudentV1DemoDataSeeder --force  # مناطق/بنوك/مشتريات/اختبارات للاختبار
```

---

## 6. المعمارية — أين تعدّل (محدّث)

| تريد تعديل | عدّل هنا | لا تلمس | الحالة |
|---|---|---|---|
| واجهة تسويقية | `resources/views/landing_v1/` + `LandingV1Controller` | `design_1/web` | 100% حقيقي |
| لوحة طالب | `resources/views/panel_v1/student/` + `StudentController` | `design_1/panel` | 100% حقيقي (23 route) |
| مشغل الدورة | `resources/views/panel_v1/student/course-player/` + `CoursePlayerController::buildPlayerData()` | `design_1/web/courses/learning_page` | 100% حقيقي (كان Mock) |
| لوحة مدرب | `resources/views/panel_v1/instructor/` + `InstructorController` | `design_1/panel` | 95% (باقي `courseWatch` سابقاً) |
| لوحة منظمة | `resources/views/panel_v1/organization/` + `OrganizationController` | — | 100% حقيقي (6 تابات `ProfileSettingsTrait`) |
| لوحة إدارة | `resources/views/panel_v1/admin/` + `Admin/*Controller` | `design_1/admin` | 100% حقيقي (`59/59 tests`) |
| ثيم إدارة | `resources/sass/admin/qiec-theme.scss` → `custom.css` | `style.css` (Stisla) | — |
| تحويل بعد الدخول | `app/Helpers/helper.php:panelV1HomeUrl()` | `Role::$*` | لا يزال للقديم حتى التفعيل |
| ترتيب mix | `webpack.mix.js` | `vite.config.js` للجديد | — |

**قاعدة:** `panel_v1` لا تستورد `design_1`، والعكس.

---

## 7. الاختبارات

```bash
docker exec -w /var/www/html -e APP_ENV=testing qiec-app vendor/bin/phpunit
docker exec -w /var/www/html -e APP_ENV=testing qiec-app vendor/bin/phpunit --testsuite=Unit
docker exec -w /var/www/html -e APP_ENV=testing qiec-app vendor/bin/phpunit --testsuite=Feature --testdox
docker exec -w /var/www/html qiec-app php test_comprehensive.php  # اختبار شامل 30+ مسار v1 بداتا واقعية
```

- قاعدة `qiec_testing` + `DatabaseTransactions`.
- **52 اختبار (93 assertion)** — يغطي: أسعار، `panelV1HomeUrl`, `Setting` fallback, أدوار `User`, `ReserveMeeting`, guards الأربعة، ملكية `teacherWebinarOrFail`, اختبارات `passed/failed/waiting`, ترخيص, `Student` 9 حالات مباشرة.
- إصلاحان: `convertPriceToDefaultCurrency` + `User::isAdmin()` null-safe + `AdminController:82` `]));`.

**قاعدة جودة:** أي `MockData` في الإنتاج مرفوض — الاختبار يجب أن يستخدم `DB` حقيقية (`Sale::where`, `Webinar::where`).

---

## 8. النشر

راجع `_docs/DEPLOYMENT.md` + `ENV_PRODUCTION_CHECKLIST.md` + `FIRST_DEPLOY_CHECKLIST.md`.

**الخلاصة:** `build` محلياً (`mix` + `vite`) → `git push` (بإذن) → `git pull` على السيرفر → نقل `public/assets|build|vendor` يدوياً (gitignored) → `migrate --force && storage:link && config:clear` (كـ `www-data`) → `APP_DEBUG=false`.

> `_docs/` تحتوي باسوردات تاريخية — لا تنسخها في ردود.

---

## 9. أسلوب العمل للوكيل — موسّع

### قبل التعديل
- اقرأ `README.md` + `AGENTS.md` + `_docs/README.md` + `vite.config.js`/`webpack.mix.js` قبل أي `edit`.
- استخدم `Read/Grep/Glob` قبل `Edit`؛ لا تستخدم `bash` للقراءة.

### أثناء التعديل
- غيّر أصغر نطاق ممكن — لا تحذف أسطر غير مطلوبة.
- أي استيراد جديد `use` → تحقق `php -l`.
- لا تنشئ ملفات إلا عند الضرورة — عدّل الموجود.
- للـ `Blade`: استخدم `@php $x=...; @endphp` لا `@php($x=...)` (يسبب `unexpected token "@"`).

### التحقق (إجباري)
- شغّل `php artisan view:clear && php -l` + `phpunit` قبل التسليم — لا تسلّم بدون تنفيذ.
- اختبر بداتا واقعية: `User::find(2)` (11 شراء) عبر `StudentController::home()` + `CoursePlayerController::watch()` مباشرة.
- عند الشك في `ionCube`/الدفع/الصلاحيات → اسأل.

### التواصل
- رسائلك قصيرة ومباشرة، بلا مدح — أشر للملف:السطر (`file:line`).
- عند تضارب الأدلة → اذكر الفرضيات والنتائج، وثق بالأدلة المحلية.
- لا `commit/push` إلا بأمر صريح — اعرض `git status/diff` أولاً وافحص الأسرار.

### الأدوار
- **مطور V1:** ينقل `design_1` → `v1` بمنطق حقيقي، يزيل `MockData`، يكتب `tests/Feature/PanelV1/*`.
- **مصمم:** يطابق Figma (`Figma → Tailwind` في `landing_v1/panel_v1` مع `rounded-[12px] bg-[#0F3D36]`).
- **مدقق:** يشغّل `test_comprehensive.php` (30+ مسار) ويتأكد من `phpunit` أخضر.

---

## 10. جهات اتصال المشروع

- **الإنتاج:** `https://training.qiec.sa`
- **المستودع:** `git@github.com:mahmoud-wahba-dev/qice-rocket-lms.git` (https: `https://github.com/mahmoud-wahba-dev/qice-rocket-lms.git`)
- **المجلد المحلي:** `/home/hmh/IdeaProjects/qice-rocket-lms`
- **الـ Docker:** `/tmp/qiec-docker` (لا تنقله) — `qiec-app:8000` + `qiec-db:3307` (`db:3306`)
- **المضيف:** `hmh@hmh-MS-7C96` — `~/Desktop` للتقارير (`v1_nawaqis_taqrir.md`)

---

## 11. خارطة الطريق المحدثة (بعد `bccbd78`)

- **تم:** `Student` 100% + `CoursePlayer` حقيقي + `Landing` + `Figma home` + `Instructor` 100% (`support/marketing/assignments/courseWatch/Performance` → حقيقي) + `Organization` 100% (`settings` 6 تابات) + `Admin` 100% (`education/sales/marketing/system` كلها `DB` حقيقي) — `59/59`
- **المتبقي:** تفعيل `panelV1HomeUrl()` + `build` `public/build|assets`
- **التالي:** تفعيل التحويل وبدء `QA` النهائي قبل الإنتاج

> **تذكير أخير:** عند فتح `http://training.qiec.local:8000/purchase-code` مباشرة سترى نموذج الكود — هذا طبيعي. التحويل التلقائي يحدث فقط عند فشل التحقق أو دخول `localhost`.
