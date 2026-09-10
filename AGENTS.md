# AGENTS — تعليمات وكلاء التطوير لمشروع QICE

> هذا الملف للوكلاء (AI agents) والأدوات الآلية العاملة على هذا المستودع. الأولوية: **عدم كسر الإنتاج، عدم تسريب أسرار، عدم الرفع بدون إذن**.

---

## 1. ما هذا المشروع

- **QIEC للتدريب** على RocketLMS v2.1 — Laravel 9 · PHP 8.2 + ionCube Loader 15.5 · MySQL 8.
- طبقتان مضافتان: `landing_v1` (تسويقية — Vite+Tailwind) و`panel_v1` (لوحات Student/Instructor/Admin/Organization).
- الثيم القديم `design_1` (admin + panel) لا يزال فعّالاً ويُبنى بـ Mix.
- الترخيص مربوط بـ `training.qiec.local` — `localhost` يُحول لصفحة التفعيل.

---

## 2. القواعد الصلبة — لا تتجاوزها

1. **لا `git commit` ولا `git push` بدون إذن صريح من المالك** — كل العمل يبقى في working tree، المالك يرفع.
2. **لا تلمس ملفات ionCube المشفرة** (`PurchaseCode`, `LicenseService`, `routes/admin.php` ومحتوى يبدأ بـ `//00507`).
3. **لا ترفع أسراراً** إلى git: `.env`, `.env.testing`, `firebase-auth.json`, `*.pem`, `*.sql`, `storage/logs/*`, `vendor/`, `node_modules/`, `public/build`, `public/assets/`, `public/vendor/` — كلها `gitignored` لسبب.
4. **لا تحذف volume الداتابيز** (`docker compose down -v`) إلا بأمر صريح — يمسح البيانات.
5. **لا تقترح تغيير PHP أو ionCube** خارج `8.2 + 15.5` إلا بعد مناقشة — المنصة الأساسية مقفلة عليهما.

---

## 3. كيف تشغّل المشروع

### المسار المدعوم: Docker

الملفات في `/tmp/qiec-docker/` (Dockerfile + docker-compose.yml) — لا تنقلها. الحاويات: `qiec-app:8000` و`qiec-db:3307` (داخلياً `db:3306`). الموقع: `http://training.qiec.local:8000` بعد سطر hosts:

```bash
echo '127.0.0.1 training.qiec.local' | sudo tee -a /etc/hosts
cd /tmp/qiec-docker && docker compose up -d
```

### أوامر artisan — نفّذها كـ www-data

```bash
docker exec -u www-data -w /var/www/html qiec-app php artisan config:clear
docker exec -u www-data -w /var/www/html qiec-app php artisan migrate --force
```

نفّذ كـ `root` فقط عند تثبيت الحزم؛ ثم صحح الملكية:

```bash
docker exec -w /var/www/html qiec-app chown -R www-data:www-data storage bootstrap/cache
```

### الأصول

```bash
npm install
npx mix --production        # admin + design_1 → public/assets/*
npm run build:landing       # landing_v1 + panel_v1 → public/build
# نسخ filemanager (vendors لا يُبنى بـ Mix هنا)
docker exec -w /var/www/html qiec-app bash -c "mkdir -p public/vendor/laravel-filemanager && cp -r vendor/unisharp/laravel-filemanager/public/* public/vendor/laravel-filemanager/"
```

---

## 4. بيئات العمل

| المتغير | محلي | اختبار | إنتاج |
|---|---|---|---|
| ملف | `.env` | `.env.testing` (`APP_ENV=testing`, DB `qiec_testing`) | `.env` على السيرفر |
| `APP_DEBUG` | `true` | `true` | **`false`** |
| `APP_URL` | `http://training.qiec.local:8000` | `http://localhost` | `https://training.qiec.sa` (بلا `/`) |
| `MAIL_MAILER` | `log` | `array` | `smtp` (hostinger) |
| `DB_DATABASE` | `qiec` | `qiec_testing` | `u873288737_markaz` |

