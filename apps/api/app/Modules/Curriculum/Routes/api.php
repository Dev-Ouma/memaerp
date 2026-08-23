<?php

declare(strict_types=1);

use App\Modules\Curriculum\Http\Controllers\ProgrammeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/curriculum')->middleware('auth:sanctum')->group(function (): void {
    Route::get('/programmes', [ProgrammeController::class, 'index'])->name('api.curriculum.programmes.index');
    Route::get('/programmes/{id}', [ProgrammeController::class, 'show'])->name('api.curriculum.programmes.show');
    Route::post('/programmes', [ProgrammeController::class, 'store'])->name('api.curriculum.programmes.store');
});
