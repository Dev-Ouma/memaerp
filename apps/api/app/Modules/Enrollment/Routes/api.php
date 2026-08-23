<?php

declare(strict_types=1);

use App\Modules\Enrollment\Http\Controllers\EnrollmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/enrollment')->middleware('auth:sanctum')->group(function (): void {
    Route::get('/students', [EnrollmentController::class, 'students'])->name('api.enrollment.students.index');
    Route::get('/students/{id}', [EnrollmentController::class, 'showStudent'])->name('api.enrollment.students.show');
    Route::get('/registrations', [EnrollmentController::class, 'termRegistrations'])->name('api.enrollment.registrations.index');
    Route::get('/course-enrollments', [EnrollmentController::class, 'courseEnrollments'])->name('api.enrollment.enrollments.index');
});
