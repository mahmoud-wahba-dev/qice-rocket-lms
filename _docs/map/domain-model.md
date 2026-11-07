# Domain model

Models are in `app/Models` (over 200). `User` is at `app/User.php`. Courses are called **webinars** in code, even when they're recorded courses.

## Key entities

| Entity | Table | Notes |
|---|---|---|
| User | `users` | All people, including organizations |
| Role | `roles` | `Role::$admin`, `$user` (student), `$teacher`, `$organization` |
| Webinar | `webinars` | Chapters, sessions, files, quizzes, assignments, reviews, sales, tickets; translated via `webinar_translations` |
| Bundle | `bundles` | Groups webinars via `bundle_webinars` |
| Order, OrderItem | `orders`, `order_items` | Precede sales |
| Sale | `sales` | Typed: webinar, meeting, subscribe, promotion, package, product, bundle, gift, installment |
| Accounting | `accounting` | Financial ledger |
| Quiz | `quizzes` | `quizzes_questions`; results in `QuizzesResult` |
| Certificate | `certificates` | `certificates_templates`; links quiz, student, webinar, bundle |
| WebinarAssignment | `webinar_assignments` | `webinar_assignment_history` |
| Meeting | `meetings` | `meeting_times`, `reserve_meetings` |
| Forum | `forums` | `forum_topics`; course forums use `course_forums` |
| Support | `supports` | `support_conversations`, departments |

## Roles

- Stored as `users.role_name` plus `users.role_id`.
- Check with `isAdmin()`, `isTeacher()`, `isOrganization()`, `isUser()`.
- `isAdmin()` uses `roles.is_admin`, so custom admin roles work.
- `hasPermission()` goes through `Permission` to `Section`.
- **Instructor approval:** a teacher who registers stays `status = pending` after contact verification (`User::requiresAdminApproval()`; checked in `RegisterController`, `LoginController::afterLogged`, `VerificationController::confirmCode`). An admin activates them from admin > users > Teachers tab ("قبول المدرب"/"رفض الطلب") or the instructor-requests section, which call `SystemController::approveInstructorRequest`/`rejectInstructorRequest`. Public pages only show `status = active` instructors. Students and organizations still activate on verification.
- **There's no Organization model.** An organization is a `User`; members link to it through `users.organ_id`.

## Course seats and waitlist

`webinars.capacity` (null = unlimited) is compared with non-refunded sales (`Webinar::getWebinarCapacity()`, `checkCapacityReached()`, `canSale()`). `enable_waitlist` turns on the waitlist, stored in `waitlists` and listed in admin at `/v1/admin/education/waitlists`. On the v1 paid course page (`course-details-paid`), `LandingV1Controller::courseSeatsData()` feeds a seats chip and, when full, both CTAs open `landing_v1/components/seats-full-modal`, which posts to `POST /course/{slug}/waitlists/join` (`Web\WaitlistController::store`, rejects courses with the waitlist disabled). Cart add is blocked for full courses (logged-in via `checkCourseForSale`, guests in `CartManagerController::store`). The free course page does not use this yet.

## Disabled and archived courses

`webinars.status`: `active`, `pending`, `is_draft`, `inactive` (admin "تعطيل"), `archived` (added by migration `2026_11_08_000001`). Public queries filter `status = 'active'`, so inactive and archived courses vanish from the site. Buyers keep access: the student dashboard, course player and purchased-course helpers use `Webinar::$buyerVisibleStatuses` (active, inactive, archived) and `checkUserHasBought()`, which enforces `access_days` per student; when a student's access ends the course leaves their dashboard and the player blocks them.

Archive: admins archive any course from the course list ("أرشفة", `EducationController::archiveCourse`) and restore it from `/v1/admin/education/archived` ("إلغاء الأرشفة" returns it to `inactive`). `courses:archive-finished [--dry-run]` archives live courses whose last session (`Webinar::getEndTimestamp()`) has passed; its daily schedule is off unless `AUTO_ARCHIVE_FINISHED_COURSES=true`.

## Certificates

`app/Mixins/Certificate/MakeCertificate.php` builds certificate data and renders `panel_v1/certificates/qiec-template`.

Certificates are bilingual:
- Trainee names are stored in `certificates.meta` (`trainee_name_ar`, `trainee_name_en`) and remembered in `user_metas` (`full_name_ar`, `full_name_en`). Students complete them in a modal before downloading.
- Course titles come from `webinar_translations` (`ar` and `en`), collected in course wizard step 3 when the certificate toggle is on.

Details: `_docs/plans/certificate-bilingual-data.plan.md`. Test: `tests/Feature/PanelV1/CertificateBilingualDataTest.php`.

## Helpers

Global functions in `app/Helpers/helper.php`:

| Function | Purpose |
|---|---|
| `getTemplate()` | Active legacy template |
| `dateTimeFormat()` | Date formatting |
| `currency()`, `handlePrice()` | Currency and price display |
| `convertPriceToUserCurrency()`, `convertPriceToDefaultCurrency()` | Currency conversion |
| `sendNotification()` | User notifications |
| `panelV1HomeUrl()` | Role-based home after login |
| `panelV1PublicUrl()`, `panelV1FilePreviewItem()` | File URLs and previews for v1 |

Other reusable code:

- `Setting::get*Settings()`: site settings
- `app/Mixins/`: business logic shared with legacy. Check here before writing new logic.
- `PanelV1/Support/`: v1 shared traits
- `app/Services/Media/VideoFaststart.php`: video upload processing

Files: the public disk root is `public/store` (`config/filesystems.php`).

## Artisan commands

| Command | Purpose |
|---|---|
| `qiec:seed-landing-demo` | Demo instructors and courses |
| `qiec:seed-student-demo` | Student demo data |
| `qiec:platform-status` | Platform status |
| `qiec:test-mail` | Send a test mail |
| `qiec:recover-paid-orders` | Recover paid orders |
| `qiec:backfill-payment-channels` | Backfill payment channels |
| `qiec:fix-assignment-storage-paths` | Repair assignment file paths |
| `qiec:reset-admin-password` | Reset admin password |
| `clear:all` | Clear caches |

Read a command's source before running it, as several change data.

## Integrations

- **Payments:** 44 drivers in `app/PaymentChannels/Drivers`, including Stripe, PayPal, Paymob, MyFatoorah, Tabby, Tamara, TapPayment and Paylink. Which ones are enabled is configured in the admin panel, not in code.
- **Live classes:** Zoom, Agora, BigBlueButton
- **Messaging:** SMTP, Firebase, Twilio, Vonage, Msegat, Kavenegar
- **Storage and media:** BunnyCDN, S3/MinIO, YouTube, Google Drive
- **Other:** OpenAI, Google Calendar, Google Translate, Socialite, JWT
- **PDF and Excel:** dompdf, mpdf, Laravel Excel
