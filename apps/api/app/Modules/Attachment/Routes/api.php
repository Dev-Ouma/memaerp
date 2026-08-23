<?php

declare(strict_types=1);

use App\Modules\Attachment\Http\Controllers\AttachmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/attachment')->middleware(['auth:sanctum', 'iam.session'])->group(function (): void {
    Route::get('/dashboard', [AttachmentController::class, 'dashboard'])->name('api.attachment.dashboard');
    Route::get('/organizations', [AttachmentController::class, 'organizations'])->name('api.attachment.organizations');
    Route::post('/organizations', [AttachmentController::class, 'storeOrganization'])->name('api.attachment.organizations.store');
    Route::get('/my-status', [AttachmentController::class, 'myStatus'])->name('api.attachment.my-status');
    Route::get('/applications', [AttachmentController::class, 'applications'])->name('api.attachment.applications');
    Route::post('/applications', [AttachmentController::class, 'submitApplication'])->name('api.attachment.applications.submit');
    Route::post('/applications/{applicationId}/review', [AttachmentController::class, 'reviewApplication'])->name('api.attachment.applications.review');
    Route::get('/placements', [AttachmentController::class, 'placements'])->name('api.attachment.placements');
    Route::get('/my-supervised-placements', [AttachmentController::class, 'mySupervisedPlacements'])->name('api.attachment.my-supervised');
    Route::post('/applications/{applicationId}/placement', [AttachmentController::class, 'createPlacement'])->name('api.attachment.placements.create');
    Route::post('/placements/{placementId}/confirm-host', [AttachmentController::class, 'confirmHost'])->name('api.attachment.placements.confirm-host');
    Route::get('/placements/{placementId}/logbook', [AttachmentController::class, 'logbookEntries'])->name('api.attachment.logbook.index');
    Route::post('/placements/{placementId}/logbook', [AttachmentController::class, 'submitLogbook'])->name('api.attachment.logbook.submit');
    Route::post('/logbook/{entryId}/endorse', [AttachmentController::class, 'endorseLogbook'])->name('api.attachment.logbook.endorse');
    Route::post('/placements/{placementId}/assessments', [AttachmentController::class, 'submitAssessment'])->name('api.attachment.assessments.submit');
});
