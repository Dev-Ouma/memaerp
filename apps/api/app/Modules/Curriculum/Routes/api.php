<?php

declare(strict_types=1);

use App\Modules\Curriculum\Http\Controllers\ProgrammeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/curriculum')->middleware(['auth:sanctum', 'iam.session'])->group(function (): void {
    Route::get('/programmes', [ProgrammeController::class, 'index'])->name('api.curriculum.programmes.index');
    Route::post('/programmes', [ProgrammeController::class, 'store'])->name('api.curriculum.programmes.store');
    Route::get('/programmes/{programme}', [ProgrammeController::class, 'show'])->name('api.curriculum.programmes.show');
    Route::patch('/programmes/{programme}', [ProgrammeController::class, 'update'])->name('api.curriculum.programmes.update');
    Route::get('/programmes/{programme}/curricula', [ProgrammeController::class, 'versions']);
    Route::get('/courses', [ProgrammeController::class, 'courses']);
    Route::post('/versions', [ProgrammeController::class, 'storeVersion']);
    Route::post('/versions/{version}/elective-groups', [ProgrammeController::class, 'storeElectiveGroup']);
    Route::patch('/versions/{version}/elective-groups/{group}', [ProgrammeController::class, 'updateElectiveGroup']);
    Route::delete('/versions/{version}/elective-groups/{group}', [ProgrammeController::class, 'destroyElectiveGroup']);
    Route::post('/versions/{version}/courses', [ProgrammeController::class, 'storeCurriculumCourse']);
    Route::patch('/versions/{version}/courses/{item}', [ProgrammeController::class, 'updateCurriculumCourse']);
    Route::delete('/versions/{version}/courses/{item}', [ProgrammeController::class, 'destroyCurriculumCourse']);
    Route::post('/versions/{version}/requirements', [ProgrammeController::class, 'addRequirement']);
    Route::delete('/versions/{version}/requirements/{requirement}', [ProgrammeController::class, 'destroyRequirement']);
    Route::post('/versions/{version}/submit', [ProgrammeController::class, 'submit']);
    Route::post('/versions/{version}/approve', [ProgrammeController::class, 'approve']);
    Route::post('/versions/{version}/assign-cohort', [ProgrammeController::class, 'assignCohort']);
    Route::get('/versions/{version}/report', [ProgrammeController::class, 'report']);
});
