<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PanelV1\StudentController;
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

            Route::get('/instructor', [InstructorController::class, 'home'])->name('instructor.home');
        });

    Route::middleware(['web', 'admin'])
        ->group(function () {
            Route::get('/admin', [AdminController::class, 'home'])->name('admin.home');
        });
});
