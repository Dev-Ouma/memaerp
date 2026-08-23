<?php

declare(strict_types=1);

use App\Modules\Examination\Http\Controllers\ExaminationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/exams')->middleware('auth:sanctum')->group(function (): void {
    Route::get('/marks-sheet/{offeringId}', [ExaminationController::class, 'marksSheet'])->name('api.exams.marks-sheet');
    Route::get('/term-gpas', [ExaminationController::class, 'termGpas'])->name('api.exams.term-gpas');
});
