<?php

use App\Http\Controllers\AcademicController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdmissionAdminController;
use App\Http\Controllers\ApplicantAdmissionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\PublicAdmissionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});
Route::get('/programmes/apply', [PublicAdmissionController::class, 'catalogue'])->name('admissions.catalogue');
Route::get('/programmes', [PublicAdmissionController::class, 'catalogue']);
Route::get('/programmes/{offering}', [PublicAdmissionController::class, 'apply']);
Route::get('/apply', [PublicAdmissionController::class, 'catalogue']);
Route::get('/register', [PublicAdmissionController::class, 'catalogue']);
foreach (['verify-email' => 'Verify your email', 'forgot-password' => 'Forgot password', 'reset-password' => 'Reset password', 'privacy' => 'Privacy notice', 'terms' => 'Terms of use', 'cookies' => 'Cookie information', 'accessibility' => 'Accessibility statement', 'support' => 'Admissions support'] as $path => $title) {
    Route::view('/'.$path, 'admissions.info', compact('title'));
}
Route::get('/apply/{offering}', [PublicAdmissionController::class, 'apply'])->name('admissions.apply');
Route::post('/apply/{offering}', [PublicAdmissionController::class, 'register'])->middleware('throttle:10,1')->name('admissions.register');
Route::get('/verify/admission/{token}', [PublicAdmissionController::class, 'verify'])->middleware('throttle:30,1')->name('admissions.verify');
Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/account/{section}', [AccountController::class, 'show'])->name('account.show');
    Route::put('/account/preferences', [AccountController::class, 'preferences'])->name('account.preferences');
    Route::post('/account/switch-role', [AccountController::class, 'switchRole'])->name('account.switch-role');
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
    Route::post('/admissions/applications/{application}/submit', [ApplicantAdmissionController::class, 'submit'])->name('admissions.application.submit');
    Route::post('/admissions/applications/{application}/offer-response', [ApplicantAdmissionController::class, 'respond'])->name('admissions.application.respond');
    Route::get('/admissions', [AdmissionAdminController::class, 'index'])->name('admissions.index');
    Route::get('/admissions/analytics', [AdmissionAdminController::class, 'analytics'])->name('admissions.analytics');
    Route::get('/admissions/reports', [AdmissionAdminController::class, 'reports'])->name('admissions.reports');
    foreach (['dashboard', 'applications', 'work-queues', 'document-verification', 'reviews', 'approvals', 'shortlists', 'offers', 'waitlists', 'admission-rolls', 'student-conversions', 'payments', 'payment-reconciliation', 'audit', 'setups'] as $workspace) {
        Route::get('/admissions/'.$workspace, [AdmissionAdminController::class, 'index']);
    }
    Route::get('/admissions/reports/applications.csv', [AdmissionAdminController::class, 'exportApplications'])->name('admissions.reports.applications');
    Route::get('/admissions/{application}', [AdmissionAdminController::class, 'show'])->name('admissions.show');
    Route::post('/admissions/{application}/reviews', [AdmissionAdminController::class, 'review'])->name('admissions.review');
    Route::post('/admissions/{application}/transition', [AdmissionAdminController::class, 'transition'])->name('admissions.transition');
    Route::get('/students', [AcademicController::class, 'students'])->name('students.index');
    Route::post('/students', [AcademicController::class, 'storeStudent'])->middleware('can:admin')->name('students.store');
    Route::delete('/students/{student}', [AcademicController::class, 'destroyStudent'])->middleware('can:admin')->name('students.destroy');
    Route::get('/courses', [AcademicController::class, 'courses'])->middleware('can:admin')->name('courses.index');
    Route::post('/courses', [AcademicController::class, 'storeCourse'])->middleware('can:admin')->name('courses.store');
    Route::delete('/courses/{course}', [AcademicController::class, 'destroyCourse'])->middleware('can:admin')->name('courses.destroy');
    Route::patch('/courses/{course}/sequence', [AcademicController::class, 'updateCourseSequence'])->middleware('can:admin')->name('courses.sequence');
    Route::get('/subjects', [AcademicController::class, 'subjects'])->name('subjects.index');
    Route::get('/results', [AcademicController::class, 'results'])->name('results.index');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::post('/impersonate/stop', [ImpersonationController::class, 'stop'])->name('impersonate.stop');
    Route::post('/impersonate/{user}', [ImpersonationController::class, 'start'])->middleware('can:admin')->name('impersonate.start');
});
