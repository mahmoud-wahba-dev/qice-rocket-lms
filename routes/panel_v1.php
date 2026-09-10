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
            Route::get('/student/notifications', [StudentController::class, 'notifications'])->name('student.notifications');
            Route::post('/student/notifications/mark-all-read', [StudentController::class, 'markAllNotificationsRead'])
                ->name('student.notifications.mark-all-read');
            Route::get('/student/purchases', [StudentController::class, 'purchases'])->name('student.purchases');
            Route::get('/student/support', [StudentController::class, 'support'])->name('student.support');
            Route::post('/student/support', [StudentController::class, 'storeSupport'])->name('student.support.store');
            Route::get('/student/settings', [StudentController::class, 'settings'])->name('student.settings');
            Route::post('/student/settings', [StudentController::class, 'updateSettings'])->name('student.settings.update');
            Route::get('/student/favorites', [StudentController::class, 'favorites'])->name('student.favorites');
            Route::post('/student/favorites/toggle', [StudentController::class, 'toggleFavorite'])->name('student.favorites.toggle');
            Route::get('/student/notes', [StudentController::class, 'notes'])->name('student.notes');
            Route::post('/student/notes', [StudentController::class, 'storeNote'])->name('student.notes.store');
            Route::post('/student/notes/{id}/delete', [StudentController::class, 'deleteNote'])->name('student.notes.delete');

            Route::prefix('student/courses/{slug}')->name('student.course.')->group(function () {
                Route::get('/watch', [CoursePlayerController::class, 'watch'])->name('watch');
                Route::get('/forum', [CoursePlayerController::class, 'forum'])->name('forum');
                Route::post('/forum', [CoursePlayerController::class, 'storeForumTopic'])->name('forum.store');
                Route::get('/assignment', [CoursePlayerController::class, 'assignment'])->name('assignment');
                Route::post('/assignment', [CoursePlayerController::class, 'submitAssignment'])->name('assignment.submit');
                Route::get('/quiz', [CoursePlayerController::class, 'quiz'])->name('quiz');
                Route::get('/quiz/take', [CoursePlayerController::class, 'quizTake'])->name('quiz.take');
                Route::post('/quiz/answer', [CoursePlayerController::class, 'quizAnswer'])->name('quiz.answer');
            });

            Route::get('/instructor', [InstructorController::class, 'home'])->name('instructor.home');
            Route::get('/instructor/courses', [InstructorController::class, 'courses'])->name('instructor.courses');
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
            Route::get('/instructor/courses/{slug}/assignments', [InstructorController::class, 'courseAssignments'])
                ->name('instructor.courses.assignments');
            Route::get('/instructor/assignments', [InstructorController::class, 'assignments'])->name('instructor.assignments');
            Route::get('/instructor/assignments/{id}/review', [InstructorController::class, 'assignmentReview'])
                ->name('instructor.assignments.review');
            Route::post('/instructor/assignments/{id}/grade', [InstructorController::class, 'gradeAssignment'])
                ->name('instructor.assignments.grade');
            Route::get('/instructor/consultations', [InstructorController::class, 'consultations'])
                ->name('instructor.consultations');
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
            Route::get('/instructor/certificates', [InstructorController::class, 'certificates'])
                ->name('instructor.certificates');
            Route::get('/instructor/finance', [InstructorController::class, 'finance'])
                ->name('instructor.finance');
            Route::get('/instructor/payouts', [InstructorController::class, 'payouts'])
                ->name('instructor.payouts');
            Route::post('/instructor/payouts/request', [InstructorController::class, 'requestPayout'])
                ->name('instructor.payouts.request');
            Route::get('/instructor/marketing', [InstructorController::class, 'marketing'])
                ->name('instructor.marketing');
            Route::post('/instructor/discounts', [InstructorController::class, 'discountStore'])
                ->name('instructor.discounts.store');
            Route::get('/instructor/support', [InstructorController::class, 'support'])
                ->name('instructor.support');
            Route::get('/instructor/settings', [InstructorController::class, 'settings'])
                ->name('instructor.settings');
            Route::post('/instructor/settings', [InstructorController::class, 'updateSettings'])
                ->name('instructor.settings.update');

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
        });

    Route::middleware(['web', 'admin'])
        ->group(function () {
            Route::get('/admin', [AdminController::class, 'home'])->name('admin.home');
        });
});
