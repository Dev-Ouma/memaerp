<?php

declare(strict_types=1);

use App\Modules\Lms\Http\Controllers\LmsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/lms')->middleware(['auth:sanctum', 'iam.session'])->group(function (): void {
    Route::get('/sync/status', [LmsController::class, 'status'])->name('api.lms.sync.status');
    Route::post('/sync/courses', [LmsController::class, 'syncCourse'])->name('api.lms.sync.courses');
    Route::post('/sync/enrollments', [LmsController::class, 'syncEnrollment'])->name('api.lms.sync.enrollments');
    Route::post('/sync/grades/pull', [LmsController::class, 'pullGrades'])->name('api.lms.sync.grades.pull');
    Route::get('/launch', [LmsController::class, 'launchUrl'])->name('api.lms.launch');
});
