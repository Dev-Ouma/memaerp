<?php

declare(strict_types=1);

use App\Modules\Portal\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/portal/student')->middleware('auth:sanctum')->group(function (): void {
    Route::get('/dashboard', [PortalController::class, 'dashboard'])->name('api.portal.student.dashboard');
    Route::get('/alerts', [PortalController::class, 'alerts'])->name('api.portal.student.alerts');
    Route::get('/documents', [PortalController::class, 'documents'])->name('api.portal.student.documents');
    Route::get('/preferences', [PortalController::class, 'preferences'])->name('api.portal.student.preferences');
    Route::patch('/preferences', [PortalController::class, 'updatePreferences'])->name('api.portal.student.preferences.update');
});
