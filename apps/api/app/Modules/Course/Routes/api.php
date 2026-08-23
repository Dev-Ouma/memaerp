<?php

declare(strict_types=1);

use App\Modules\Course\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/courses')->middleware('auth:sanctum')->group(function (): void {
    Route::get('/', [CourseController::class, 'index'])->name('api.courses.index');
    Route::get('/offerings/active', [CourseController::class, 'activeOfferings'])->name('api.courses.offerings.active');
    Route::get('/{id}', [CourseController::class, 'show'])->name('api.courses.show');
    Route::post('/', [CourseController::class, 'store'])->name('api.courses.store');
});
