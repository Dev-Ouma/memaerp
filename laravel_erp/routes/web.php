<?php

use App\Http\Controllers\AcademicController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminSetupController;
use App\Http\Controllers\AdmissionAdminController;
use App\Http\Controllers\AdmissionWorkspaceActionController;
use App\Http\Controllers\ApplicantAdmissionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BudgetingController;
use App\Http\Controllers\CohortController;
use App\Http\Controllers\CurriculumController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardExportController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\ExaminationController;
use App\Http\Controllers\FeesController;
use App\Http\Controllers\GovernanceAdminController;
use App\Http\Controllers\GraduationController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\ImprestController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\LmsController;
use App\Http\Controllers\LoadBalancerController;
use App\Http\Controllers\OperationalRecordController;
use App\Http\Controllers\PgResearchActionController;
use App\Http\Controllers\PgResearchController;
use App\Http\Controllers\PublicAdmissionController;
use App\Http\Controllers\RecycleBinController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RoleAssignmentController;
use App\Http\Controllers\ServiceProvidersController;
use App\Http\Controllers\SmhrController;
use App\Http\Controllers\StudentTransferController;
use App\Http\Controllers\SystemMaintenanceController;
use App\Http\Controllers\TaskManagementController;
use App\Http\Controllers\WorkStudyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/docs/api/admission-openapi.yaml', static fn () => response()->file(
    base_path('docs/api/admission-openapi.yaml'),
    ['Content-Type' => 'application/yaml; charset=UTF-8'],
));

