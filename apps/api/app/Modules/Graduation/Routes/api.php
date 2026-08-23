<?php

declare(strict_types=1);

use App\Modules\Graduation\Http\Controllers\GraduationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/graduation')->group(function (): void {
    Route::get('/verify-certificate/{token}', [GraduationController::class, 'verifyCertificate'])
        ->name('api.graduation.verify-certificate');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/apply', [GraduationController::class, 'apply'])->name('api.graduation.apply');
        Route::get('/clearance-status', [GraduationController::class, 'clearanceStatus'])->name('api.graduation.clearance-status');
        Route::get('/clearance-queue', [GraduationController::class, 'clearanceQueue'])->name('api.graduation.clearance-queue');
        Route::post('/checkpoints/{checkpoint}/clear', [GraduationController::class, 'clearCheckpoint'])
            ->whereUuid('checkpoint')
            ->name('api.graduation.checkpoints.clear');
        Route::get('/transcript', [GraduationController::class, 'transcript'])->name('api.graduation.transcript');
        Route::get('/certificates/{certificate}', [GraduationController::class, 'certificate'])
            ->whereUuid('certificate')
            ->name('api.graduation.certificates.show');
    });
});
