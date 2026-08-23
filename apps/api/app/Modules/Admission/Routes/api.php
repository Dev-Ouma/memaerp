<?php

declare(strict_types=1);

use App\Modules\Admission\Http\Controllers\AdmissionsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/admissions')->group(function (): void {
    Route::get('/catalogue', [AdmissionsController::class, 'catalogue'])->name('api.admissions.catalogue');
    Route::post('/register', [AdmissionsController::class, 'register'])->name('api.admissions.register');

    Route::middleware(['auth:sanctum', 'iam.session'])->group(function (): void {
        Route::get('/dashboard', [AdmissionsController::class, 'dashboard'])->name('api.admissions.dashboard');
        Route::get('/applications', [AdmissionsController::class, 'index'])->name('api.admissions.applications.index');
        Route::post('/applications', [AdmissionsController::class, 'store'])->name('api.admissions.applications.store');
        Route::get('/applications/{application}', [AdmissionsController::class, 'show'])->name('api.admissions.applications.show');
        Route::get('/applications/{application}/status', [AdmissionsController::class, 'status'])->name('api.admissions.applications.status');
        Route::post('/applications/{application}/documents', [AdmissionsController::class, 'uploadDocument'])->name('api.admissions.applications.documents');
        Route::post('/applications/{application}/pay', [AdmissionsController::class, 'pay'])->name('api.admissions.applications.pay');
        Route::post('/applications/{application}/submit', [AdmissionsController::class, 'submit'])->name('api.admissions.applications.submit');
        Route::post('/applications/{application}/begin-review', [AdmissionsController::class, 'beginReview'])->name('api.admissions.applications.begin-review');
        Route::post('/applications/{application}/verify', [AdmissionsController::class, 'verify'])->name('api.admissions.applications.verify');
        Route::post('/applications/{application}/decide', [AdmissionsController::class, 'decide'])->name('api.admissions.applications.decide');
        Route::post('/applications/{application}/accept-offer', [AdmissionsController::class, 'acceptOffer'])->name('api.admissions.applications.accept-offer');
        Route::get('/applications/{application}/offer-letter', [AdmissionsController::class, 'offerLetter'])->name('api.admissions.applications.offer-letter');
        Route::post('/kuccps/import', [AdmissionsController::class, 'importKuccps'])->name('api.admissions.kuccps.import');
        Route::get('/prospects', [AdmissionsController::class, 'prospects'])->name('api.admissions.prospects.index');
        Route::post('/prospects', [AdmissionsController::class, 'storeProspect'])->name('api.admissions.prospects.store');
        Route::get('/report', [AdmissionsController::class, 'report'])->name('api.admissions.report');
        Route::get('/fee-report', [AdmissionsController::class, 'feeReport'])->name('api.admissions.fee-report');
    });
});