Route::middleware('guest')->group(function (): void {
    Route::get('/', [AuthController::class, 'create'])->name('login');
    Route::get('/login', [AuthController::class, 'create']);
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});
Route::middleware('module:admissions')->group(function (): void {
    Route::get('/programmes/apply', [PublicAdmissionController::class, 'catalogue'])->name('admissions.catalogue');
    Route::get('/programmes/brochure', [PublicAdmissionController::class, 'brochure'])->name('admissions.brochure');
    Route::get('/programmes/flier', [PublicAdmissionController::class, 'brochure'])->name('admissions.flier');
    Route::get('/programmes', [PublicAdmissionController::class, 'catalogue']);
    Route::get('/programmes/{offering}', [PublicAdmissionController::class, 'apply']);
    Route::get('/apply', [PublicAdmissionController::class, 'catalogue']);
});
Route::get('/terms', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/terms/export/pdf', [LegalController::class, 'terms'])->name('legal.terms.pdf');
Route::get('/privacy', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/privacy/export/pdf', [LegalController::class, 'privacy'])->name('legal.privacy.pdf');
Route::get('/cookies', [LegalController::class, 'cookies'])->name('legal.cookies');
Route::get('/cookies/export/pdf', [LegalController::class, 'cookies'])->name('legal.cookies.pdf');

foreach (['verify-email' => 'Verify your email', 'accessibility' => 'Accessibility statement', 'support' => 'Admissions support'] as $path => $title) {
    Route::view('/'.$path, 'admissions.info', compact('title'));
}
Route::get('/forgot-password', fn () => view('auth.forgot-password'))->name('password.request');
Route::get('/reset-password/{token}', fn (string $token, Request $request) => view('auth.reset-password', [
    'token' => $token,
    'email' => $request->query('email', ''),
]))->name('password.reset');
Route::middleware('module:admissions')->group(function (): void {
    Route::get('/apply/{offering}', [PublicAdmissionController::class, 'apply'])->name('admissions.apply');
    Route::post('/apply/{offering}', [PublicAdmissionController::class, 'register'])->middleware('throttle:10,1')->name('admissions.register');
    Route::get('/verify/admission/{token}', [PublicAdmissionController::class, 'verify'])->middleware('throttle:30,1')->name('admissions.verify');
});
Route::middleware('auth')->group(function (): void {
    Route::get('/admin/setups', [AdminSetupController::class, 'platformIndex'])->name('admin.setups.index');
    Route::middleware('module:admissions')->group(function (): void {
        Route::get('/admissions/setups', [AdminSetupController::class, 'index'])->name('admissions.setups.index');
        Route::get('/admissions/setups/{setup}', [AdminSetupController::class, 'show'])->name('admissions.setups.show');
        Route::post('/admissions/setups/{setup}/versions', [AdminSetupController::class, 'store'])->name('admissions.setups.store');
        Route::post('/admissions/setups/versions/{version}/publish', [AdminSetupController::class, 'publish'])->name('admissions.setups.publish');
        Route::patch('/admissions/setups/versions/{version}/status', [AdminSetupController::class, 'status'])->name('admissions.setups.status');
    });
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/dashboard/export', [DashboardExportController::class, 'export'])->name('dashboard.export');
    Route::get('/dashboard/records-preview', [DashboardExportController::class, 'preview'])->name('dashboard.records-preview');
    Route::post('/operational/{module}/{kind}', [OperationalRecordController::class, 'store'])->name('operational.records.store');
    Route::patch('/operational/records/{record}/status', [OperationalRecordController::class, 'updateStatus'])->name('operational.records.status');
    Route::get('/account/{section}', [AccountController::class, 'show'])->name('account.show');
    Route::put('/account/preferences', [AccountController::class, 'preferences'])->name('account.preferences');
    Route::post('/account/switch-role', [AccountController::class, 'switchRole'])->name('account.switch-role');

    // Profile Actions
    Route::post('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::post('/account/profile/avatar', [AccountController::class, 'uploadAvatar'])->name('account.profile.avatar.upload');
    Route::delete('/account/profile/avatar', [AccountController::class, 'deleteAvatar'])->name('account.profile.avatar.delete');
    Route::get('/account/profile/avatar/{user}', [AccountController::class, 'serveAvatar'])->name('account.profile.avatar.serve');
    Route::get('/verify/email/{token}', [AccountController::class, 'verifyPendingEmail'])->name('account.verify-email');

    // Security Actions
    Route::post('/account/security/password', [AccountController::class, 'changePassword'])->name('account.security.password');
    Route::post('/account/security/mfa', [AccountController::class, 'toggleMfa'])->name('account.security.mfa');
    Route::post('/account/security/keys', [AccountController::class, 'registerSecurityKey'])->name('account.security.keys.register');
    Route::delete('/account/security/keys/{key}', [AccountController::class, 'deleteSecurityKey'])->name('account.security.keys.delete');
    Route::post('/account/security/sessions/{session}/revoke', [AccountController::class, 'revokeSession'])->name('account.security.sessions.revoke');
    Route::post('/account/security/sessions/revoke-others', [AccountController::class, 'revokeOtherSessions'])->name('account.security.sessions.revoke-others');

    // Calendar Actions
    Route::post('/account/calendar/events', [AccountController::class, 'storeEvent'])->name('account.calendar.events.store');
    Route::put('/account/calendar/events/{event}', [AccountController::class, 'updateEvent'])->name('account.calendar.events.update');
    Route::delete('/account/calendar/events/{event}', [AccountController::class, 'deleteEvent'])->name('account.calendar.events.delete');
    Route::post('/account/calendar/google/connect', [AccountController::class, 'connectGoogleCalendar'])->name('account.calendar.google.connect');
    Route::post('/account/calendar/google/disconnect', [AccountController::class, 'disconnectGoogleCalendar'])->name('account.calendar.google.disconnect');
    Route::post('/account/calendar/google/sync', [AccountController::class, 'syncGoogleCalendar'])->name('account.calendar.google.sync');

    // Files Actions
    Route::post('/account/files/upload', [AccountController::class, 'uploadFile'])->name('account.files.upload');
    Route::post('/account/files/folder', [AccountController::class, 'createFolder'])->name('account.files.folder');
    Route::post('/account/files/rename/{file}', [AccountController::class, 'renameFile'])->name('account.files.rename');
    Route::post('/account/files/move/{file}', [AccountController::class, 'moveFile'])->name('account.files.move');
    Route::delete('/account/files/{file}', [AccountController::class, 'deleteFile'])->name('account.files.delete');
    Route::post('/account/files/restore/{id}', [AccountController::class, 'restoreFile'])->name('account.files.restore');
    Route::delete('/account/files/permanent/{id}', [AccountController::class, 'permanentDeleteFile'])->name('account.files.permanent');
    Route::get('/account/files/download/{file}', [AccountController::class, 'downloadFile'])->name('account.files.download');

    // Reports Actions
    Route::post('/account/reports', [AccountController::class, 'storeReport'])->name('account.reports.store');
    Route::put('/account/reports/{report}', [AccountController::class, 'updateReport'])->name('account.reports.update');
    Route::delete('/account/reports/{report}', [AccountController::class, 'deleteReport'])->name('account.reports.delete');
    Route::post('/account/reports/{report}/duplicate', [AccountController::class, 'duplicateReport'])->name('account.reports.duplicate');
    Route::get('/account/reports/{report}/run', [AccountController::class, 'runReport'])->name('account.reports.run');

    // Support Actions
    Route::post('/account/support/ticket', [AccountController::class, 'submitSupportTicket'])->name('account.support.ticket');

    Route::middleware('module:admissions')->group(function (): void {
        Route::get('/admissions/my-application', [ApplicantAdmissionController::class, 'portal'])->name('admissions.portal');
        foreach (['dashboard', 'profile', 'applications', 'applications/new', 'receipts', 'documents', 'settings', 'support'] as $path) {
            Route::get('/applicant/'.$path, [ApplicantAdmissionController::class, 'portal']);
        }
        foreach (['payment', 'review', 'timeline', 'documents', 'messages', 'offer'] as $section) {
            Route::get('/applicant/applications/{applicationId}/'.$section, [ApplicantAdmissionController::class, 'portal']);
        }
        Route::get('/applicant/applications/{applicationId}', [ApplicantAdmissionController::class, 'portal']);
        Route::put('/admissions/applications/{application}', [ApplicantAdmissionController::class, 'update'])->name('admissions.application.update');
        Route::post('/admissions/applications/{application}/documents', [ApplicantAdmissionController::class, 'upload'])->name('admissions.application.documents');
        Route::post('/admissions/applications/{application}/payment', [ApplicantAdmissionController::class, 'payment'])->name('admissions.application.payment');
        Route::post('/admissions/applications/{application}/payment/{attempt}/transaction-code', [ApplicantAdmissionController::class, 'declarePayment'])->middleware('throttle:20,1')->name('admissions.application.payment.declare');
        Route::post('/admissions/applications/{application}/submit', [ApplicantAdmissionController::class, 'submit'])->name('admissions.application.submit');
        Route::post('/admissions/applications/{application}/offer-response', [ApplicantAdmissionController::class, 'respond'])->name('admissions.application.respond');
        Route::post('/admissions/applications/{application}/enrol', [ApplicantAdmissionController::class, 'enrol'])->name('admissions.application.enrol');
        Route::get('/admissions', [AdmissionAdminController::class, 'index'])->name('admissions.index');
        Route::get('/admissions/analytics', [AdmissionAdminController::class, 'analytics'])->name('admissions.analytics');
        Route::get('/admissions/reports', [AdmissionAdminController::class, 'reports'])->name('admissions.reports');
        Route::get('/admissions/student-conversions', [AdmissionAdminController::class, 'conversions'])->name('admissions.conversions');
        Route::post('/admissions/conversions/{conversion}/retry', [AdmissionAdminController::class, 'retryConversion'])->name('admissions.conversions.retry');

        // Admissions Workspaces (End-to-End Lifecycle Operations)
        Route::get('/admissions/dashboard', [AdmissionAdminController::class, 'dashboard'])->name('admissions.workspace.dashboard');
        Route::get('/admissions/applications', [AdmissionAdminController::class, 'index'])->name('admissions.workspace.applications');
        Route::get('/admissions/work-queues', [AdmissionAdminController::class, 'workQueues'])->name('admissions.workspace.work-queues');
        Route::get('/admissions/document-verification', [AdmissionAdminController::class, 'documentVerification'])->name('admissions.workspace.document-verification');
        Route::get('/admissions/reviews', [AdmissionAdminController::class, 'reviews'])->name('admissions.workspace.reviews');
        Route::get('/admissions/shortlists', [AdmissionAdminController::class, 'shortlists'])->name('admissions.workspace.shortlists');
        Route::get('/admissions/approvals', [AdmissionAdminController::class, 'approvals'])->name('admissions.workspace.approvals');
        Route::get('/admissions/offers', [AdmissionAdminController::class, 'offers'])->name('admissions.workspace.offers');
        Route::get('/admissions/waitlists', [AdmissionAdminController::class, 'waitlists'])->name('admissions.workspace.waitlists');
        Route::get('/admissions/admission-rolls', [AdmissionAdminController::class, 'admissionRolls'])->name('admissions.workspace.admission-rolls');
        Route::get('/admissions/payments', [AdmissionAdminController::class, 'payments'])->name('admissions.workspace.payments');
        Route::get('/admissions/payment-reconciliation', [AdmissionAdminController::class, 'paymentReconciliation'])->name('admissions.workspace.payment-reconciliation');
        Route::get('/admissions/audit', [AdmissionAdminController::class, 'audit'])->name('admissions.workspace.audit');

        // Workspace actions — the write side of the screens above.
        Route::post('/admissions/work-queues/auto-assign', [AdmissionWorkspaceActionController::class, 'autoAssign'])->name('admissions.work-queues.auto-assign');
        Route::post('/admissions/reviews/assign', [AdmissionWorkspaceActionController::class, 'assignReviewers'])->name('admissions.reviews.assign');
        Route::post('/admissions/shortlists/submit-to-board', [AdmissionWorkspaceActionController::class, 'submitShortlistToBoard'])->name('admissions.shortlists.submit');
        Route::post('/admissions/shortlists/{application}/advance', [AdmissionWorkspaceActionController::class, 'advanceShortlist'])->name('admissions.shortlists.advance');
        Route::post('/admissions/approvals/authorize', [AdmissionWorkspaceActionController::class, 'authorizeOffers'])->name('admissions.approvals.authorize');
        Route::post('/admissions/approvals/{application}/sign-off', [AdmissionWorkspaceActionController::class, 'signOff'])->name('admissions.approvals.sign-off');
        Route::post('/admissions/waitlists/auto-promote', [AdmissionWorkspaceActionController::class, 'autoPromoteWaitlist'])->name('admissions.waitlists.auto-promote');
        Route::post('/admissions/waitlists/{application}/promote', [AdmissionWorkspaceActionController::class, 'promoteWaitlisted'])->name('admissions.waitlists.promote');
        Route::get('/admissions/admission-rolls/export.csv', [AdmissionWorkspaceActionController::class, 'exportRoll'])->name('admissions.rolls.export');
        Route::post('/admissions/payments/waiver', [AdmissionWorkspaceActionController::class, 'waiveFee'])->name('admissions.payments.waiver');
        Route::post('/admissions/payments/{attempt}/confirm', [AdmissionWorkspaceActionController::class, 'confirmPayment'])->name('admissions.payments.confirm');
        Route::post('/admissions/payments/{attempt}/reject', [AdmissionWorkspaceActionController::class, 'rejectPayment'])->name('admissions.payments.reject');
        Route::get('/admissions/payments/{attempt}/receipt', [AdmissionWorkspaceActionController::class, 'receipt'])->name('admissions.payments.receipt');
        Route::post('/admissions/payment-reconciliation/run', [AdmissionWorkspaceActionController::class, 'runReconciliation'])->name('admissions.reconciliation.run');
        Route::post('/admissions/audit/verify-integrity', [AdmissionWorkspaceActionController::class, 'verifyAuditIntegrity'])->name('admissions.audit.verify');

        Route::get('/admissions/document-templates', [DocumentTemplateController::class, 'index'])->name('admissions.templates');
        Route::get('/admissions/reports/applications.csv', [AdmissionAdminController::class, 'exportApplications'])->name('admissions.reports.applications');
        Route::get('/admissions/{application}', [AdmissionAdminController::class, 'show'])->name('admissions.show');
        Route::get('/admissions/applications/{application}/letter', [AdmissionAdminController::class, 'admissionLetter'])->name('admissions.application.letter');
        Route::get('/admissions/documents/{document}/download', [AdmissionAdminController::class, 'downloadDocument'])->name('admissions.document.download');
        Route::post('/admissions/documents/{document}/verify', [AdmissionAdminController::class, 'verifyDocument'])->name('admissions.document.verify');
        Route::post('/admissions/{application}/reviews', [AdmissionAdminController::class, 'review'])->name('admissions.review');
        Route::post('/admissions/{application}/transition', [AdmissionAdminController::class, 'transition'])->name('admissions.transition');
        Route::post('/admissions/applications/{application}/convert', [AdmissionAdminController::class, 'convertToStudent'])->name('admissions.application.convert');
    });
    Route::get('/students', [AcademicController::class, 'students'])->name('students.index');
    Route::post('/students', [AcademicController::class, 'storeStudent'])->middleware('can:admin')->name('students.store');
    Route::delete('/students/{student}', [AcademicController::class, 'destroyStudent'])->middleware('can:admin')->name('students.destroy');
    Route::get('/courses', [AcademicController::class, 'courses'])->middleware('can:admin')->name('courses.index');
    Route::post('/courses', [AcademicController::class, 'storeCourse'])->middleware('can:admin')->name('courses.store');
    Route::delete('/courses/{course}', [AcademicController::class, 'destroyCourse'])->middleware('can:admin')->name('courses.destroy');
    Route::patch('/courses/{course}/sequence', [AcademicController::class, 'updateCourseSequence'])->middleware('can:admin')->name('courses.sequence');
    Route::get('/subjects', [AcademicController::class, 'subjects'])->name('subjects.index');
    Route::get('/results', [AcademicController::class, 'results'])->name('results.index');

    // SMHR - Staff HR & Payroll Module Routes (Start-to-End Operational Lifecycle)
    Route::prefix('smhr')->middleware('module:smhr')->name('smhr.')->group(function (): void {
        Route::get('/', [SmhrController::class, 'dashboard'])->name('index');
        Route::get('/dashboard', [SmhrController::class, 'dashboard'])->name('dashboard');
        Route::get('/staff-directory', [SmhrController::class, 'staffDirectory'])->name('staff-directory');
        Route::post('/staff-directory', [SmhrController::class, 'storeStaff'])->name('staff-directory.store');
        Route::get('/onboarding', [SmhrController::class, 'onboarding'])->name('onboarding');
        Route::post('/onboarding', [SmhrController::class, 'storeOnboarding'])->name('onboarding.store');
        Route::get('/leave-management', [SmhrController::class, 'leaveManagement'])->name('leave-management');
        Route::post('/leave-management', [SmhrController::class, 'submitLeave'])->name('leave-management.store');
        Route::post('/leave-management/{id}/approve', [SmhrController::class, 'approveLeave'])->name('leave-management.approve');
        Route::post('/leave-management/{id}/reject', [SmhrController::class, 'rejectLeave'])->name('leave-management.reject');
        Route::get('/workload-allocation', [SmhrController::class, 'workloadAllocation'])->name('workload-allocation');
        Route::get('/performance-appraisals', [SmhrController::class, 'performanceAppraisals'])->name('performance-appraisals');
        Route::get('/payroll-register', [SmhrController::class, 'payrollRegister'])->name('payroll-register');
        Route::get('/payslip/{id?}', [SmhrController::class, 'payslip'])->name('payslip');
        Route::get('/p9-form/{staffId?}', [SmhrController::class, 'p9Form'])->name('p9-form');
        Route::get('/reports', [SmhrController::class, 'reports'])->name('reports');
        Route::get('/disciplinary-records', [SmhrController::class, 'disciplinaryRecords'])->name('disciplinary-records');
    });

    // Student Transfers Module Routes (Start-to-End Lifecycle)
    Route::prefix('transfers')->middleware('module:transfers')->name('transfers.')->group(function (): void {
        Route::get('/', [StudentTransferController::class, 'datesSetup'])->name('index');
        Route::get('/dates-setup', [StudentTransferController::class, 'datesSetup'])->name('dates-setup');
        Route::post('/dates-setup', [StudentTransferController::class, 'storeDate'])->name('dates.store');
        Route::get('/inter-intra', [StudentTransferController::class, 'interIntra'])->name('inter-intra');
        Route::post('/inter-intra', [StudentTransferController::class, 'storeInterIntra'])->name('inter-intra.store');
        Route::patch('/inter-intra/{transfer}/status', [StudentTransferController::class, 'updateInterIntraStatus'])->name('inter-intra.status');
        Route::get('/credit-transfers', [StudentTransferController::class, 'creditTransfers'])->name('credit-transfers');
        Route::post('/credit-transfers', [StudentTransferController::class, 'storeCredit'])->name('credits.store');
        Route::patch('/credit-transfers/{credit}/status', [StudentTransferController::class, 'updateCreditStatus'])->name('credits.status');
        Route::get('/exemptions', [StudentTransferController::class, 'exemptions'])->name('exemptions');
        Route::post('/exemptions', [StudentTransferController::class, 'storeExemption'])->name('exemptions.store');
        Route::patch('/exemptions/{exemption}/status', [StudentTransferController::class, 'updateExemptionStatus'])->name('exemptions.status');
    });

    // PG Research Management Module Routes (Start-to-End Lifecycle)
    Route::prefix('pg-research')->middleware('module:pg-research')->name('pg-research.')->group(function (): void {
        Route::get('/', [PgResearchController::class, 'supervisorRoles'])->name('index');
        Route::get('/supervisor-roles', [PgResearchController::class, 'supervisorRoles'])->name('supervisor-roles');
        Route::get('/eligibility-gating', [PgResearchController::class, 'eligibilityGating'])->name('eligibility-gating');
        Route::get('/supervisor-allocation', [PgResearchController::class, 'supervisorAllocation'])->name('supervisor-allocation');
        Route::get('/proposal-reader-review', [PgResearchController::class, 'proposalReaderReview'])->name('proposal-reader-review');
        Route::get('/seminar-presentations', [PgResearchController::class, 'seminarPresentations'])->name('seminar-presentations');
        Route::get('/progress-reports', [PgResearchController::class, 'progressReports'])->name('progress-reports');
        Route::get('/plagiarism-checker', [PgResearchController::class, 'plagiarismChecker'])->name('plagiarism-checker');
        Route::get('/defence-request-approval', [PgResearchController::class, 'defenceRequestApproval'])->name('defence-request-approval');
        Route::get('/examiner-dashboard', [PgResearchController::class, 'examinerDashboard'])->name('examiner-dashboard');
        Route::get('/viva-examination', [PgResearchController::class, 'vivaExamination'])->name('viva-examination');
        Route::get('/thesis-marks-approval', [PgResearchController::class, 'thesisMarksApproval'])->name('thesis-marks-approval');
        Route::get('/thesis-resubmission', [PgResearchController::class, 'thesisResubmission'])->name('thesis-resubmission');
        Route::get('/publications-review', [PgResearchController::class, 'publicationsReview'])->name('publications-review');
        Route::get('/legacy-migration', [PgResearchController::class, 'legacyMigration'])->name('legacy-migration');
        Route::get('/appeal-period-setup', [PgResearchController::class, 'appealPeriodSetup'])->name('appeal-period-setup');
        Route::get('/appeal-category', [PgResearchController::class, 'appealCategory'])->name('appeal-category');

        // Write operations. Every control on the screens above resolves to one of these.
        Route::controller(PgResearchActionController::class)->group(function (): void {
            Route::post('/candidates', 'storeCandidate')->name('candidates.store');
            Route::post('/candidates/{candidate}/recompute-eligibility', 'recomputeEligibility')->name('candidates.recompute');
            Route::post('/waivers', 'requestWaiver')->name('waivers.request');
            Route::post('/waivers/{waiver}/decide', 'decideWaiver')->name('waivers.decide');
            Route::post('/waivers/{waiver}/revoke', 'revokeWaiver')->name('waivers.revoke');

            Route::post('/supervisors', 'storeSupervisor')->name('supervisors.store');
            Route::post('/supervisors/{supervisor}/toggle', 'toggleSupervisor')->name('supervisors.toggle');
            Route::post('/allocations', 'allocateSupervisor')->name('allocations.store');
            Route::post('/allocations/{allocation}/end', 'endAllocation')->name('allocations.end');

            Route::post('/proposals', 'submitProposal')->name('proposals.store');
            Route::post('/proposals/{proposal}/reader', 'appointReader')->name('proposals.reader');
            Route::post('/proposals/{proposal}/review', 'reviewProposal')->name('proposals.review');

            Route::post('/seminars', 'scheduleSeminar')->name('seminars.store');
            Route::post('/seminars/{seminar}/conclude', 'concludeSeminar')->name('seminars.conclude');

            Route::post('/progress-reports', 'submitProgressReport')->name('progress-reports.store');
            Route::post('/progress-reports/{report}/decide', 'decideProgressReport')->name('progress-reports.decide');

            Route::post('/scans', 'recordScan')->name('scans.store');
            Route::post('/scans/{scan}/override', 'overrideScan')->name('scans.override');

            Route::post('/defence-requests', 'requestDefence')->name('defence-requests.store');
            Route::post('/defence-requests/{defenceRequest}/decide', 'decideDefence')->name('defence-requests.decide');

            Route::post('/examiners', 'appointExaminer')->name('examiners.store');
            Route::post('/examiners/{examiner}/report', 'submitExaminerReport')->name('examiners.report');

            Route::post('/vivas', 'scheduleViva')->name('vivas.store');
            Route::post('/vivas/{viva}/verdict', 'recordVivaVerdict')->name('vivas.verdict');

            Route::post('/marks/{mark}/ratify', 'ratifyMark')->name('marks.ratify');
            Route::post('/marks/{mark}/return', 'returnMark')->name('marks.return');

            Route::post('/resubmissions/{resubmission}/submit', 'submitResubmission')->name('resubmissions.submit');
            Route::post('/resubmissions/{resubmission}/verify', 'verifyResubmission')->name('resubmissions.verify');

            Route::post('/publications', 'submitPublication')->name('publications.store');
            Route::post('/publications/{publication}/decide', 'decidePublication')->name('publications.decide');

            Route::post('/legacy', 'stageLegacyRecord')->name('legacy.store');
            Route::post('/legacy/batch-import', 'importLegacyBatch')->name('legacy.batch');
            Route::post('/legacy/{migration}/import', 'importLegacyRecord')->name('legacy.import');
            Route::post('/legacy/{migration}/verify', 'verifyLegacyRecord')->name('legacy.verify');

            Route::post('/appeal-categories', 'storeAppealCategory')->name('appeal-categories.store');
            Route::put('/appeal-categories/{category}', 'updateAppealCategory')->name('appeal-categories.update');
            Route::post('/appeal-categories/{category}/toggle', 'toggleAppealCategory')->name('appeal-categories.toggle');

            Route::post('/appeal-periods', 'storeAppealPeriod')->name('appeal-periods.store');
            Route::post('/appeal-periods/{period}/open', 'openAppealPeriod')->name('appeal-periods.open');
            Route::post('/appeal-periods/{period}/close', 'closeAppealPeriod')->name('appeal-periods.close');

            Route::post('/appeals', 'lodgeAppeal')->name('appeals.store');
            Route::post('/appeals/{appeal}/assign', 'assignAppeal')->name('appeals.assign');
            Route::post('/appeals/{appeal}/decide', 'decideAppeal')->name('appeals.decide');
        });
    });

    // Curriculum Setup Module Routes
    Route::prefix('curriculum')->middleware('module:curriculum')->name('curriculum.')->group(function (): void {
        Route::get('/', [CurriculumController::class, 'school'])->name('index');
        Route::get('/school', [CurriculumController::class, 'school'])->name('school');
        Route::post('/school', [CurriculumController::class, 'storeSchool'])->name('school.store');
        Route::put('/school/{school}', [CurriculumController::class, 'updateSchool'])->name('school.update');
        Route::delete('/school/{school}', [CurriculumController::class, 'destroySchool'])->middleware('can:admin')->name('school.destroy');
        Route::get('/department', [CurriculumController::class, 'department'])->name('department');
        Route::post('/department', [CurriculumController::class, 'storeDepartment'])->name('department.store');
        Route::put('/department/{department}', [CurriculumController::class, 'updateDepartment'])->name('department.update');
        Route::delete('/department/{department}', [CurriculumController::class, 'destroyDepartment'])->middleware('can:admin')->name('department.destroy');
        Route::get('/program-type', [CurriculumController::class, 'programType'])->name('program-type');
        Route::get('/programme', [CurriculumController::class, 'programme'])->name('programme');
        Route::post('/programme', [CurriculumController::class, 'storeProgramme'])->name('programme.store');
        Route::put('/programme/{programme}', [CurriculumController::class, 'updateProgramme'])->name('programme.update');
        Route::delete('/programme/{programme}', [CurriculumController::class, 'destroyProgramme'])->middleware('can:admin')->name('programme.destroy');
        Route::get('/programme-curriculum', [CurriculumController::class, 'programmeCurriculum'])->name('programme-curriculum');
        Route::get('/course-unit', [CurriculumController::class, 'courseUnit'])->name('course-unit');
        Route::get('/course-unit/template', [CurriculumController::class, 'courseUnitTemplate'])->name('course-unit.template');
        Route::post('/course-unit', [CurriculumController::class, 'storeCourseUnit'])->name('course-unit.store');
        Route::put('/course-unit/{courseUnit}', [CurriculumController::class, 'updateCourseUnit'])->name('course-unit.update');
        Route::delete('/course-unit/{courseUnit}', [CurriculumController::class, 'destroyCourseUnit'])->middleware('can:admin')->name('course-unit.destroy');
        Route::get('/specialisation', [CurriculumController::class, 'specialisation'])->name('specialisation');
        Route::get('/student-specialization-mapping', [CurriculumController::class, 'studentSpecializationMapping'])->name('student-specialization-mapping');
        Route::get('/instructor-mapping', [CurriculumController::class, 'instructorMapping'])->name('instructor-mapping');
        Route::get('/cluster-subjects', [CurriculumController::class, 'clusterSubjects'])->name('cluster-subjects');
        Route::get('/program-cluster-mapping', [CurriculumController::class, 'programClusterMapping'])->name('program-cluster-mapping');
        Route::get('/progression-criteria', [CurriculumController::class, 'progressionCriteria'])->name('progression-criteria');
        Route::get('/short-course-creation', [CurriculumController::class, 'shortCourseCreation'])->name('short-course-creation');
    });

    // Work Study Module Routes (Student Affairs Lifecycle)
    Route::prefix('work-study')->middleware('module:student-affairs')->name('work-study.')->group(function (): void {
        Route::get('/', [WorkStudyController::class, 'periodSetup'])->name('index');
        Route::get('/period-setup', [WorkStudyController::class, 'periodSetup'])->name('period-setup');
        Route::get('/positions', [WorkStudyController::class, 'positions'])->name('positions');
        Route::get('/applications', [WorkStudyController::class, 'applications'])->name('applications');
        Route::get('/allocations', [WorkStudyController::class, 'allocations'])->name('allocations');
        Route::get('/timesheets', [WorkStudyController::class, 'timesheets'])->name('timesheets');
        Route::get('/claims', [WorkStudyController::class, 'claims'])->name('claims');
    });

    // Imprest Management Module Routes (Start-to-End Lifecycle)
    Route::prefix('imprest')->middleware('module:imprest')->name('imprest.')->group(function (): void {
        Route::get('/', [ImprestController::class, 'permissions'])->name('index');
        Route::get('/permissions', [ImprestController::class, 'permissions'])->name('permissions');
        Route::get('/claim-approval-permission', [ImprestController::class, 'claimApprovals'])->name('claim-approvals');
        Route::get('/imprest-surrender-permission', [ImprestController::class, 'surrenderPermissions'])->name('surrender-permissions');
        Route::get('/requisitions', [ImprestController::class, 'requisitions'])->name('requisitions');
        Route::get('/surrenders', [ImprestController::class, 'surrenders'])->name('surrenders');
        Route::get('/audit-ledger', [ImprestController::class, 'auditLedger'])->name('audit-ledger');
    });

    // Cohort Setup Module Routes (Lifecycle Sequence)
    Route::prefix('cohort')->middleware('module:cohort')->name('cohort.')->group(function (): void {
        Route::get('/', [CohortController::class, 'academicYear'])->name('index');
        Route::get('/academic-year', [CohortController::class, 'academicYear'])->name('academic-year');
        Route::post('/academic-year', [CohortController::class, 'storeAcademicYear'])->name('academic-year.store');
        Route::put('/academic-year/{academicYear}', [CohortController::class, 'updateAcademicYear'])->name('academic-year.update');
        Route::delete('/academic-year/{academicYear}', [CohortController::class, 'destroyAcademicYear'])->name('academic-year.destroy');
        Route::get('/cohort-creation', [CohortController::class, 'cohortCreation'])->name('cohort-creation');
        Route::get('/programme-cohort-mapping', [CohortController::class, 'programmeCohortMapping'])->name('programme-cohort-mapping');
        Route::get('/publish-finance', [CohortController::class, 'publishFinance'])->name('publish-finance');
        Route::get('/cohort-transfer', [CohortController::class, 'cohortTransfer'])->name('cohort-transfer');
    });

    // Registration Module Routes (Start-to-End Operational Lifecycle)
    Route::prefix('registration')->middleware('module:registration')->name('registration.')->group(function (): void {
        Route::get('/', [RegistrationController::class, 'applicationVerification'])->name('index');
        Route::get('/application-verification', [RegistrationController::class, 'applicationVerification'])->name('application-verification');
        Route::get('/application-approval', [RegistrationController::class, 'applicationApproval'])->name('application-approval');
        Route::get('/rejected-list', [RegistrationController::class, 'rejectedList'])->name('rejected-list');
        Route::get('/kuccps-registration', [RegistrationController::class, 'kuccpsRegistration'])->name('kuccps-registration');
        Route::post('/kuccps-registration', [RegistrationController::class, 'storeKuccps'])->name('kuccps.store');
        Route::get('/student-registrations', [RegistrationController::class, 'studentRegistrations'])->name('student-registrations');
        Route::get('/course-registration-periods', [RegistrationController::class, 'courseRegistrationPeriods'])->name('course-registration-periods');
        Route::post('/course-registration-periods', [RegistrationController::class, 'storePeriod'])->name('periods.store');
        Route::post('/course-enrolments', [RegistrationController::class, 'storeEnrolment'])->name('enrolments.store');
        Route::get('/promotions', [RegistrationController::class, 'promotions'])->name('promotions');
        Route::post('/promotions', [RegistrationController::class, 'storePromotion'])->name('promotions.store');
        Route::get('/professional-development-users', [RegistrationController::class, 'professionalDevelopmentUsers'])->name('professional-development-users');
        Route::post('/professional-development-users', [RegistrationController::class, 'storeCpd'])->name('cpd.store');
        Route::get('/moodle-sync', [RegistrationController::class, 'moodleSync'])->name('moodle-sync');
        Route::post('/moodle-sync', [RegistrationController::class, 'storeMoodle'])->name('moodle.store');
        Route::get('/student-info-update', [RegistrationController::class, 'studentInfoUpdate'])->name('student-info-update');
        Route::post('/student-info-update', [RegistrationController::class, 'storeInfoUpdate'])->name('info-updates.store');
        Route::get('/reminders', [RegistrationController::class, 'reminders'])->name('reminders');
        Route::post('/reminders', [RegistrationController::class, 'storeReminder'])->name('reminders.store');
        Route::get('/user-registration', [RegistrationController::class, 'userRegistration'])->name('user-registration');
        Route::get('/student-password', [RegistrationController::class, 'studentPassword'])->name('student-password');
        Route::get('/staff-password', [RegistrationController::class, 'staffPassword'])->name('staff-password');
        Route::get('/password-reset', [RegistrationController::class, 'passwordReset'])->name('password-reset');
    });

    // LMS Virtual Learning Module Routes (Start-to-End Operational Lifecycle)
    Route::prefix('lms')->middleware('module:lms')->name('lms.')->group(function (): void {
        Route::get('/', [LmsController::class, 'courseShells'])->name('index');
        Route::get('/course-shells', [LmsController::class, 'courseShells'])->name('course-shells');
        Route::get('/lecturer-assignments', [LmsController::class, 'lecturerAssignments'])->name('lecturer-assignments');
        Route::get('/live-lectures', [LmsController::class, 'liveLectures'])->name('live-lectures');
        Route::get('/e-resources', [LmsController::class, 'eResources'])->name('e-resources');
        Route::get('/assignments', [LmsController::class, 'assignments'])->name('assignments');
        Route::get('/student-analytics', [LmsController::class, 'studentAnalytics'])->name('student-analytics');
        Route::get('/discussion-forums', [LmsController::class, 'discussionForums'])->name('discussion-forums');
        Route::get('/online-quizzes', [LmsController::class, 'onlineQuizzes'])->name('online-quizzes');
        Route::get('/gradebook-sync', [LmsController::class, 'gradebookSync'])->name('gradebook-sync');
    });

    // Examination Module Routes (Start-to-End Operational Lifecycle)
    Route::prefix('examination')->middleware('module:examination')->name('examination.')->group(function (): void {
        Route::get('/', [ExaminationController::class, 'examCenter'])->name('index');
        Route::get('/exam-center', [ExaminationController::class, 'examCenter'])->name('exam-center');
        Route::post('/exam-center', [ExaminationController::class, 'storeCenter'])->middleware('can:admin')->name('exam-center.store');
        Route::get('/exam-session', [ExaminationController::class, 'examSession'])->name('exam-session');
        Route::post('/exam-session', [ExaminationController::class, 'storeSession'])->middleware('can:admin')->name('exam-session.store');
        Route::get('/exam-schedule', [ExaminationController::class, 'examSchedule'])->name('exam-schedule');
        Route::post('/exam-schedule', [ExaminationController::class, 'storeSchedule'])->middleware('can:admin')->name('exam-schedule.store');
        Route::get('/marks-capture', [ExaminationController::class, 'marksCapture'])->name('marks-capture');
        Route::post('/marks-capture', [ExaminationController::class, 'storeResult'])->name('marks-capture.store');
        Route::get('/marks-submission', [ExaminationController::class, 'marksSubmission'])->name('marks-submission');
        Route::get('/marks-approval', [ExaminationController::class, 'marksApproval'])->name('marks-approval');
        Route::get('/marks-publish', [ExaminationController::class, 'marksPublish'])->name('marks-publish');
        Route::get('/scores-analysis', [ExaminationController::class, 'scoresAnalysis'])->name('scores-analysis');
        Route::get('/summary-results', [ExaminationController::class, 'summaryResults'])->name('summary-results');
        Route::get('/grades-config', [ExaminationController::class, 'gradesConfig'])->name('grades-config');
        Route::post('/grades-config', [ExaminationController::class, 'storeGradeScale'])->middleware('can:admin')->name('grades-config.store');
        Route::get('/pass-list', [ExaminationController::class, 'passList'])->name('pass-list');
        Route::get('/progression-list', [ExaminationController::class, 'progressionList'])->name('progression-list');
        Route::get('/fail-list', [ExaminationController::class, 'failList'])->name('fail-list');
        Route::get('/provisional-transcript', [ExaminationController::class, 'provisionalTranscript'])->name('provisional-transcript');
        Route::get('/academic-transcript', [ExaminationController::class, 'academicTranscript'])->name('academic-transcript');
        Route::get('/transcript-requests', [ExaminationController::class, 'transcriptRequests'])->name('transcript-requests');
        Route::get('/senate-reports', [ExaminationController::class, 'senateReports'])->name('senate-reports');
        Route::get('/consolidated-marksheets', [ExaminationController::class, 'consolidatedMarksheets'])->name('consolidated-marksheets');
    });

    // Fees Module Routes (Start-to-End Operational Lifecycle)
    Route::prefix('fees')->middleware('module:fees')->name('fees.')->group(function (): void {
        Route::get('/', [FeesController::class, 'paymentAccounts'])->name('index');
        Route::get('/payment-accounts', [FeesController::class, 'paymentAccounts'])->name('payment-accounts');
        Route::post('/payment-accounts', [FeesController::class, 'storeAccount'])->name('accounts.store');
        Route::get('/payment-types', [FeesController::class, 'paymentTypes'])->name('payment-types');
        Route::post('/payment-types', [FeesController::class, 'storeType'])->name('types.store');
        Route::get('/payment-source', [FeesController::class, 'paymentSource'])->name('payment-source');
        Route::post('/payment-source', [FeesController::class, 'storeSource'])->name('sources.store');
        Route::get('/fee-setup', [FeesController::class, 'feeSetup'])->name('fee-setup');
        Route::post('/fee-setup', [FeesController::class, 'storeStructure'])->name('structures.store');
        Route::get('/fee-payables', [FeesController::class, 'feePayables'])->name('fee-payables');
        Route::post('/fee-payments', [FeesController::class, 'storePayment'])->name('payments.store');
        Route::post('/fee-payments/{payment}/confirm', [FeesController::class, 'confirmPayment'])->name('payments.confirm');
        Route::get('/pending-payments', [FeesController::class, 'pendingPayments'])->name('pending-payments');
        Route::get('/payment-receipt', [FeesController::class, 'paymentReceipt'])->name('payment-receipt');
    });

    // Graduation Module Routes (Start-to-End Operational Lifecycle)
    Route::prefix('graduation')->middleware('module:graduation')->name('graduation.')->group(function (): void {
        Route::get('/', [GraduationController::class, 'criteria'])->name('index');
        Route::get('/criteria', [GraduationController::class, 'criteria'])->name('criteria');
        Route::post('/criteria', [GraduationController::class, 'storeCriteria'])->name('criteria.store');
        Route::get('/clearance-checklist', [GraduationController::class, 'clearanceChecklist'])->name('clearance-checklist');
        Route::post('/clearance-checklist', [GraduationController::class, 'storeClearanceChecklist'])->name('clearance-checklist.store');
        Route::get('/finance-clearance', [GraduationController::class, 'financeClearance'])->name('finance-clearance');
        Route::post('/finance-clearance', [GraduationController::class, 'storeFinanceClearance'])->name('finance-clearance.store');
        Route::get('/grade-list', [GraduationController::class, 'gradeList'])->name('grade-list');
        Route::post('/grade-list', [GraduationController::class, 'storeGradeList'])->name('grade-list.store');
        Route::get('/generate-list', [GraduationController::class, 'generateList'])->name('generate-list');
        Route::post('/generate-list', [GraduationController::class, 'storeGenerateList'])->name('generate-list.store');
        Route::get('/validate-list', [GraduationController::class, 'validateList'])->name('validate-list');
        Route::post('/validate-list', [GraduationController::class, 'storeValidateList'])->name('validate-list.store');
        Route::get('/publish-list', [GraduationController::class, 'publishList'])->name('publish-list');
        Route::post('/publish-list', [GraduationController::class, 'storePublishList'])->name('publish-list.store');
        Route::get('/list-report', [GraduationController::class, 'listReport'])->name('list-report');
        Route::post('/list-report', [GraduationController::class, 'storeListReport'])->name('list-report.store');
        Route::get('/summary-list', [GraduationController::class, 'summaryList'])->name('summary-list');
        Route::post('/summary-list', [GraduationController::class, 'storeSummaryList'])->name('summary-list.store');
        Route::get('/certification-setup', [GraduationController::class, 'certificationSetup'])->name('certification-setup');
        Route::post('/certification-setup', [GraduationController::class, 'storeCertificationSetup'])->name('certification-setup.store');
        Route::get('/alumni-list', [GraduationController::class, 'alumniList'])->name('alumni-list');
        Route::post('/alumni-list', [GraduationController::class, 'storeAlumniList'])->name('alumni-list.store');
        Route::get('/ceremony', [GraduationController::class, 'ceremony'])->name('ceremony');
        Route::post('/ceremony', [GraduationController::class, 'storeCeremony'])->name('ceremony.store');
        Route::get('/ceremony-report', [GraduationController::class, 'ceremonyReport'])->name('ceremony-report');
        Route::post('/ceremony-report', [GraduationController::class, 'storeCeremonyReport'])->name('ceremony-report.store');
    });

    // User Management & Task Management Module Routes (Start-to-End Operational Lifecycle)
    Route::redirect('/user-management', '/task-management');
    Route::redirect('/users', '/task-management');
    Route::prefix('task-management')->middleware('module:task-management')->name('task-management.')->group(function (): void {
        Route::get('/', [TaskManagementController::class, 'landing'])->name('index');
        Route::get('/college-users', [TaskManagementController::class, 'collegeUsers'])->name('users');
        Route::post('/college-users', [TaskManagementController::class, 'storeUser'])->middleware('can:admin')->name('users.store');
        Route::patch('/college-users/{user}', [TaskManagementController::class, 'updateUser'])->middleware('can:admin')->name('users.update');
        Route::post('/college-users/{user}/toggle-status', [TaskManagementController::class, 'toggleStatus'])->middleware('can:admin')->name('users.toggle-status');
        Route::post('/college-users/assign-role', [TaskManagementController::class, 'assignRole'])->middleware('can:admin')->name('users.assign-role');
        Route::delete('/college-users/role-assignment/{assignment}', [TaskManagementController::class, 'revokeRoleAssignment'])->middleware('can:admin')->name('users.revoke-role');
        Route::get('/roles', [TaskManagementController::class, 'roles'])->name('roles');
        Route::post('/roles', [TaskManagementController::class, 'storeRole'])->middleware('can:admin')->name('roles.store');
        Route::get('/task-roles', [TaskManagementController::class, 'taskRoles'])->name('task-roles');
        Route::post('/task-roles', [TaskManagementController::class, 'storeTaskRole'])->middleware('can:admin')->name('task-roles.store');
        Route::get('/task-manager', [TaskManagementController::class, 'taskManager'])->name('task-manager');
        Route::post('/task-manager', [TaskManagementController::class, 'storeTask'])->middleware('can:admin')->name('tasks.store');
        Route::post('/task-manager/{task}/transition', [TaskManagementController::class, 'transitionTask'])->name('tasks.transition');
    });

    // Reports & Intelligence Center Routes (Core Dashboards)
    Route::prefix('reports')->middleware('module:reports')->name('reports.')->group(function (): void {
        Route::get('/', [ReportsController::class, 'advancedAnalytics'])->name('index');
        Route::get('/advanced-analytics', [ReportsController::class, 'advancedAnalytics'])->name('advanced-analytics');

        // 13 Core Institutional Admission Reports + Module Reports
        Route::get('/application-register', [ReportsController::class, 'showReport'])->defaults('report', 'application-register')->name('application-register');
        Route::get('/applications-by-programme', [ReportsController::class, 'showReport'])->defaults('report', 'applications-by-programme')->name('applications-by-programme');
        Route::get('/admission-status-summary', [ReportsController::class, 'showReport'])->defaults('report', 'admission-status-summary')->name('admission-status-summary');
        Route::get('/review-workload', [ReportsController::class, 'showReport'])->defaults('report', 'review-workload')->name('review-workload');
        Route::get('/outstanding-documents', [ReportsController::class, 'showReport'])->defaults('report', 'outstanding-documents')->name('outstanding-documents');
        Route::get('/shortlisted-waitlisted', [ReportsController::class, 'showReport'])->defaults('report', 'shortlisted-waitlisted')->name('shortlisted-waitlisted');
        Route::get('/admitted-letters', [ReportsController::class, 'showReport'])->defaults('report', 'admitted-letters')->name('admitted-letters');
        Route::get('/rejected-withdrawn-deferred', [ReportsController::class, 'showReport'])->defaults('report', 'rejected-withdrawn-deferred')->name('rejected-withdrawn-deferred');
        Route::get('/offer-acceptance-expiry', [ReportsController::class, 'showReport'])->defaults('report', 'offer-acceptance-expiry')->name('offer-acceptance-expiry');
        Route::get('/payments-clearance', [ReportsController::class, 'showReport'])->defaults('report', 'payments-clearance')->name('payments-clearance');
        Route::get('/enrolled-students', [ReportsController::class, 'showReport'])->defaults('report', 'enrolled-students')->name('enrolled-students');
        Route::get('/programme-capacity-conversion', [ReportsController::class, 'showReport'])->defaults('report', 'programme-capacity-conversion')->name('programme-capacity-conversion');
        Route::get('/audit-trail', [ReportsController::class, 'showReport'])->defaults('report', 'audit-trail')->name('audit-trail');

        // Export Report (CSV / XLSX / PDF)
        Route::get('/{report}/export', [ReportsController::class, 'exportReport'])->name('export');

        // 29 Specific Submodule Reports
        Route::get('/application-status', [ReportsController::class, 'showReport'])->defaults('report', 'application-status')->name('application-status');
        Route::get('/programme-applicants', [ReportsController::class, 'showReport'])->defaults('report', 'programme-applicants')->name('programme-applicants');
        Route::get('/registration-report', [ReportsController::class, 'showReport'])->defaults('report', 'registration-report')->name('registration-report');
        Route::get('/gender-wise-list', [ReportsController::class, 'showReport'])->defaults('report', 'gender-wise-list')->name('gender-wise-list');
        Route::get('/dynamic-report', [ReportsController::class, 'showReport'])->defaults('report', 'dynamic-report')->name('dynamic-report');
        Route::get('/dynamic-payment', [ReportsController::class, 'showReport'])->defaults('report', 'dynamic-payment')->name('dynamic-payment');
        Route::get('/student-fee-statement', [ReportsController::class, 'showReport'])->defaults('report', 'student-fee-statement')->name('student-fee-statement');
        Route::get('/user-details', [ReportsController::class, 'showReport'])->defaults('report', 'user-details')->name('user-details');
        Route::get('/nominal-roll', [ReportsController::class, 'showReport'])->defaults('report', 'nominal-roll')->name('nominal-roll');
        Route::get('/student-registration-details', [ReportsController::class, 'showReport'])->defaults('report', 'student-registration-details')->name('student-registration-details');
        Route::get('/course-registration', [ReportsController::class, 'showReport'])->defaults('report', 'course-registration')->name('course-registration');
        Route::get('/exemption-report', [ReportsController::class, 'showReport'])->defaults('report', 'exemption-report')->name('exemption-report');
        Route::get('/reattempt-report', [ReportsController::class, 'showReport'])->defaults('report', 'reattempt-report')->name('reattempt-report');
        Route::get('/cohort-curriculum-mapping', [ReportsController::class, 'showReport'])->defaults('report', 'cohort-curriculum-mapping')->name('cohort-curriculum-mapping');
        Route::get('/audit-trail-user', [ReportsController::class, 'showReport'])->defaults('report', 'audit-trail-user')->name('audit-trail-user');
        Route::get('/student-progression', [ReportsController::class, 'showReport'])->defaults('report', 'student-progression')->name('student-progression');
        Route::get('/report-by-source', [ReportsController::class, 'showReport'])->defaults('report', 'report-by-source')->name('report-by-source');
        Route::get('/fee-movement', [ReportsController::class, 'showReport'])->defaults('report', 'fee-movement')->name('fee-movement');
        Route::get('/debtors-report', [ReportsController::class, 'showReport'])->defaults('report', 'debtors-report')->name('debtors-report');
        Route::get('/fee-overpayment', [ReportsController::class, 'showReport'])->defaults('report', 'fee-overpayment')->name('fee-overpayment');
        Route::get('/search-student-short-courses', [ReportsController::class, 'showReport'])->defaults('report', 'search-student-short-courses')->name('search-student-short-courses');
        Route::get('/search-payment-source', [ReportsController::class, 'showReport'])->defaults('report', 'search-payment-source')->name('search-payment-source');
        Route::get('/search-transaction-id', [ReportsController::class, 'showReport'])->defaults('report', 'search-transaction-id')->name('search-transaction-id');
        Route::get('/fees-collection', [ReportsController::class, 'showReport'])->defaults('report', 'fees-collection')->name('fees-collection');
        Route::get('/fee-summary', [ReportsController::class, 'showReport'])->defaults('report', 'fee-summary')->name('fee-summary');
        Route::get('/student-invoices', [ReportsController::class, 'showReport'])->defaults('report', 'student-invoices')->name('student-invoices');
        Route::get('/debtors-ageing-analysis', [ReportsController::class, 'showReport'])->defaults('report', 'debtors-ageing-analysis')->name('debtors-ageing-analysis');
        Route::get('/kuccps-students', [ReportsController::class, 'showReport'])->defaults('report', 'kuccps-students')->name('kuccps-students');
        Route::get('/student-provisional-transcripts', [ReportsController::class, 'showReport'])->defaults('report', 'student-provisional-transcripts')->name('student-provisional-transcripts');
    });

    // Service Providers Module Routes (Start-to-End Operational Lifecycle)
    Route::prefix('service-providers')->middleware('module:service-providers')->name('service-providers.')->group(function (): void {
        Route::get('/taxes', [ServiceProvidersController::class, 'taxes'])->name('taxes');
        Route::get('/items', [ServiceProvidersController::class, 'items'])->name('items');
        Route::get('/provider-groups', [ServiceProvidersController::class, 'providerGroups'])->name('provider-groups');
        Route::get('/', [ServiceProvidersController::class, 'providers'])->name('index');
        Route::get('/providers', [ServiceProvidersController::class, 'providers'])->name('providers');
        Route::get('/vendor-approval', [ServiceProvidersController::class, 'vendorApproval'])->name('vendor-approval');
        Route::get('/invoice-permissions', [ServiceProvidersController::class, 'invoicePermissions'])->name('invoice-permissions');
        Route::get('/bills', [ServiceProvidersController::class, 'bills'])->name('bills');
        Route::get('/payment-permissions', [ServiceProvidersController::class, 'paymentPermissions'])->name('payment-permissions');
        Route::get('/payments', [ServiceProvidersController::class, 'payments'])->name('payments');
        Route::get('/debit-notes', [ServiceProvidersController::class, 'debitNotes'])->name('debit-notes');
        Route::get('/credit-notes', [ServiceProvidersController::class, 'creditNotes'])->name('credit-notes');
    });

    // Budgeting and Planning Module Routes (Start-to-End Operational Lifecycle)
    Route::prefix('budgeting')->middleware('module:budgeting')->name('budgeting.')->group(function (): void {
        Route::get('/', [BudgetingController::class, 'permissions'])->name('index');
        Route::get('/permissions', [BudgetingController::class, 'permissions'])->name('permissions');
        Route::post('/permissions', [BudgetingController::class, 'storeSubmitter'])->middleware('can:admin')->name('permissions.store');
        Route::delete('/permissions/{submitter}', [BudgetingController::class, 'destroySubmitter'])->middleware('can:admin')->name('permissions.destroy');
        Route::get('/proposals', [BudgetingController::class, 'proposals'])->name('proposals');
        Route::post('/proposals', [BudgetingController::class, 'storeProposal'])->name('proposals.store');
        Route::post('/proposals/{proposal}/transition', [BudgetingController::class, 'transition'])->name('proposals.transition');
    });

    // Admin Setups Submodules Routes
    Route::prefix('admin-setups')->name('admin.setups.')->group(function (): void {
        Route::get('/document-templates', [DocumentTemplateController::class, 'index'])->name('document-templates');
        Route::get('/document-templates/{templateKey}/preview', [DocumentTemplateController::class, 'preview'])->name('document-templates.preview');
        Route::get('/document-templates/{templateKey}/pdf', [DocumentTemplateController::class, 'pdf'])->name('document-templates.pdf');
        Route::get('/accounting', [AdminSetupController::class, 'accounting'])->name('accounting');
        Route::get('/bank', [AdminSetupController::class, 'bank'])->name('bank');
        Route::get('/invoicing', [AdminSetupController::class, 'invoicing'])->name('invoicing');
        Route::get('/payment', [AdminSetupController::class, 'payment'])->name('payment');
        Route::get('/module-manager', [AdminSetupController::class, 'moduleManager'])->name('module-manager');
        Route::patch('/module-manager/toggle', [AdminSetupController::class, 'toggleModule'])->name('module-manager.toggle');
        Route::post('/module-manager/enable-all', [AdminSetupController::class, 'enableAllModules'])->name('module-manager.enable-all');
        Route::match(['GET', 'POST'], '/module-manager/integrity', [AdminSetupController::class, 'verifyModuleIntegrity'])->name('module-manager.integrity');
        Route::match(['GET', 'POST'], '/module-manager/audit-integrity', [AdminSetupController::class, 'verifyModuleIntegrity'])->name('module-manager.audit-integrity');
        Route::prefix('access')->middleware('can:platform.role.manage')->name('access.')->group(function (): void {
            Route::get('/', [RoleAssignmentController::class, 'index'])->name('index');
            Route::post('/assignments', [RoleAssignmentController::class, 'store'])->name('assignments.store');
            Route::delete('/assignments/{assignment}', [RoleAssignmentController::class, 'destroy'])->name('assignments.destroy');
        });
        Route::prefix('governance')->name('governance.')->group(function (): void {
            Route::get('/', [GovernanceAdminController::class, 'index'])->middleware('can:platform.audit.view')->name('index');
            Route::post('/retention-rules', [GovernanceAdminController::class, 'storeRetentionRule'])->middleware('can:platform.retention.execute')->name('retention.store');
            Route::post('/legal-holds', [GovernanceAdminController::class, 'placeHold'])->middleware('can:platform.retention.execute')->name('holds.store');
            Route::patch('/legal-holds/{hold}/release', [GovernanceAdminController::class, 'releaseHold'])->middleware('can:platform.retention.execute')->name('holds.release');
            Route::get('/audit', [GovernanceAdminController::class, 'audit'])->middleware('can:platform.audit.view')->name('audit');
        });
        // Recycle Bin & Data Recovery Routes
        Route::prefix('recycle-bin')->name('recycle-bin.')->group(function (): void {
            Route::get('/', [RecycleBinController::class, 'index'])->middleware('can:platform.audit.view')->name('index');
            Route::post('/restore/{deletion}', [RecycleBinController::class, 'restore'])->middleware('can:platform.retention.execute')->name('restore');
            Route::post('/purge/{deletion}/request', [RecycleBinController::class, 'requestPurge'])->middleware('can:platform.retention.execute')->name('purge.request');
            Route::post('/purge-requests/{action}/approve', [RecycleBinController::class, 'approvePurge'])->middleware('can:platform.retention.execute')->name('purge.approve');
            Route::delete('/empty', [RecycleBinController::class, 'emptyBin'])->middleware('can:platform.retention.execute')->name('empty');
            Route::post('/restore-all', [RecycleBinController::class, 'restoreAll'])->middleware('can:platform.retention.execute')->name('restore-all');
        });

        // System Maintenance & Administration Routes
        Route::prefix('system-maintenance')->name('system-maintenance.')->group(function (): void {
            Route::get('/', [SystemMaintenanceController::class, 'index'])->name('index');
            Route::post('/lockdown', [SystemMaintenanceController::class, 'toggleLockdown'])->name('lockdown.update');
            Route::post('/clear-cache', [SystemMaintenanceController::class, 'clearCache'])->name('cache.clear');
            Route::post('/cache/purge', [SystemMaintenanceController::class, 'purgeCaches'])->name('cache.purge');
            Route::post('/cache/rebuild', [SystemMaintenanceController::class, 'rebuildCaches'])->name('cache.rebuild');
            Route::post('/optimize', [SystemMaintenanceController::class, 'runOptimization'])->name('optimize');
            Route::post('/backup', [SystemMaintenanceController::class, 'triggerBackup'])->name('backup.create');
            Route::get('/backup/{backup}/download', [SystemMaintenanceController::class, 'downloadBackup'])->name('backup.download');
            Route::post('/backup/{backup}/restore', [SystemMaintenanceController::class, 'restoreBackup'])->name('backup.restore');
            Route::post('/upgrade', [SystemMaintenanceController::class, 'applyUpgrade'])->name('upgrade');
            Route::post('/upgrade/apply', [SystemMaintenanceController::class, 'applyUpgrade'])->name('upgrade.apply');
            Route::post('/version/{version}/rollback', [SystemMaintenanceController::class, 'triggerRollback'])->name('version.rollback');
            Route::post('/broadcast', [SystemMaintenanceController::class, 'sendBroadcast'])->name('broadcast');
            Route::post('/cron', [SystemMaintenanceController::class, 'runCron'])->name('cron');
            Route::post('/cron/run', [SystemMaintenanceController::class, 'runCron'])->name('cron.run');
            Route::post('/codebase', [SystemMaintenanceController::class, 'syncCodebase'])->name('codebase.sync');
            Route::post('/cloud-mirror', [SystemMaintenanceController::class, 'cloudMirror'])->name('cloud-mirror');
        });

        // Load Balancer & Queuing Strategy Routes
        Route::get('/load-balancer', [LoadBalancerController::class, 'index'])->name('load-balancer');
        Route::prefix('load-balancer')->name('load-balancer.')->group(function (): void {
            Route::post('/strategy', [LoadBalancerController::class, 'updateStrategy'])->name('strategy');
            Route::post('/config', [LoadBalancerController::class, 'updateConfig'])->name('config');
            Route::post('/nodes', [LoadBalancerController::class, 'storeNode'])->name('store-node');
            Route::post('/nodes/{id}/toggle', [LoadBalancerController::class, 'toggleNode'])->name('toggle-node');
            Route::delete('/nodes/{id}', [LoadBalancerController::class, 'destroyNode'])->name('destroy-node');
            Route::post('/simulate', [LoadBalancerController::class, 'simulate'])->name('simulate');
            Route::post('/health-check', [LoadBalancerController::class, 'healthCheck'])->name('health-check');
            Route::post('/nodes/{node}/toggle', [LoadBalancerController::class, 'toggleNode'])->name('nodes.toggle');
            Route::post('/nodes/{node}/drain', [LoadBalancerController::class, 'drainNode'])->name('nodes.drain');
            Route::post('/queues/pause', [LoadBalancerController::class, 'pauseQueues'])->name('queues.pause');
            Route::post('/queues/resume', [LoadBalancerController::class, 'resumeQueues'])->name('queues.resume');
            Route::post('/queues/flush-failed', [LoadBalancerController::class, 'flushFailed'])->name('queues.flush-failed');
            Route::post('/queues/retry-failed', [LoadBalancerController::class, 'retryFailed'])->name('queues.retry-failed');
            Route::post('/restart-workers', [LoadBalancerController::class, 'restartWorkers'])->name('restart-workers');
        });
    });

    // Standalone Recycle Bin & Data Recovery Module Routes
    Route::prefix('recycle-bin')->middleware('can:platform.audit.view')->group(function (): void {
        Route::get('/', [RecycleBinController::class, 'index'])->name('recycle-bin');
        Route::get('/all', [RecycleBinController::class, 'index'])->name('recycle-bin.index');
        Route::post('/restore/{deletion}', [RecycleBinController::class, 'restore'])->middleware('can:platform.retention.execute')->name('recycle-bin.restore');
        Route::post('/purge/{deletion}/request', [RecycleBinController::class, 'requestPurge'])->middleware('can:platform.retention.execute')->name('recycle-bin.purge.request');
        Route::post('/purge-requests/{action}/approve', [RecycleBinController::class, 'approvePurge'])->middleware('can:platform.retention.execute')->name('recycle-bin.purge.approve');
        Route::delete('/empty', [RecycleBinController::class, 'emptyBin'])->middleware('can:platform.retention.execute')->name('recycle-bin.empty');
        Route::post('/restore-all', [RecycleBinController::class, 'restoreAll'])->middleware('can:platform.retention.execute')->name('recycle-bin.restore-all');
    });

    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::post('/impersonate/stop', [ImpersonationController::class, 'stop'])->name('impersonate.stop');
    Route::post('/impersonate/{user}', [ImpersonationController::class, 'start'])->middleware('can:admin')->name('impersonate.start');
});
