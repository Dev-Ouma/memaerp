<?php

declare(strict_types=1);

use App\Modules\Student\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/students/verify-id/{token}', [StudentController::class, 'verifyId'])
        ->name('api.students.verify-id');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/students/dashboard', [StudentController::class, 'dashboard'])->name('api.students.dashboard');
        Route::get('/students/matriculation-queue', [StudentController::class, 'matriculationQueue'])
            ->name('api.students.matriculation-queue');
        Route::post('/students/matriculate', [StudentController::class, 'matriculate'])->name('api.students.matriculate');
        Route::get('/students/report', [StudentController::class, 'report'])->name('api.students.report');
        Route::get('/students/by-number/{studentNumber}', [StudentController::class, 'showByNumber'])
            ->name('api.students.show-by-number');
        Route::get('/students/{student}/digital-id', [StudentController::class, 'digitalId'])
            ->whereUuid('student')
            ->name('api.students.digital-id');
        Route::patch('/students/{student}/status', [StudentController::class, 'updateStatus'])
            ->whereUuid('student')
            ->name('api.students.update-status');
        Route::get('/students', [StudentController::class, 'index'])->name('api.students.index');
        Route::get('/students/{student}', [StudentController::class, 'show'])
            ->whereUuid('student')
            ->name('api.students.show');
        Route::patch('/students/{student}', [StudentController::class, 'update'])
            ->whereUuid('student')
            ->name('api.students.update');
    });
});
