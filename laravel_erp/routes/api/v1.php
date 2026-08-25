<?php

declare(strict_types=1);

use App\Modules\Admission\Http\Controllers\ApplicantApplicationController;
use App\Modules\Admission\Http\Controllers\AuthController;
use App\Modules\Admission\Http\Controllers\PublicProgrammeController;
use App\Modules\Platform\Api\ApiResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => ApiResponse::data([
    'name' => 'Mema College Admission API',
    'version' => 'v1',
    'documentation' => url('/docs/api/admission-openapi.yaml'),
]))->name('api.v1.index');

Route::middleware('cache.public:60')->group(function (): void {
    Route::get('/public/programme-offerings', [PublicProgrammeController::class, 'index']);
    Route::get('/public/programme-offerings/{offering}', [PublicProgrammeController::class, 'show']);
});

Route::prefix('auth')->middleware('throttle:10,1')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('api.token')->group(function (): void {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/applications', [ApplicantApplicationController::class, 'index']);
    Route::post('/applications', [ApplicantApplicationController::class, 'store'])->middleware('idempotent');
    Route::get('/applications/{application}', [ApplicantApplicationController::class, 'show']);
    Route::patch('/applications/{application}', [ApplicantApplicationController::class, 'update'])->middleware('idempotent');
    Route::post('/applications/{application}/payment-attempts', [ApplicantApplicationController::class, 'payment'])->middleware('idempotent:required');
    Route::post('/applications/{application}/submit', [ApplicantApplicationController::class, 'submit'])->middleware('idempotent:required');
});
