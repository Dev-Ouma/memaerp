<?php

declare(strict_types=1);

use App\Modules\Attendance\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/attendance')->middleware(['auth:sanctum', 'iam.session'])->group(function (): void {
    Route::post('/sessions/open', [AttendanceController::class, 'openSession'])->name('api.attendance.sessions.open');
    Route::get('/sessions/active', [AttendanceController::class, 'activeSessions'])->name('api.attendance.sessions.active');
    Route::get('/sessions/{sessionId}/qr', [AttendanceController::class, 'sessionQr'])->name('api.attendance.sessions.qr');
    Route::post('/sessions/{sessionId}/close', [AttendanceController::class, 'closeSession'])->name('api.attendance.sessions.close');
    Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('api.attendance.check-in');
    Route::get('/my-record', [AttendanceController::class, 'myRecord'])->name('api.attendance.my-record');
    Route::get('/course/{offeringId}/report', [AttendanceController::class, 'courseReport'])->name('api.attendance.course.report');
    Route::get('/at-risk', [AttendanceController::class, 'atRisk'])->name('api.attendance.at-risk');
});
