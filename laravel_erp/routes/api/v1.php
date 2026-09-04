<?php

declare(strict_types=1);

use App\Modules\Admission\Http\Controllers\AdminSetupApiController;
use App\Modules\Admission\Http\Controllers\ApplicantApplicationController;
use App\Modules\Admission\Http\Controllers\AuthController;
use App\Modules\Admission\Http\Controllers\PaymentWebhookController;
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

Route::prefix('admin/setups')->middleware(['api.token', 'permission:platform.system.configure'])->group(function (): void {
    Route::get('/', [AdminSetupApiController::class, 'index']);
    Route::get('/{setup}', [AdminSetupApiController::class, 'show']);
    Route::post('/{setup}/versions', [AdminSetupApiController::class, 'store'])->middleware('idempotent');
    Route::post('/versions/{version}/publish', [AdminSetupApiController::class, 'publish'])->middleware('idempotent:required');
});

/*
| Provider callbacks. Unauthenticated by design — the caller is a payment
| provider, not a user — so each handler authenticates the request itself by
| HMAC signature and source address before reading the body. Throttled well
| above normal provider volume purely to bound an abusive caller.
*/
Route::middleware('throttle:300,1')->prefix('webhooks/payments')->group(function (): void {
    Route::post('/mpesa/stk', [PaymentWebhookController::class, 'mpesaStk'])->name('webhooks.payments.mpesa.stk');
    Route::post('/mpesa/c2b', [PaymentWebhookController::class, 'mpesaC2b'])->name('webhooks.payments.mpesa.c2b');
    Route::post('/card', [PaymentWebhookController::class, 'card'])->name('webhooks.payments.card');
});
