<?php

declare(strict_types=1);

use App\Modules\Student\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function (): void {
    Route::get('/students', [StudentController::class, 'index'])->name('api.students.index');
    Route::get('/students/{student}', [StudentController::class, 'show'])
        ->whereUuid('student')
        ->name('api.students.show');
    Route::patch('/students/{student}', [StudentController::class, 'update'])
        ->whereUuid('student')
        ->name('api.students.update');
});
