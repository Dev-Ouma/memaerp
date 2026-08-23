<?php

declare(strict_types=1);

use App\Modules\Enrollment\Http\Controllers\EnrollmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/enrollment')->middleware('auth:sanctum')->group(function (): void {
    Route::post('/register-term', [EnrollmentController::class, 'registerTerm'])->name('api.enrollment.register-term');
    Route::get('/available-courses', [EnrollmentController::class, 'availableCourses'])->name('api.enrollment.available-courses');
    Route::post('/courses/enroll', [EnrollmentController::class, 'enrollCourses'])->name('api.enrollment.courses.enroll');
    Route::delete('/courses/{enrollment}/drop', [EnrollmentController::class, 'dropCourse'])
        ->whereUuid('enrollment')
        ->name('api.enrollment.courses.drop');
    Route::get('/my-courses', [EnrollmentController::class, 'myCourses'])->name('api.enrollment.my-courses');
    Route::get('/registrations/{registration}/slip', [EnrollmentController::class, 'registrationSlip'])
        ->whereUuid('registration')
        ->name('api.enrollment.registrations.slip');
    Route::get('/students', [EnrollmentController::class, 'students'])->name('api.enrollment.students.index');
    Route::get('/students/{id}', [EnrollmentController::class, 'showStudent'])->name('api.enrollment.students.show');
    Route::get('/registrations', [EnrollmentController::class, 'termRegistrations'])->name('api.enrollment.registrations.index');
    Route::get('/course-enrollments', [EnrollmentController::class, 'courseEnrollments'])->name('api.enrollment.enrollments.index');
});
