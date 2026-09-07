<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PanelV1\StudentController;
use App\Http\Controllers\PanelV1\CoursePlayerController;
use App\Http\Controllers\PanelV1\InstructorController;
use App\Http\Controllers\PanelV1\AdminController;

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
            Route::get('/student/settings', [StudentController::class, 'settings'])->name('student.settings');

            Route::prefix('student/courses/{slug}')->name('student.course.')->group(function () {
                Route::get('/watch', [CoursePlayerController::class, 'watch'])->name('watch');
                Route::get('/forum', [CoursePlayerController::class, 'forum'])->name('forum');
                Route::get('/assignment', [CoursePlayerController::class, 'assignment'])->name('assignment');
                Route::get('/quiz', [CoursePlayerController::class, 'quiz'])->name('quiz');
                Route::get('/quiz/take', [CoursePlayerController::class, 'quizTake'])->name('quiz.take');
            });

            Route::get('/instructor', [InstructorController::class, 'home'])->name('instructor.home');
            Route::get('/instructor/courses', [InstructorController::class, 'courses'])->name('instructor.courses');
            Route::get('/instructor/courses/{slug}/performance', [InstructorController::class, 'coursePerformance'])
                ->name('instructor.courses.performance');
            Route::get('/instructor/courses/{slug}/assignments', [InstructorController::class, 'courseAssignments'])
                ->name('instructor.courses.assignments');
            Route::get('/instructor/assignments', [InstructorController::class, 'assignments'])->name('instructor.assignments');
            Route::get('/instructor/assignments/{id}/review', [InstructorController::class, 'assignmentReview'])
                ->name('instructor.assignments.review');
            Route::get('/instructor/consultations', [InstructorController::class, 'consultations'])
                ->name('instructor.consultations');
            Route::get('/instructor/quizzes', [InstructorController::class, 'quizzes'])
                ->name('instructor.quizzes');
            Route::get('/instructor/quizzes/{id}/view', [InstructorController::class, 'quizView'])
                ->name('instructor.quizzes.view');
            Route::get('/instructor/certificates', [InstructorController::class, 'certificates'])
                ->name('instructor.certificates');
            Route::get('/instructor/finance', [InstructorController::class, 'finance'])
                ->name('instructor.finance');
            Route::get('/instructor/payouts', [InstructorController::class, 'payouts'])
                ->name('instructor.payouts');
            Route::get('/instructor/marketing', [InstructorController::class, 'marketing'])
                ->name('instructor.marketing');
            Route::get('/instructor/support', [InstructorController::class, 'support'])
                ->name('instructor.support');
        });

    Route::middleware(['web', 'admin'])
        ->group(function () {
            Route::get('/admin', [AdminController::class, 'home'])->name('admin.home');
        });
});
