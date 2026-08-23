<?php

declare(strict_types=1);

use App\Modules\Advising\Http\Controllers\AdvisingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/advising')->middleware(['auth:sanctum', 'iam.session'])->group(function (): void {
    Route::get('/my-advisees', [AdvisingController::class, 'myAdvisees'])->name('api.advising.my-advisees');
    Route::get('/assignments', [AdvisingController::class, 'assignments'])->name('api.advising.assignments');
    Route::post('/assignments', [AdvisingController::class, 'assign'])->name('api.advising.assign');
    Route::get('/my-progress', [AdvisingController::class, 'myProgress'])->name('api.advising.my-progress');
    Route::get('/student/{studentId}/degree-audit', [AdvisingController::class, 'degreeAudit'])->name('api.advising.degree-audit');
    Route::get('/student/{studentId}/notes', [AdvisingController::class, 'studentNotes'])->name('api.advising.student.notes');
    Route::post('/notes', [AdvisingController::class, 'storeNote'])->name('api.advising.notes.store');
    Route::post('/sessions/request', [AdvisingController::class, 'requestSession'])->name('api.advising.sessions.request');
    Route::get('/sessions', [AdvisingController::class, 'mySessions'])->name('api.advising.sessions');
    Route::patch('/sessions/{sessionId}', [AdvisingController::class, 'updateSession'])->name('api.advising.sessions.update');
});
