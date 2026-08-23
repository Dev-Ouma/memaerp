<?php

declare(strict_types=1);

use App\Modules\Examination\Http\Controllers\ExaminationController;
use App\Modules\Examination\Http\Controllers\ProgressionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/exams')->group(function (): void {
    Route::get('/verify-card/{token}', [ExaminationController::class, 'verifyCard'])->name('api.exams.verify-card');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/my-card', [ExaminationController::class, 'myCard'])->name('api.exams.my-card');
        Route::get('/marks-sheet/{offeringId}', [ExaminationController::class, 'marksSheet'])->name('api.exams.marks-sheet');
        Route::post('/marks-sheet/{offeringId}/save', [ExaminationController::class, 'saveMarks'])->name('api.exams.marks.save');
        Route::post('/marks-sheet/{offeringId}/submit', [ExaminationController::class, 'submitMarks'])->name('api.exams.marks.submit');
        Route::post('/marks-sheet/{offeringId}/approve', [ExaminationController::class, 'approveMarks'])->name('api.exams.marks.approve');
        Route::get('/term-gpas', [ExaminationController::class, 'termGpas'])->name('api.exams.term-gpas');
    });
});

Route::prefix('v1/progression')->middleware('auth:sanctum')->group(function (): void {
    Route::post('/calculate-batch', [ProgressionController::class, 'calculateBatch'])->name('api.progression.calculate-batch');
    Route::post('/publish-results', [ProgressionController::class, 'publishResults'])->name('api.progression.publish-results');
    Route::get('/my-results', [ProgressionController::class, 'myResults'])->name('api.progression.my-results');
    Route::get('/result-slip/{term}', [ProgressionController::class, 'resultSlip'])
        ->whereUuid('term')
        ->name('api.progression.result-slip');
});
