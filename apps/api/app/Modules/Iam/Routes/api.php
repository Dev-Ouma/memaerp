<?php

declare(strict_types=1);

use App\Modules\Iam\Http\Controllers\AuthController;
use App\Modules\Iam\Http\Controllers\IamAdminController;
use App\Modules\Iam\Http\Controllers\MfaController;
use App\Modules\Iam\Http\Controllers\PasswordController;
use App\Modules\Iam\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/auth')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('api.auth.login');
    Route::post('/mfa/verify', [MfaController::class, 'verify'])->middleware('throttle:10,1')->name('api.auth.mfa.verify');
    Route::post('/password/forgot', [PasswordController::class, 'forgot'])->middleware('throttle:5,1')->name('api.auth.password.forgot');
    Route::post('/password/reset', [PasswordController::class, 'reset'])->middleware('throttle:5,1')->name('api.auth.password.reset');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('api.auth.logout-all');
        Route::get('/me', [AuthController::class, 'me'])->name('api.auth.me');
        Route::post('/password/change', [PasswordController::class, 'change'])->name('api.auth.password.change');
        Route::post('/mfa/setup', [MfaController::class, 'setup'])->name('api.auth.mfa.setup');
        Route::post('/mfa/confirm', [MfaController::class, 'confirm'])->name('api.auth.mfa.confirm');
        Route::delete('/mfa', [MfaController::class, 'disable'])->name('api.auth.mfa.disable');
        Route::get('/sessions', [SessionController::class, 'index'])->name('api.auth.sessions.index');
        Route::delete('/sessions/{session}', [SessionController::class, 'destroy'])->name('api.auth.sessions.destroy');
    });
});

Route::prefix('v1/iam')->middleware('auth:sanctum')->group(function (): void {
    Route::get('/users', [IamAdminController::class, 'users']);
    Route::post('/users', [IamAdminController::class, 'storeUser']);
    Route::patch('/users/{user}/status', [IamAdminController::class, 'updateStatus']);
    Route::post('/users/{user}/roles', [IamAdminController::class, 'assignRole']);
    Route::get('/roles', [IamAdminController::class, 'roles']);
});