> بعد أي تعديل لـ `.env` على السيرفر: `rm ~/domains/training.qiec.sa/public_html/bootstrap/cache/config.php`.

---

## 5. قاعدة البيانات

- 471 migration — 11 منها مُصلحة محلياً لتكون idempotent (FK وهمية/أعمدة ناقصة) — لا ترجعها لأصلها المكسور.
- migration لوكال إضافية: `2026_09_08_000001_create_purchase_code_table.php`.
- seeders: `php artisan db:seed` → `DefaultGeneralSeeder` + `DefaultFinancialSeeder` + `UsersTableSeeder` + `qiec:seed-landing-demo` (اختياري).

---

## 6. المعمارية — أين تعدّل

| تريد تعديل | عدّل هنا | لا تلمس |
|---|---|---|
| واجهة تسويقية | `resources/views/landing_v1/` + `LandingV1Controller` | `resources/views/design_1/` |
| لوحة طالب/مدرب جديدة | `resources/views/panel_v1/` + `PanelV1/*Controller` | `resources/views/design_1/panel/` (القديمة) |
| ثيم إدارة QIEC | `resources/sass/admin/qiec-theme.scss` → `public/assets/admin/css/custom.css` | `public/assets/admin/css/style.css` (مُولّد من Stisla) |
| تحويل بعد الدخول | `app/Helpers/helper.php:panelV1HomeUrl()` | `Role::$*` ثوابت |
| ترتيب mix | `webpack.mix.js` | `vite.config.js` للجديد |

---

## 7. الاختبارات

```bash
docker exec -w /var/www/html -e APP_ENV=testing qiec-app vendor/bin/phpunit
docker exec -w /var/www/html -e APP_ENV=testing qiec-app vendor/bin/phpunit --testsuite=Unit
docker exec -w /var/www/html -e APP_ENV=testing qiec-app vendor/bin/phpunit --testsuite=Feature
```

- قاعدة `qiec_testing` تُهاجر مرة واحدة ثم تُدار بـ `DatabaseTransactions` (لا `RefreshDatabase`).
- 43 اختبار تغطي: حراس الأسعار، `panelV1HomeUrl`، `Setting` fallback، أدوار `User`, علاقة `ReserveMeeting`, guards الأدوار الأربعة، حماية الملكية، منطق الاختبارات، تحقق purchase code.
- إصلاحان موثقان: `convertPriceToDefaultCurrency` + `User::isAdmin()` null-safe.

---

## 8. النشر

راجع `_docs/DEPLOYMENT.md` و`_docs/ENV_PRODUCTION_CHECKLIST.md`.

**الخلاصة:** `build` محلياً (`mix` + `vite`) → `git push` (بإذن) → `git pull` على السيرفر → نقل `public/assets/` + `public/build/` + `public/vendor/` يدوياً (gitignored) → `migrate --force` → `storage:link`.

> تذكير: `_docs/` تحتوي باسوردات إنتاج تاريخية — لا تنسخها في ردود أو تذاكر.

---

## 9. أسلوب العمل للوكيل

- اقرأ `README.md` و`AGENTS.md` و`_docs/README.md` قبل أي تعديل.
- استخدم أدوات القراءة المتخصصة قبل `edit`؛ تحقق عبر التنفيذ (شغّل اختبار/أمر) قبل التسليم.
- رسائلك قصيرة ومباشرة، بلا مدح — أشر للملف:السطر عند الحاجة.
- عند الشك، اسأل بدل التخمين — خاصة في ionCube والدفع والصلاحيات.

---

## 10. جهات اتصال المشروع

- **الإنتاج:** `https://training.qiec.sa`
- **المستودع:** `git@github.com:mahmoud-wahba-dev/qice-rocket-lms.git`
- **المجلد المحلي (هذا الجهاز):** `/home/hmh/IdeaProjects/qice-rocket-lms`
- **مجلد Docker:** `/tmp/qiec-docker`
