<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PanelV1\StudentController;use App\Http\Controllers\PanelV1\CoursePlayerController;
use App\Http\Controllers\PanelV1\InstructorController;
use App\Http\Controllers\PanelV1\AdminController;
use App\Http\Controllers\PanelV1\OrganizationController;

/*
|--------------------------------------------------------------------------
| Panel V1 Routes (new redesign shells)
|--------------------------------------------------------------------------
|
| Isolated from design_1 panel and legacy admin UI.
| Post-login redirects remain on the old dashboards until switched later.
|
*/

Route::prefix('v1')->name('panel.v1.')->group(function () {

    Route::middleware(['impersonate', 'panel', 'share', 'check_maintenance', 'check_restriction'])
        ->group(function () {
            Route::get('/student', [StudentController::class, 'home'])->name('student.home');
            Route::post('/student/calendar-events', [StudentController::class, 'storeCalendarEvent'])->name('student.calendar-events.store');
            Route::post('/student/calendar-events/{id}/delete', [StudentController::class, 'deleteCalendarEvent'])->name('student.calendar-events.delete');
            Route::get('/student/notifications', [StudentController::class, 'notifications'])->name('student.notifications');
            Route::post('/student/notifications/mark-all-read', [StudentController::class, 'markAllNotificationsRead'])
                ->name('student.notifications.mark-all-read');
            Route::get('/student/purchases', [StudentController::class, 'purchases'])->name('student.purchases');
            Route::post('/student/payouts/request', [StudentController::class, 'requestPayout'])->name('student.payouts.request');
            Route::get('/student/support', [StudentController::class, 'support'])->name('student.support');
            Route::post('/student/support', [StudentController::class, 'storeSupport'])->name('student.support.store');
            Route::get('/student/settings', [StudentController::class, 'settings'])->name('student.settings');
            Route::post('/student/settings', [StudentController::class, 'updateSettings'])->name('student.settings.update');
            Route::post('/student/settings/extra', [StudentController::class, 'updateExtra'])->name('student.extra.update');
            Route::post('/student/settings/financial', [StudentController::class, 'updateFinancial'])->name('student.financial.update');
            Route::post('/student/settings/images', [StudentController::class, 'updateImages'])->name('student.images.update');
            Route::post('/student/settings/media/{type}/delete', [StudentController::class, 'deleteMedia'])->name('student.media.delete');
            Route::post('/student/settings/about', [StudentController::class, 'updateAbout'])->name('student.about.update');
            Route::post('/student/settings/metas', [StudentController::class, 'storeMeta'])->name('student.metas.store');
            Route::post('/student/settings/metas/{metaId}/update', [StudentController::class, 'updateMeta'])->name('student.metas.update');
            Route::post('/student/settings/metas/{metaId}/delete', [StudentController::class, 'deleteMeta'])->name('student.metas.delete');
            Route::post('/student/settings/attachments', [StudentController::class, 'storeAttachment'])->name('student.attachments.store');
            Route::post('/student/settings/attachments/{attachmentId}/update', [StudentController::class, 'updateAttachment'])->name('student.attachments.update');
            Route::post('/student/settings/attachments/{attachmentId}/delete', [StudentController::class, 'deleteAttachment'])->name('student.attachments.delete');
            Route::post('/student/settings/sessions/{sessionId}/end', [StudentController::class, 'endSession'])->name('student.sessions.end');
            Route::get('/student/favorites', [StudentController::class, 'favorites'])->name('student.favorites');
            Route::post('/student/favorites/toggle', [StudentController::class, 'toggleFavorite'])->name('student.favorites.toggle');
            Route::get('/student/notes', [StudentController::class, 'notes'])->name('student.notes');
            Route::post('/student/notes', [StudentController::class, 'storeNote'])->name('student.notes.store');
            Route::post('/student/notes/{id}/delete', [StudentController::class, 'deleteNote'])->name('student.notes.delete');
            Route::get('/student/certificates', [StudentController::class, 'certificates'])->name('student.certificates');
            Route::get('/student/certificates/{id}/download', [StudentController::class, 'downloadCertificate'])->name('student.certificates.download');
            Route::get('/student/assignments', [StudentController::class, 'assignmentsPage'])->name('student.assignments');
            Route::get('/student/quizzes', [StudentController::class, 'quizzesPage'])->name('student.quizzes');
            Route::get('/student/comments', [StudentController::class, 'commentsPage'])->name('student.comments');
            Route::post('/student/comments/{id}/delete', [StudentController::class, 'deleteComment'])->name('student.comments.delete');
            Route::get('/student/meetings', [StudentController::class, 'meetings'])->name('student.meetings');
            Route::get('/student/meetings/{id}/join', [StudentController::class, 'meetingsJoin'])->name('student.meetings.join');
            Route::post('/student/meetings/{id}/finish', [StudentController::class, 'meetingsFinish'])->name('student.meetings.finish');
            Route::get('/student/support/{id}', [StudentController::class, 'supportShow'])->name('student.support.show');
            Route::post('/student/support/{id}/reply', [StudentController::class, 'storeSupportConversation'])->name('student.support.reply');
            Route::post('/student/support/{id}/close', [StudentController::class, 'closeSupport'])->name('student.support.close');
            Route::get('/student/noticeboards', [StudentController::class, 'noticeboards'])->name('student.noticeboards');
            Route::post('/student/noticeboards/{id}/seen', [StudentController::class, 'noticeboardSeen'])->name('student.noticeboards.seen');
            Route::get('/student/rewards', [StudentController::class, 'rewards'])->name('student.rewards');
            Route::post('/student/rewards/exchange', [StudentController::class, 'rewardExchange'])->name('student.rewards.exchange');
            Route::get('/student/attendances', [StudentController::class, 'attendances'])->name('student.attendances');
            Route::get('/student/upcoming', [StudentController::class, 'upcomingCourses'])->name('student.upcoming');
            Route::post('/student/upcoming/{id}/follow', [StudentController::class, 'upcomingFollow'])->name('student.upcoming.follow');
            Route::post('/student/upcoming/{id}/unfollow', [StudentController::class, 'upcomingUnfollow'])->name('student.upcoming.unfollow');
            Route::get('/student/forums', [StudentController::class, 'forums'])->name('student.forums');
            Route::post('/student/forums/{topicId}/bookmark', [StudentController::class, 'forumBookmarkToggle'])->name('student.forums.bookmark');
            Route::post('/student/comments/{id}/update', [StudentController::class, 'updateComment'])->name('student.comments.update');
            Route::post('/student/comments/{id}/report', [StudentController::class, 'reportComment'])->name('student.comments.report');
            Route::get('/student/installments', [StudentController::class, 'installments'])->name('student.installments');

            Route::prefix('student/courses/{slug}')->name('student.course.')->group(function () {
                Route::get('/watch', [CoursePlayerController::class, 'watch'])->name('watch');
                Route::post('/comments', [CoursePlayerController::class, 'storeComment'])->name('comments.store');
                Route::get('/forum', [CoursePlayerController::class, 'forum'])->name('forum');
                Route::post('/forum', [CoursePlayerController::class, 'storeForumTopic'])->name('forum.store');
                Route::get('/assignment', [CoursePlayerController::class, 'assignment'])->name('assignment');
                Route::post('/assignment', [CoursePlayerController::class, 'submitAssignment'])->name('assignment.submit');
                Route::get('/quiz', [CoursePlayerController::class, 'quiz'])->name('quiz');
                Route::get('/quiz/take', [CoursePlayerController::class, 'quizTake'])->name('quiz.take');
                Route::post('/quiz/answer', [CoursePlayerController::class, 'quizAnswer'])->name('quiz.answer');
            });

            Route::get('/instructor', [InstructorController::class, 'home'])->name('instructor.home');
            Route::get('/instructor/students', [InstructorController::class, 'students'])->name('instructor.students');
            Route::get('/instructor/courses', [InstructorController::class, 'courses'])->name('instructor.courses');
            Route::get('/instructor/bundles', [InstructorController::class, 'bundles'])->name('instructor.bundles');
            Route::post('/instructor/bundles/store', [InstructorController::class, 'storeBundle'])
                ->name('instructor.bundles.store');
            Route::get('/instructor/bundles/{id}/edit', [InstructorController::class, 'editBundle'])
                ->whereNumber('id')
                ->name('instructor.bundles.edit');
            Route::post('/instructor/bundles/{id}/update', [InstructorController::class, 'updateBundle'])
                ->whereNumber('id')
                ->name('instructor.bundles.update');
            Route::get('/instructor/bundles/{id}/courses', [InstructorController::class, 'bundleCourses'])
                ->whereNumber('id')
                ->name('instructor.bundles.courses');
            Route::post('/instructor/bundles/{id}/courses/attach', [InstructorController::class, 'attachBundleCourse'])
                ->whereNumber('id')
                ->name('instructor.bundles.courses.attach');
            Route::post('/instructor/bundles/{id}/courses/{webinarId}/detach', [InstructorController::class, 'detachBundleCourse'])
                ->whereNumber('id')
                ->whereNumber('webinarId')
                ->name('instructor.bundles.courses.detach');
            Route::get('/instructor/bundles/{id}/preview', [InstructorController::class, 'bundlePreview'])
                ->whereNumber('id')
                ->name('instructor.bundles.preview');
            Route::post('/instructor/bundles/{id}/delete', [InstructorController::class, 'deleteBundle'])
                ->whereNumber('id')
                ->name('instructor.bundles.delete');
            Route::post('/instructor/courses/{id}/delete', [InstructorController::class, 'deleteCourse'])
                ->whereNumber('id')
                ->name('instructor.courses.delete');
            Route::get('/instructor/courses/create/{step?}', [InstructorController::class, 'createCourse'])
                ->where('step', '[1-5]')
                ->name('instructor.courses.create');
            Route::post('/instructor/courses/store', [InstructorController::class, 'storeCourse'])
                ->name('instructor.courses.store');
            Route::post('/instructor/curriculum/chapters', [InstructorController::class, 'chapterStore'])
                ->name('instructor.curriculum.chapters.store');
            Route::post('/instructor/curriculum/chapters/{chapterId}/delete', [InstructorController::class, 'chapterDelete'])
                ->name('instructor.curriculum.chapters.delete');
            Route::post('/instructor/curriculum/sessions', [InstructorController::class, 'curriculumSessionStore'])
                ->name('instructor.curriculum.sessions.store');
            Route::post('/instructor/curriculum/sessions/{sessionId}/delete', [InstructorController::class, 'curriculumSessionDelete'])
                ->name('instructor.curriculum.sessions.delete');
            Route::post('/instructor/curriculum/files', [InstructorController::class, 'curriculumFileStore'])
                ->name('instructor.curriculum.files.store');
            Route::post('/instructor/curriculum/files/{fileId}/delete', [InstructorController::class, 'curriculumFileDelete'])
                ->name('instructor.curriculum.files.delete');
            Route::post('/instructor/curriculum/texts', [InstructorController::class, 'curriculumTextStore'])
                ->name('instructor.curriculum.texts.store');
            Route::post('/instructor/curriculum/texts/{textId}/delete', [InstructorController::class, 'curriculumTextDelete'])
                ->name('instructor.curriculum.texts.delete');
            Route::get('/instructor/courses/{slug}/watch', [InstructorController::class, 'courseWatch'])
                ->name('instructor.courses.watch');
            Route::get('/instructor/courses/{slug}/assignment', [InstructorController::class, 'courseAssignment'])
                ->name('instructor.courses.assignment');
            Route::get('/instructor/courses/{slug}/performance', [InstructorController::class, 'coursePerformance'])
                ->name('instructor.courses.performance');
            Route::get('/instructor/courses/{slug}/performance/export', [InstructorController::class, 'exportCoursePerformance'])
                ->name('instructor.courses.performance.export');
            Route::post('/instructor/courses/{slug}/performance/remind/{studentId}', [InstructorController::class, 'remindCourseStudent'])
                ->whereNumber('studentId')
                ->name('instructor.courses.performance.remind');
            Route::get('/instructor/courses/{slug}/assignments', [InstructorController::class, 'courseAssignments'])
                ->name('instructor.courses.assignments');
            Route::get('/instructor/assignments', [InstructorController::class, 'assignments'])->name('instructor.assignments');
            Route::post('/instructor/assignments', [InstructorController::class, 'storeAssignment'])
                ->name('instructor.assignments.store');
            Route::get('/instructor/assignments/{id}/review', [InstructorController::class, 'assignmentReview'])
                ->name('instructor.assignments.review');
            Route::post('/instructor/assignments/{id}/grade', [InstructorController::class, 'gradeAssignment'])
                ->name('instructor.assignments.grade');
            Route::get('/instructor/consultations', [InstructorController::class, 'consultations'])
                ->name('instructor.consultations');
            Route::get('/instructor/calendar', [InstructorController::class, 'calendar'])
                ->name('instructor.calendar');
            Route::get('/instructor/consultations/{id}', [InstructorController::class, 'consultationShow'])
                ->whereNumber('id')
                ->name('instructor.consultations.show');
            Route::get('/instructor/consultations/{id}/join', [InstructorController::class, 'consultationJoin'])
                ->whereNumber('id')
                ->name('instructor.consultations.join');
            Route::post('/instructor/consultations/{id}/session', [InstructorController::class, 'consultationCreateSession'])
                ->whereNumber('id')
                ->name('instructor.consultations.session');
            Route::post('/instructor/consultations/{id}/finish', [InstructorController::class, 'consultationFinish'])
                ->whereNumber('id')
                ->name('instructor.consultations.finish');
            Route::get('/instructor/quizzes', [InstructorController::class, 'quizzes'])
                ->name('instructor.quizzes');
            Route::get('/instructor/quizzes/create', [InstructorController::class, 'quizCreate'])
                ->name('instructor.quizzes.create');
            Route::post('/instructor/quizzes', [InstructorController::class, 'quizStore'])
                ->name('instructor.quizzes.store');
            Route::get('/instructor/quizzes/{id}/view', [InstructorController::class, 'quizView'])
                ->name('instructor.quizzes.view');
            Route::get('/instructor/quizzes/{id}/edit', [InstructorController::class, 'quizEdit'])
                ->name('instructor.quizzes.edit');
            Route::post('/instructor/quizzes/{id}', [InstructorController::class, 'quizUpdate'])
                ->name('instructor.quizzes.update');
            Route::post('/instructor/quizzes/{id}/delete', [InstructorController::class, 'quizDelete'])
                ->name('instructor.quizzes.delete');
            Route::post('/instructor/quizzes/{id}/questions', [InstructorController::class, 'questionStore'])
                ->name('instructor.quizzes.questions.store');
            Route::post('/instructor/quizzes/{id}/questions/{questionId}/delete', [InstructorController::class, 'questionDelete'])
                ->name('instructor.quizzes.questions.delete');
            Route::get('/instructor/quiz-results/{resultId}/grade', [InstructorController::class, 'gradeQuizResult'])
                ->name('instructor.quiz-results.grade');
            Route::post('/instructor/quiz-results/{resultId}/grade', [InstructorController::class, 'storeQuizResultGrade'])
                ->name('instructor.quiz-results.grade.store');
            Route::get('/instructor/comments', [InstructorController::class, 'comments'])
                ->name('instructor.comments');
            Route::post('/instructor/comments/{id}/reply', [InstructorController::class, 'commentReply'])
                ->whereNumber('id')
                ->name('instructor.comments.reply');
            Route::post('/instructor/comments/{id}/report', [InstructorController::class, 'commentReport'])
                ->whereNumber('id')
                ->name('instructor.comments.report');
            Route::get('/instructor/certificates', [InstructorController::class, 'certificates'])
                ->name('instructor.certificates');
            Route::get('/instructor/certificates/students', [InstructorController::class, 'certificatesStudents'])
                ->name('instructor.certificates.students');
            Route::get('/instructor/certificates/students/{id}/download', [InstructorController::class, 'downloadCertificate'])
                ->whereNumber('id')
                ->name('instructor.certificates.download');
            Route::get('/instructor/certificates/{type}/{id}', [InstructorController::class, 'certificatesStudents'])
                ->where('type', 'quiz|courses|bundles')
                ->whereNumber('id')
                ->name('instructor.certificates.details');
            Route::get('/instructor/finance', [InstructorController::class, 'finance'])
                ->name('instructor.finance');
            Route::get('/instructor/payouts', [InstructorController::class, 'payouts'])
                ->name('instructor.payouts');
            Route::post('/instructor/payouts/request', [InstructorController::class, 'requestPayout'])
                ->name('instructor.payouts.request');
            Route::get('/instructor/payouts/export', [InstructorController::class, 'exportPayouts'])
                ->name('instructor.payouts.export');
            Route::get('/instructor/marketing', [InstructorController::class, 'marketing'])
                ->name('instructor.marketing');
            Route::post('/instructor/discounts', [InstructorController::class, 'discountStore'])
                ->name('instructor.discounts.store');
            Route::post('/instructor/special-offers', [InstructorController::class, 'specialOfferStore'])
                ->name('instructor.special-offers.store');
            Route::post('/instructor/promotions/request', [InstructorController::class, 'promotionRequestStore'])
                ->name('instructor.promotions.request');
            Route::get('/instructor/support', [InstructorController::class, 'support'])
                ->name('instructor.support');
            Route::post('/instructor/support', [InstructorController::class, 'storeSupport'])
                ->name('instructor.support.store');
            Route::get('/instructor/support/conversations/{id?}', [InstructorController::class, 'supportConversations'])
                ->name('instructor.support.conversations');
            Route::post('/instructor/support/conversations/{id}', [InstructorController::class, 'storeSupportConversation'])
                ->name('instructor.support.reply');
            Route::post('/instructor/support/conversations/{id}/close', [InstructorController::class, 'closeSupport'])
                ->name('instructor.support.close');
            Route::get('/instructor/notifications', [InstructorController::class, 'notifications'])
                ->name('instructor.notifications');
            Route::post('/instructor/notifications/mark-all-read', [InstructorController::class, 'markAllNotificationsRead'])
                ->name('instructor.notifications.mark-all-read');
            Route::get('/instructor/settings', [InstructorController::class, 'settings'])
                ->name('instructor.settings');
            Route::post('/instructor/settings', [InstructorController::class, 'updateSettings'])
                ->name('instructor.settings.update');
            Route::post('/instructor/settings/extra', [InstructorController::class, 'updateExtra'])->name('instructor.extra.update');
            Route::post('/instructor/settings/financial', [InstructorController::class, 'updateFinancial'])->name('instructor.financial.update');
            Route::post('/instructor/settings/images', [InstructorController::class, 'updateImages'])->name('instructor.images.update');
            Route::post('/instructor/settings/media/{type}/delete', [InstructorController::class, 'deleteMedia'])->name('instructor.media.delete');
            Route::post('/instructor/settings/about', [InstructorController::class, 'updateAbout'])->name('instructor.about.update');
            Route::post('/instructor/settings/metas', [InstructorController::class, 'storeMeta'])->name('instructor.metas.store');
            Route::post('/instructor/settings/metas/{metaId}/update', [InstructorController::class, 'updateMeta'])->name('instructor.metas.update');
            Route::post('/instructor/settings/metas/{metaId}/delete', [InstructorController::class, 'deleteMeta'])->name('instructor.metas.delete');
            Route::post('/instructor/settings/attachments', [InstructorController::class, 'storeAttachment'])->name('instructor.attachments.store');
            Route::post('/instructor/settings/attachments/{attachmentId}/update', [InstructorController::class, 'updateAttachment'])->name('instructor.attachments.update');
            Route::post('/instructor/settings/attachments/{attachmentId}/delete', [InstructorController::class, 'deleteAttachment'])->name('instructor.attachments.delete');
            Route::post('/instructor/settings/sessions/{sessionId}/end', [InstructorController::class, 'endSession'])->name('instructor.sessions.end');

            Route::get('/organization', [OrganizationController::class, 'home'])->name('organization.home');
            Route::get('/organization/users/{type}', [OrganizationController::class, 'users'])
                ->where('type', 'instructors|students')
                ->name('organization.users');
            Route::get('/organization/users/{type}/create', [OrganizationController::class, 'memberCreate'])
                ->where('type', 'instructors|students')
                ->name('organization.members.create');
            Route::post('/organization/users/{type}', [OrganizationController::class, 'memberStore'])
                ->where('type', 'instructors|students')
                ->name('organization.members.store');
            Route::get('/organization/users/{type}/{id}/edit', [OrganizationController::class, 'memberEdit'])
                ->where('type', 'instructors|students')
                ->name('organization.members.edit');
            Route::post('/organization/users/{type}/{id}', [OrganizationController::class, 'memberUpdate'])
                ->where('type', 'instructors|students')
                ->name('organization.members.update');
            Route::get('/organization/courses', [OrganizationController::class, 'courses'])->name('organization.courses');
            Route::get('/organization/settings', [OrganizationController::class, 'settings'])->name('organization.settings');
            Route::post('/organization/settings', [OrganizationController::class, 'updateSettings'])->name('organization.settings.update');
            Route::post('/organization/settings/extra', [OrganizationController::class, 'updateExtra'])->name('organization.extra.update');
            Route::post('/organization/settings/financial', [OrganizationController::class, 'updateFinancial'])->name('organization.financial.update');
            Route::post('/organization/settings/images', [OrganizationController::class, 'updateImages'])->name('organization.images.update');
            Route::post('/organization/settings/media/{type}/delete', [OrganizationController::class, 'deleteMedia'])->name('organization.media.delete');
            Route::post('/organization/settings/about', [OrganizationController::class, 'updateAbout'])->name('organization.about.update');
            Route::post('/organization/settings/metas', [OrganizationController::class, 'storeMeta'])->name('organization.metas.store');
            Route::post('/organization/settings/metas/{metaId}/update', [OrganizationController::class, 'updateMeta'])->name('organization.metas.update');
            Route::post('/organization/settings/metas/{metaId}/delete', [OrganizationController::class, 'deleteMeta'])->name('organization.metas.delete');
            Route::post('/organization/settings/attachments', [OrganizationController::class, 'storeAttachment'])->name('organization.attachments.store');
            Route::post('/organization/settings/attachments/{attachmentId}/update', [OrganizationController::class, 'updateAttachment'])->name('organization.attachments.update');
            Route::post('/organization/settings/attachments/{attachmentId}/delete', [OrganizationController::class, 'deleteAttachment'])->name('organization.attachments.delete');
            Route::post('/organization/settings/sessions/{sessionId}/end', [OrganizationController::class, 'endSession'])->name('organization.sessions.end');
        });

    Route::middleware(['web', 'admin'])
        ->group(function () {
            Route::get('/admin', [AdminController::class, 'home'])->name('admin.home');

            Route::prefix('admin/education')->name('admin.education.')->group(function () {
                Route::get('/', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'home'])->name('home');
                Route::get('/departments/create', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'createDepartment'])->name('departments.create');
                Route::post('/departments', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'storeDepartment'])->name('departments.store');
                Route::get('/departments/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'editDepartment'])->name('departments.edit');
                Route::post('/departments/{id}/update', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'updateDepartment'])->name('departments.update');
                Route::post('/departments/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'deleteDepartment'])->name('departments.delete');
                // Filters & Trends
                Route::get('/filters', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'filtersList'])->name('filters.list');
                Route::get('/filters/create', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'createFilter'])->name('filters.create');
                Route::post('/filters', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'storeFilter'])->name('filters.store');
                Route::get('/filters/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'editFilter'])->name('filters.edit');
                Route::post('/filters/{id}/update', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'updateFilter'])->name('filters.update');
                Route::post('/filters/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'deleteFilter'])->name('filters.delete');
                Route::get('/trends', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'trendCategoriesList'])->name('trends.list');
                Route::get('/trends/create', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'createTrendCategory'])->name('trends.create');
                Route::post('/trends', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'storeTrendCategory'])->name('trends.store');
                Route::get('/trends/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'editTrendCategory'])->name('trends.edit');
                Route::post('/trends/{id}/update', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'updateTrendCategory'])->name('trends.update');
                Route::post('/trends/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'deleteTrendCategory'])->name('trends.delete');
                Route::get('/certificates/templates/create', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'createCertificateTemplate'])->name('certificates.templates.create');
                Route::post('/certificates/templates', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'storeCertificateTemplate'])->name('certificates.templates.store');
                Route::get('/certificates/templates/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'editCertificateTemplate'])->name('certificates.templates.edit');
                Route::post('/certificates/templates/{id}/update', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'updateCertificateTemplate'])->name('certificates.templates.update');
                Route::post('/certificates/templates/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'deleteCertificateTemplate'])->name('certificates.templates.delete');
                // دورات — تنفيذ حرفي مستند لـ Admin\WebinarController (إنشاء/تعديل/حذف/موافقة)
                Route::get('/courses/create', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'createCourse'])->name('courses.create');
                Route::post('/courses', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'storeCourse'])->name('courses.store');
                Route::get('/courses/export', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'exportCourses'])->name('courses.export');
                Route::get('/courses/{id}/edit/{step?}', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'editCourse'])->name('courses.edit');
                Route::post('/courses/{id}/wizard', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'storeCourseWizard'])->name('courses.wizard.store');
                Route::post('/courses/{id}/update', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'updateCourse'])->name('courses.update');
                Route::post('/courses/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'deleteCourse'])->name('courses.delete');
                Route::post('/courses/{id}/approve', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'approveCourse'])->name('courses.approve');
                Route::post('/courses/{id}/reject', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'rejectCourse'])->name('courses.reject');
                Route::get('/courses/{id}/notify', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'notifyCourseForm'])->name('courses.notify');
                Route::post('/courses/{id}/notify', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'sendCourseNotification'])->name('courses.notify.send');
                Route::get('/courses/{id}/curriculum', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'courseCurriculum'])->name('courses.curriculum');
                Route::get('/extra-descriptions/{scope}/{itemId}', [\App\Http\Controllers\PanelV1\Admin\WebinarExtraDescriptionController::class, 'index'])->name('extra-descriptions.index');
                Route::post('/extra-descriptions/{scope}/{itemId}', [\App\Http\Controllers\PanelV1\Admin\WebinarExtraDescriptionController::class, 'store'])->name('extra-descriptions.store');
                Route::post('/extra-descriptions/{id}/update', [\App\Http\Controllers\PanelV1\Admin\WebinarExtraDescriptionController::class, 'update'])->name('extra-descriptions.update');
                Route::post('/extra-descriptions/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\WebinarExtraDescriptionController::class, 'destroy'])->name('extra-descriptions.delete');
                Route::post('/courses/{id}/chapters', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'adminChapterStore'])->name('courses.chapters.store');
                Route::post('/courses/{id}/chapters/{chapterId}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'adminChapterDelete'])->name('courses.chapters.delete');
                Route::post('/courses/{id}/sessions', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'adminSessionStore'])->name('courses.sessions.store');
                Route::post('/courses/{id}/sessions/{sessionId}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'adminSessionDelete'])->name('courses.sessions.delete');
                Route::post('/courses/{id}/files', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'adminFileStore'])->name('courses.files.store');
                Route::post('/courses/{id}/files/{fileId}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'adminFileDelete'])->name('courses.files.delete');
                // Wizard curriculum AJAX (admin middleware — instructor /v1/instructor/* rejects admins)
                Route::post('/curriculum/chapters', [InstructorController::class, 'chapterStore'])
                    ->name('curriculum.chapters.store');
                Route::post('/curriculum/chapters/{chapterId}/delete', [InstructorController::class, 'chapterDelete'])
                    ->name('curriculum.chapters.delete');
                Route::post('/curriculum/sessions', [InstructorController::class, 'curriculumSessionStore'])
                    ->name('curriculum.sessions.store');
                Route::post('/curriculum/sessions/{sessionId}/delete', [InstructorController::class, 'curriculumSessionDelete'])
                    ->name('curriculum.sessions.delete');
                Route::post('/curriculum/files', [InstructorController::class, 'curriculumFileStore'])
                    ->name('curriculum.files.store');
                Route::post('/curriculum/files/{fileId}/delete', [InstructorController::class, 'curriculumFileDelete'])
                    ->name('curriculum.files.delete');
                Route::post('/curriculum/texts', [InstructorController::class, 'curriculumTextStore'])
                    ->name('curriculum.texts.store');
                Route::post('/curriculum/texts/{textId}/delete', [InstructorController::class, 'curriculumTextDelete'])
                    ->name('curriculum.texts.delete');
                // حِزم واختبارات وتكليفات — قابل للاستخدام
                Route::get('/bundles/create', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'createBundle'])->name('bundles.create');
                Route::post('/bundles', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'storeBundle'])->name('bundles.store');
                Route::get('/bundles/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'editBundle'])->name('bundles.edit');
                Route::post('/bundles/{id}/update', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'updateBundle'])->name('bundles.update');
                Route::post('/bundles/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'deleteBundle'])->name('bundles.delete');
                Route::get('/quizzes/create', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'createQuiz'])->name('quizzes.create');
                Route::post('/quizzes', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'storeQuiz'])->name('quizzes.store');
                Route::post('/quizzes/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'deleteQuiz'])->name('quizzes.delete');
                Route::get('/quizzes/{id}/questions', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'quizQuestions'])->name('quizzes.questions');
                Route::post('/quizzes/{id}/questions', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'storeQuizQuestion'])->name('quizzes.questions.store');
                Route::get('/quizzes/{id}/questions/{questionId}/edit', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'editQuizQuestion'])->name('quizzes.questions.edit');
                Route::post('/quizzes/{id}/questions/{questionId}/update', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'updateQuizQuestion'])->name('quizzes.questions.update');
                Route::post('/quizzes/questions/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'deleteQuizQuestion'])->name('quizzes.questions.delete');
                Route::get('/assignments/create', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'createAssignment'])->name('assignments.create');
                Route::post('/assignments', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'storeAssignment'])->name('assignments.store');
                Route::get('/assignments/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'editAssignment'])->name('assignments.edit');
                Route::post('/assignments/{id}/update', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'updateAssignment'])->name('assignments.update');
                Route::post('/assignments/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'deleteAssignment'])->name('assignments.delete');
                Route::post('/reviews/{id}/approve', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'approveReview'])->name('reviews.approve');
                Route::post('/reviews/{id}/reject', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'rejectReview'])->name('reviews.reject');
                Route::post('/reviews/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'deleteReview'])->name('reviews.delete');
                Route::post('/certificates/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'deleteCertificate'])->name('certificates.delete');
                Route::post('/live/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'deleteLive'])->name('live.delete');
                Route::get('/events/create', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'createEvent'])->name('events.create');
                Route::post('/events', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'storeEvent'])->name('events.store');
                Route::get('/events/export', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'exportEvents'])->name('events.export');
                Route::get('/events/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'editEvent'])->name('events.edit');
                Route::post('/events/{id}/update', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'updateEvent'])->name('events.update');
                Route::post('/events/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'deleteEvent'])->name('events.delete');
                Route::post('/events/{id}/status/{status}', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'changeEventStatus'])->where('status','publish|reject|unpublish|cancel')->name('events.status');
                Route::post('/events/{id}/tickets', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'storeEventTicket'])->name('events.tickets.store');
                Route::post('/events/tickets/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'deleteEventTicket'])->name('events.tickets.delete');
                Route::post('/events/{id}/speakers', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'storeEventSpeaker'])->name('events.speakers.store');
                Route::post('/events/speakers/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'deleteEventSpeaker'])->name('events.speakers.delete');
                Route::post('/attendances/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'deleteAttendance'])->name('attendances.delete');
                // Quiz Results (Admin)
                Route::get('/quizzes/{quizId}/results', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'quizResults'])->name('quiz-results');
                Route::get('/quizzes/{quizId}/results/{resultId}/review', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'quizResultReview'])->name('quiz-results.review');
                Route::post('/quizzes/{quizId}/results/{resultId}/update', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'quizResultUpdate'])->name('quiz-results.update');
                Route::post('/quizzes/{quizId}/results/{resultId}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'quizResultDelete'])->name('quiz-results.delete');
                Route::get('/quizzes/{quizId}/results/export', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'quizResultsExport'])->name('quiz-results.export');
                // Enrollment
                Route::get('/enrollment/history', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'enrollmentHistory'])->name('enrollment.history');
                Route::get('/enrollment/add', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'enrollmentAddStudentForm'])->name('enrollment.add');
                Route::post('/enrollment/store', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'enrollmentStore'])->name('enrollment.store');
                Route::post('/enrollment/{saleId}/block', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'enrollmentBlock'])->name('enrollment.block');
                Route::post('/enrollment/{saleId}/enable', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'enrollmentEnable'])->name('enrollment.enable');
                // Upcoming Courses
                Route::get('/upcoming', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'upcomingList'])->name('upcoming.list');
                Route::get('/upcoming/create', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'upcomingCreate'])->name('upcoming.create');
                Route::post('/upcoming', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'upcomingStore'])->name('upcoming.store');
                Route::get('/upcoming/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'upcomingEdit'])->name('upcoming.edit');
                Route::post('/upcoming/{id}/update', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'upcomingUpdate'])->name('upcoming.update');
                Route::post('/upcoming/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'upcomingDelete'])->name('upcoming.delete');
                Route::post('/upcoming/{id}/approve', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'upcomingApprove'])->name('upcoming.approve');
                Route::post('/upcoming/{id}/reject', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'upcomingReject'])->name('upcoming.reject');
                // Waitlist
                Route::get('/waitlists', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'waitlistIndex'])->name('waitlists.index');
                Route::get('/waitlists/{webinarId}', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'waitlistView'])->name('waitlists.view');
                Route::post('/waitlists/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'waitlistDelete'])->name('waitlists.delete');
                Route::post('/waitlists/{webinarId}/delete-all', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'waitlistDeleteAll'])->name('waitlists.delete-all');
                // Statistic & Noticeboard
                Route::get('/statistics', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'webinarStatistic'])->name('statistics');
                Route::get('/noticeboard', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'noticeboardList'])->name('noticeboard.list');
                Route::get('/noticeboard/create', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'noticeboardCreate'])->name('noticeboard.create');
                Route::post('/noticeboard', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'noticeboardStore'])->name('noticeboard.store');
                Route::post('/noticeboard/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'noticeboardDelete'])->name('noticeboard.delete');
                Route::get('/related-courses/{itemId}', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'relatedCourses'])->name('related-courses');
                Route::post('/related-courses', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'storeRelatedCourse'])->name('related-courses.store');
                Route::post('/related-courses/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'deleteRelatedCourse'])->name('related-courses.delete');
                Route::get('/{section}', [\App\Http\Controllers\PanelV1\Admin\EducationController::class, 'section'])->name('section');
            });

            Route::prefix('admin/sales')->name('admin.sales.')->group(function () {
                Route::get('/', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'home'])->name('home');
                Route::get('/export', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'exportSales'])->name('export');
                Route::post('/sales/{id}/refund', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'refund'])->name('sales.refund');
                Route::get('/sales/{id}/invoice', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'invoice'])->name('sales.invoice');
                Route::post('/payouts/{id}/approve', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'approvePayout'])->name('payouts.approve');
                Route::post('/payouts/{id}/payout', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'payout'])->name('payouts.payout');
                Route::post('/payouts/{id}/reject', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'rejectPayout'])->name('payouts.reject');
                Route::get('/payouts/export', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'exportPayouts'])->name('payouts.export');
                Route::post('/offline/{id}/approve', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'approveOffline'])->name('offline.approve');
                Route::post('/offline/{id}/reject', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'rejectOffline'])->name('offline.reject');
                Route::post('/offline/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'deleteOffline'])->name('offline.delete');
                Route::get('/offline/{id}/cart', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'cartItems'])->name('offline.cart');
                Route::get('/offline/export', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'exportOffline'])->name('offline.export');
                Route::get('/event-tickets/export', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'exportEventTickets'])->name('event-tickets.export');
                Route::get('/subscribes/create', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'createSubscribe'])->name('subscribes.create');
                Route::post('/subscribes', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'storeSubscribe'])->name('subscribes.store');
                Route::get('/subscribes/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'editSubscribe'])->name('subscribes.edit');
                Route::post('/subscribes/{id}/update', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'updateSubscribe'])->name('subscribes.update');
                Route::get('/packages/create', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'createPackage'])->name('packages.create');
                Route::post('/packages', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'storePackage'])->name('packages.store');
                Route::get('/packages/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'editPackage'])->name('packages.edit');
                Route::post('/packages/{id}/update', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'updatePackage'])->name('packages.update');
                Route::get('/installments', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'installmentsList'])->name('installments.list');
                Route::get('/installments/create', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'createInstallment'])->name('installments.create');
                Route::post('/installments', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'storeInstallment'])->name('installments.store');
                Route::get('/installments/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'editInstallment'])->name('installments.edit');
                Route::post('/installments/{id}/update', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'updateInstallment'])->name('installments.update');
                Route::post('/installments/plans/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'deleteInstallmentPlan'])->name('installments.plans.delete');
                Route::post('/subscriptions/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'deleteSubscription'])->name('subscriptions.delete');
                Route::post('/installments/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'deleteInstallment'])->name('installments.delete');
                Route::get('/meeting-packages/create', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'createMeetingPackage'])->name('meeting-packages.create');
                Route::post('/meeting-packages', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'storeMeetingPackage'])->name('meeting-packages.store');
                Route::get('/meeting-packages/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'editMeetingPackage'])->name('meeting-packages.edit');
                Route::post('/meeting-packages/{id}/update', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'updateMeetingPackage'])->name('meeting-packages.update');
                Route::post('/meeting-packages/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'deleteMeetingPackage'])->name('meeting-packages.delete');
                Route::post('/packages/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'deletePackage'])->name('packages.delete');
                // Payment Channels & Documents (P0)
                Route::get('/payment-channels/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'editPaymentChannel'])->name('payment-channels.edit');
                Route::post('/payment-channels/{id}/update', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'updatePaymentChannel'])->name('payment-channels.update');
                Route::post('/payment-channels/{id}/toggle', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'togglePaymentChannel'])->name('payment-channels.toggle');
                Route::get('/documents/create', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'createDocument'])->name('documents.create');
                Route::post('/documents', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'storeDocument'])->name('documents.store');
                Route::get('/documents/{id}/print', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'printDocument'])->name('documents.print');
                Route::get('/{section}', [\App\Http\Controllers\PanelV1\Admin\SalesController::class, 'section'])->name('section');
            });

            Route::prefix('admin/marketing')->name('admin.marketing.')->group(function () {
                Route::get('/', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'home'])->name('home');
                Route::get('/discounts/create', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'createDiscount'])->name('discounts.create');
                Route::post('/discounts', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'storeDiscount'])->name('discounts.store');
                Route::get('/discounts/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'editDiscount'])->name('discounts.edit');
                Route::post('/discounts/{id}/update', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'updateDiscount'])->name('discounts.update');
                Route::post('/discounts/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'deleteDiscount'])->name('discounts.delete');
                Route::get('/pages/create', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'createPage'])->name('pages.create');
                Route::post('/pages', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'storePage'])->name('pages.store');
                Route::get('/pages/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'editPage'])->name('pages.edit');
                Route::post('/pages/{id}/update', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'updatePage'])->name('pages.update');
                Route::get('/blog/create', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'createBlog'])->name('blog.create');
                Route::post('/blog', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'storeBlog'])->name('blog.store');
                Route::get('/blog/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'editBlog'])->name('blog.edit');
                Route::post('/blog/{id}/update', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'updateBlog'])->name('blog.update');
                Route::get('/products/create', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'createProduct'])->name('products.create');
                Route::post('/products', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'storeProduct'])->name('products.store');
                Route::get('/products/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'editProduct'])->name('products.edit');
                Route::post('/products/{id}/update', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'updateProduct'])->name('products.update');
                Route::get('/cashback/create', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'createCashback'])->name('cashback.create');
                Route::post('/cashback', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'storeCashback'])->name('cashback.store');
                Route::get('/cashback/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'editCashback'])->name('cashback.edit');
                Route::post('/cashback/{id}/update', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'updateCashback'])->name('cashback.update');
                Route::post('/cashback/{id}/toggle', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'toggleCashback'])->name('cashback.toggle');
                Route::get('/special-offers/create', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'createSpecialOffer'])->name('special-offers.create');
                Route::post('/special-offers', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'storeSpecialOffer'])->name('special-offers.store');
                Route::get('/special-offers/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'editSpecialOffer'])->name('special-offers.edit');
                Route::post('/special-offers/{id}/update', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'updateSpecialOffer'])->name('special-offers.update');
                Route::post('/special-offers/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'deleteSpecialOffer'])->name('special-offers.delete');
                Route::get('/promotions/create', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'createPromotion'])->name('promotions.create');
                Route::post('/promotions', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'storePromotion'])->name('promotions.store');
                Route::get('/promotions/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'editPromotion'])->name('promotions.edit');
                Route::post('/promotions/{id}/update', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'updatePromotion'])->name('promotions.update');
                Route::post('/promotions/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'deletePromotion'])->name('promotions.delete');
                Route::get('/promotions/sales', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'promotionSales'])->name('promotions.sales');
                Route::get('/referrals/history', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'referralsHistory'])->name('referrals.history');
                Route::get('/referrals/users', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'referralsUsers'])->name('referrals.users');
                Route::get('/referrals/export', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'exportReferrals'])->name('referrals.export');
                Route::get('/newsletters/send', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'sendNewsletterForm'])->name('newsletters.send-form');
                Route::post('/newsletters/send', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'sendNewsletter'])->name('newsletters.send');
                Route::post('/newsletters/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'deleteNewsletter'])->name('newsletters.delete');
                Route::get('/newsletters/export', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'exportNewsletters'])->name('newsletters.export');
                Route::get('/abandoned-cart/rules', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'abandonedCartRules'])->name('abandoned-cart.rules');
                Route::get('/abandoned-cart/rules/create', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'createAbandonedCartRule'])->name('abandoned-cart.create');
                Route::post('/abandoned-cart/rules', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'storeAbandonedCartRule'])->name('abandoned-cart.store');
                Route::get('/abandoned-cart/rules/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'editAbandonedCartRule'])->name('abandoned-cart.edit');
                Route::post('/abandoned-cart/rules/{id}/update', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'updateAbandonedCartRule'])->name('abandoned-cart.update');
                Route::post('/abandoned-cart/rules/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'deleteAbandonedCartRule'])->name('abandoned-cart.delete');
                Route::get('/cart-discount', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'cartDiscountIndex'])->name('cart-discount.index');
                Route::post('/cart-discount', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'storeCartDiscount'])->name('cart-discount.store');
                Route::get('/registration-bonus/history', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'registrationBonusHistory'])->name('registration-bonus.history');
                Route::get('/registration-bonus/export', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'exportRegistrationBonus'])->name('registration-bonus.export');
                Route::post('/gifts/{id}/cancel', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'cancelGift'])->name('gifts.cancel');
                Route::post('/gifts/{id}/reminder', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'sendGiftReminder'])->name('gifts.reminder');
                Route::post('/products/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'deleteProduct'])->name('products.delete');
                Route::post('/blog/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'deleteBlog'])->name('blog.delete');
                Route::post('/pages/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'deletePage'])->name('pages.delete');
                Route::get('/banners/create', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'createBanner'])->name('banners.create');
                Route::post('/banners', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'storeBanner'])->name('banners.store');
                Route::get('/banners/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'editBanner'])->name('banners.edit');
                Route::post('/banners/{id}/update', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'updateBanner'])->name('banners.update');
                Route::post('/banners/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'deleteBanner'])->name('banners.delete');
                Route::get('/floating-bars/create', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'createFloatingBar'])->name('floating-bars.create');
                Route::post('/floating-bars', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'storeFloatingBar'])->name('floating-bars.store');
                Route::get('/floating-bars/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'editFloatingBar'])->name('floating-bars.edit');
                Route::post('/floating-bars/{id}/update', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'updateFloatingBar'])->name('floating-bars.update');
                Route::post('/floating-bars/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'deleteFloatingBar'])->name('floating-bars.delete');
                Route::get('/purchase-notifications/create', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'createPurchaseNotification'])->name('purchase-notifications.create');
                Route::post('/purchase-notifications', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'storePurchaseNotification'])->name('purchase-notifications.store');
                Route::get('/purchase-notifications/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'editPurchaseNotification'])->name('purchase-notifications.edit');
                Route::post('/purchase-notifications/{id}/update', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'updatePurchaseNotification'])->name('purchase-notifications.update');
                Route::post('/purchase-notifications/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'deletePurchaseNotification'])->name('purchase-notifications.delete');
                Route::get('/product-badges/create', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'createProductBadge'])->name('product-badges.create');
                Route::post('/product-badges', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'storeProductBadge'])->name('product-badges.store');
                Route::get('/product-badges/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'editProductBadge'])->name('product-badges.edit');
                Route::post('/product-badges/{id}/update', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'updateProductBadge'])->name('product-badges.update');
                Route::post('/product-badges/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'deleteProductBadge'])->name('product-badges.delete');
                Route::get('/forms/create', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'createForm'])->name('forms.create');
                Route::post('/forms', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'storeForm'])->name('forms.store');
                Route::get('/forms/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'editForm'])->name('forms.edit');
                Route::post('/forms/{id}/update', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'updateForm'])->name('forms.update');
                Route::post('/forms/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'deleteForm'])->name('forms.delete');
                Route::post('/affiliates/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'deleteAffiliate'])->name('affiliates.delete');
                Route::post('/cashbacks/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'deleteCashback'])->name('cashbacks.delete');
                Route::post('/points/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'deletePoint'])->name('points.delete');
                Route::post('/gifts/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'deleteGift'])->name('gifts.delete');
                // Abandoned & Cashback & Forms & Blog & Newsletter (P2)
                Route::get('/abandoned-users', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'abandonedUsersCart'])->name('abandoned-users');
                Route::post('/abandoned/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'deleteAbandonedCart'])->name('abandoned.delete');
                Route::get('/cashback-transactions', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'cashbackTransactions'])->name('cashback.transactions');
                Route::get('/cashback-transactions/export', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'exportCashbackTransactions'])->name('cashback.transactions.export');
                Route::get('/cashback-history', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'cashbackHistory'])->name('cashback.history');
                Route::get('/cashback-history/export', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'exportCashbackHistory'])->name('cashback.history.export');
                Route::post('/cashback-transactions/{id}/refund', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'refundCashback'])->name('cashback.refund');
                Route::get('/forms/{formId}/fields', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'formFields'])->name('form-fields');
                Route::get('/forms/{formId}/fields/create', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'createFormField'])->name('form-fields.create');
                Route::post('/forms/{formId}/fields', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'storeFormField'])->name('form-fields.store');
                Route::get('/forms/{formId}/fields/{fieldId}/edit', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'editFormField'])->name('form-fields.edit');
                Route::post('/forms/{formId}/fields/{fieldId}/update', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'updateFormField'])->name('form-fields.update');
                Route::post('/forms/{formId}/fields/{fieldId}/delete', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'deleteFormField'])->name('form-fields.delete');
                Route::get('/forms/{formId}/submissions', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'formSubmissions'])->name('form-submissions');
                Route::post('/forms/{formId}/submissions/{submissionId}/delete', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'deleteFormSubmission'])->name('form-submissions.delete');
                Route::post('/forms/{formId}/submissions/{submissionId}/update', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'updateFormSubmission'])->name('form-submissions.update');
                Route::get('/blog-categories', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'blogCategories'])->name('blog-categories');
                Route::get('/blog-categories/create', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'createBlogCategory'])->name('blog-categories.create');
                Route::post('/blog-categories', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'storeBlogCategory'])->name('blog-categories.store');
                Route::get('/blog-categories/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'editBlogCategory'])->name('blog-categories.edit');
                Route::post('/blog-categories/{id}/update', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'updateBlogCategory'])->name('blog-categories.update');
                Route::post('/blog-categories/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'deleteBlogCategory'])->name('blog-categories.delete');
                Route::get('/forms/{formId}/submissions/{submissionId}', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'showFormSubmission'])->name('form-submissions.show');
                Route::get('/newsletter-history', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'newsletterHistory'])->name('newsletter-history');
                Route::get('/additional-pages', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'additionalPages'])->name('additional-pages');
                Route::get('/blog-featured', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'blogFeatured'])->name('blog-featured');
                Route::get('/testimonials', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'testimonials'])->name('testimonials');
                Route::get('/testimonials/create', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'createTestimonial'])->name('testimonials.create');
                Route::post('/testimonials', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'storeTestimonial'])->name('testimonials.store');
                Route::get('/testimonials/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'editTestimonial'])->name('testimonials.edit');
                Route::post('/testimonials/{id}/update', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'updateTestimonial'])->name('testimonials.update');
                Route::post('/testimonials/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'deleteTestimonial'])->name('testimonials.delete');
                Route::get('/{section}', [\App\Http\Controllers\PanelV1\Admin\MarketingController::class, 'section'])->name('section');
            });

            Route::prefix('admin/system')->name('admin.system.')->group(function () {
                Route::get('/', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'home'])->name('home');
                Route::get('/users/create', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'createUser'])->name('users.create');
                Route::post('/users', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'storeUser'])->name('users.store');
                Route::get('/users/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'editUser'])->name('users.edit');
                Route::post('/users/{id}/update', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'updateUser'])->name('users.update');
                Route::post('/users/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'deleteUser'])->name('users.delete');
                Route::get('/users/{id}/impersonate', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'impersonateUser'])->name('users.impersonate');
                Route::get('/users/export', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'exportUsers'])->name('users.export');
                Route::get('/groups/create', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'createGroup'])->name('groups.create');
                Route::post('/groups', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'storeGroup'])->name('groups.store');
                Route::get('/groups/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'editGroup'])->name('groups.edit');
                Route::post('/groups/{id}/update', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'updateGroup'])->name('groups.update');
                Route::post('/groups/{id}/registration-package', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'groupRegistrationPackage'])->name('groups.registration-package');
                Route::get('/roles/create', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'createRole'])->name('roles.create');
                Route::post('/roles', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'storeRole'])->name('roles.store');
                Route::get('/roles/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'editRole'])->name('roles.edit');
                Route::post('/roles/{id}/update', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'updateRole'])->name('roles.update');
                Route::post('/roles/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'deleteRole'])->name('roles.delete');
                Route::get('/badges/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'editBadge'])->name('badges.edit');
                Route::post('/badges/{id}/update', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'updateBadge'])->name('badges.update');
                Route::get('/forums/create', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'createForum'])->name('forums.create');
                Route::post('/forums', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'storeForum'])->name('forums.store');
                Route::get('/forums/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'editForum'])->name('forums.edit');
                Route::post('/forums/{id}/update', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'updateForum'])->name('forums.update');
                Route::post('/settings/{id}/save', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'saveSetting'])->name('settings.save')->whereNumber('id');
                Route::get('/settings/{group}', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'settingsGroup'])->name('settings.group')
                    ->where('group', 'general|financial|personalization|notifications|seo|mobile-app|update-app');
                Route::post('/groups/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'deleteGroup'])->name('groups.delete');
                Route::get('/badges/create', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'createBadge'])->name('badges.create');
                Route::post('/badges', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'storeBadge'])->name('badges.store');
                Route::post('/badges/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'deleteBadge'])->name('badges.delete');
                Route::post('/instructor-requests/{id}/approve', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'approveInstructorRequest'])->name('instructor-requests.approve');
                Route::post('/instructor-requests/{id}/reject', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'rejectInstructorRequest'])->name('instructor-requests.reject');
                Route::post('/supports/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'deleteSupport'])->name('supports.delete');
                Route::post('/consultations/{id}/finish', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'finishConsultation'])->name('consultations.finish');
                Route::post('/consultations/{id}/cancel', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'cancelConsultation'])->name('consultations.cancel');
                Route::post('/consultations/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'deleteConsultation'])->name('consultations.delete');
                Route::post('/import/validate', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'validateImport'])->name('import.validate');
                Route::post('/import/confirm', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'confirmImport'])->name('import.confirm');
                Route::get('/import/sample', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'downloadImportSample'])->name('import.sample');
                Route::post('/delete-requests/{id}/confirm', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'confirmDeleteRequest'])->name('delete-requests.confirm');
                Route::post('/delete-requests/{id}/reject', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'rejectDeleteRequest'])->name('delete-requests.reject');
                Route::post('/notifications/mark-all-read', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'markAllNotificationsRead'])->name('notifications.mark-all-read');
                Route::post('/contacts/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'deleteContact'])->name('contacts.delete');
                Route::post('/reports/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'deleteReport'])->name('reports.delete');
                Route::post('/forums/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'deleteForum'])->name('forums.delete');
                Route::post('/notifications/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'deleteNotification'])->name('notifications.delete');
                // P3 & P4 — System advanced
                Route::get('/support-departments', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'supportDepartments'])->name('support-departments');
                Route::get('/support-departments/create', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'createSupportDepartment'])->name('support-departments.create');
                Route::post('/support-departments', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'storeSupportDepartment'])->name('support-departments.store');
                Route::get('/support-departments/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'editSupportDepartment'])->name('support-departments.edit');
                Route::post('/support-departments/{id}/update', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'updateSupportDepartment'])->name('support-departments.update');
                Route::post('/support-departments/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'deleteSupportDepartment'])->name('support-departments.delete');
                Route::get('/notification-templates', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'notificationTemplates'])->name('notification-templates');
                Route::get('/notification-templates/create', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'createNotificationTemplate'])->name('notification-templates.create');
                Route::post('/notification-templates', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'storeNotificationTemplate'])->name('notification-templates.store');
                Route::get('/notification-templates/{id}/edit', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'editNotificationTemplate'])->name('notification-templates.edit');
                Route::post('/notification-templates/{id}/update', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'updateNotificationTemplate'])->name('notification-templates.update');
                Route::post('/notification-templates/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'deleteNotificationTemplate'])->name('notification-templates.delete');
                Route::get('/regions', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'regionList'])->name('regions');
                Route::get('/regions/create', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'createRegion'])->name('regions.create');
                Route::post('/regions', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'storeRegion'])->name('regions.store');
                Route::post('/regions/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'deleteRegion'])->name('regions.delete');
                Route::get('/login-history', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'loginHistory'])->name('login-history');
                Route::get('/login-history/export', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'exportLoginHistory'])->name('login-history.export');
                Route::post('/login-history/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'deleteLoginHistory'])->name('login-history.delete');
                Route::get('/not-access', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'usersNotAccess'])->name('not-access');
                Route::post('/not-access/{id}/enable', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'enableNotAccess'])->name('not-access.enable');
                Route::get('/ai-contents', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'aiContents'])->name('ai-contents');
                Route::post('/ai-contents/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'deleteAiContent'])->name('ai-contents.delete');
                Route::get('/agora-history', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'agoraHistory'])->name('agora-history');
                Route::get('/agora-history/export', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'exportAgoraHistory'])->name('agora-history.export');
                Route::get('/forum-topics', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'forumTopics'])->name('forum-topics');
                Route::post('/forum-topics/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'deleteForumTopic'])->name('forum-topics.delete');
                Route::post('/forum-reports/{id}/delete', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'deleteForumReport'])->name('forum-reports.delete');
                Route::get('/forum-settings', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'forumSettings'])->name('forum-settings');
                Route::get('/themes', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'themes'])->name('themes');
                Route::get('/translator', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'translator'])->name('translator');
                Route::get('/update', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'updateSystem'])->name('update');
                Route::get('/licenses', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'licenses'])->name('licenses');
                Route::get('/{section}', [\App\Http\Controllers\PanelV1\Admin\SystemController::class, 'section'])->name('section');
            });
        });
});
