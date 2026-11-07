# Routes and controllers (v1)

## `routes/panel_v1.php`

URL prefix `v1`, name prefix `panel.v1.`, fully qualified controller classes. It inherits the `web` group. About 580 routes.

| Group | URL | Controller | Size |
|---|---|---|---|
| Student | `/v1/student` | `StudentController` | Large |
| Course player | `/v1/student/courses/{slug}/...` | `CoursePlayerController` | Medium |
| Instructor | `/v1/instructor` | `InstructorController` | Large |
| Organization | `/v1/organization` | `OrganizationController` | Small |
| Admin home | `/v1/admin` | `AdminController` | 1 route |
| Admin education | `/v1/admin/education` | `Admin\EducationController` | Largest admin group |
| Admin marketing | `/v1/admin/marketing` | `Admin\MarketingController` | Large |
| Admin system | `/v1/admin/system` | `Admin\SystemController`, `Admin\SettingsController` | Large |
| Admin sales | `/v1/admin/sales` | `Admin\SalesController` | Medium |
| Leave impersonation | `/v1/leave-impersonation` | `Admin\SystemController` | 1 route |

`/v1/v1/{path}` is a 301 cleanup for accidental double prefixes.

**Order matters:** the `/{section}` catch-all in the admin system group must stay last.

## Middleware

| Routes | Middleware |
|---|---|
| Student, instructor, organization | `web, impersonate, panel, share, check_maintenance, check_restriction` |
| Admin | `web, admin` |
| Leave impersonation | `web, impersonate, share, check_maintenance` (must not use `panel`, which rejects admins) |
| Landing (`routes/web.php`) | `check_mobile_app, impersonate, share, check_maintenance, check_restriction` |

| Alias | Class | Behaviour |
|---|---|---|
| `panel` | `PanelAuthenticate` | Sends guests **and admins** to `/login` |
| `admin` | `AdminAuthenticate` | Requires `isAdmin()` |
| `web.auth` | `WebAuthenticate` | Sends guests to `/login` |
| `impersonate` | `Impersonate` | Swaps the request user for admin "login as" |
| `share` | `Share` | Shares view data and sets locale; has a lighter path for landing |

`UserLocale` runs in the `web` group. The admin group has no `share`, and `AdminLocale` exists, but how admin locale is applied to v1 is unverified.

Guards: `web` (session) for all panels; `api` (JWT) for the REST API.

## Role checks

`panel` doesn't separate roles, so each controller has its own resolver. A resolver returns the user, or a redirect to the correct panel:

| Controller | Resolver |
|---|---|
| `StudentController`, `CoursePlayerController` | `resolveStudent()` (the player also has `resolveCourse()` and `resolveQuiz()`) |
| `InstructorController` | `resolveInstructor()`, plus `teacherWebinarOrFail()` for ownership |
| `OrganizationController` | `resolveOrganization()` |
| `AdminController` and subclasses | `resolveAdmin()`, or `renderAdmin()` which calls it |

## Controllers

```
app/Http/Controllers/
├─ Web/LandingV1Controller.php        public site, cart, checkout, account
└─ PanelV1/
   ├─ StudentController               dashboard pages and actions (~2k lines)
   ├─ InstructorController            courses, quizzes, bundles, finance (~6.5k lines)
   ├─ CoursePlayerController          watch, quiz, assignment, forum (~1.7k lines)
   ├─ OrganizationController          members, courses, settings
   ├─ AdminController                 base class: resolveAdmin, renderAdmin
   ├─ Admin/
   │  ├─ EducationController          departments, courses, quizzes, enrolment (~3k lines)
   │  ├─ SystemController             users, settings, impersonation, import
   │  ├─ SalesController              sales, payments, offline approval
   │  ├─ MarketingController          discounts, forms, blog, newsletter
   │  ├─ SettingsController           settings forms, wraps legacy controller
   │  └─ WebinarExtraDescriptionController
   └─ Support/
      ├─ CourseWizardTrait            course create/edit wizard (instructor)
      ├─ ProfileSettingsTrait         settings tabs (student, instructor, org, landing)
      ├─ CoursePerformanceBuilder     used by EducationController
      └─ AdminMockData                live admin nav/shell code despite the name
```

`InstructorController` is too large to read whole. Grep for the method name and read that range.

`AdminMockData::shell()` is called from admin pages, and the file is listed in Tailwind `content` so its icon classes get generated. New admin nav items go in `educationNav()`, `salesNav()`, `marketingNav()` or `systemNav()` in that class.

## Adding a page

**Student example:**

1. Add a route in the student group of `routes/panel_v1.php`:
   ```php
   Route::get('/student/reports', [StudentController::class, 'reports'])->name('student.reports');
   ```
2. Add the action:
   ```php
   public function reports(Request $request)
   {
       $user = $this->resolveStudent($request);
       if ($user instanceof \Illuminate\Http\RedirectResponse) {
           return $user;
       }

       return view('panel_v1.student.pages.reports', [
           'pageTitle' => 'التقارير',
           'user' => $user,
       ]);
   }
   ```
3. Create `resources/views/panel_v1/student/pages/reports.blade.php`:
   ```blade
   @extends('panel_v1.student.layouts.app')

   @section('content')
       ...
   @endsection
   ```
4. Add the nav link. Student: `panel_v1/student/layouts/navbar.blade.php`. Instructor and organization: their `components/sidebar`. Admin: the nav methods in `AdminMockData`.
5. Run `php artisan view:clear` and check it in the browser as that role.

**Admin pages** use `$this->renderAdmin($request, $view, $pageTitle, $data)` in a subclass of `AdminController`. `renderAdmin()` calls `sidebarContext()`, which fills the sidebar nav if the page passed no shell and highlights the item whose URL is the longest prefix of the request path. So a new admin page only needs a nav item (in the `AdminMockData` nav methods) whose route URL is a prefix of the page URL; no manual `adminActive` is needed.

## Listing routes

`php artisan route:list` crashes here: it instantiates every controller, and `Web\ForumController::__construct` calls `abort(403)` when forums are disabled. Grep `routes/`, or bootstrap the app in a throwaway script and read `Route::getRoutes()->getRoutes()`, which doesn't instantiate controllers.
