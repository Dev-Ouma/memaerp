<?php

declare(strict_types=1);

use App\Modules\Institution\Http\Controllers\InstitutionAdminController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/institution')->middleware(['auth:sanctum', 'iam.session'])->group(function (): void {
    Route::get('/overview', [InstitutionAdminController::class, 'overview']);
    Route::get('/campuses', [InstitutionAdminController::class, 'campuses']);
    Route::post('/campuses', [InstitutionAdminController::class, 'storeCampus']);
    Route::patch('/campuses/{campus}', [InstitutionAdminController::class, 'updateCampus']);
    Route::get('/faculties', [InstitutionAdminController::class, 'faculties']);
    Route::post('/faculties', [InstitutionAdminController::class, 'storeFaculty']);
    Route::patch('/faculties/{faculty}', [InstitutionAdminController::class, 'updateFaculty']);
    Route::get('/schools', [InstitutionAdminController::class, 'faculties']);
    Route::get('/departments', [InstitutionAdminController::class, 'departments']);
    Route::post('/departments', [InstitutionAdminController::class, 'storeDepartment']);
    Route::patch('/departments/{department}', [InstitutionAdminController::class, 'updateDepartment']);
    Route::get('/units', [InstitutionAdminController::class, 'units']);
    Route::post('/units', [InstitutionAdminController::class, 'storeUnit']);
    Route::patch('/units/{unit}', [InstitutionAdminController::class, 'updateUnit']);
    Route::get('/academic-years', [InstitutionAdminController::class, 'academicYears']);
    Route::get('/academic-years/current', [InstitutionAdminController::class, 'currentAcademicYear']);
    Route::post('/academic-years', [InstitutionAdminController::class, 'storeAcademicYear']);
    Route::post('/academic-years/{academicYear}/activate', [InstitutionAdminController::class, 'activateAcademicYear']);
    Route::post('/terms', [InstitutionAdminController::class, 'storeTerm']);
    Route::post('/terms/{term}/activate', [InstitutionAdminController::class, 'activateTerm']);
    Route::get('/study-modes', [InstitutionAdminController::class, 'studyModes']);
    Route::post('/study-modes', [InstitutionAdminController::class, 'storeStudyMode']);
    Route::patch('/study-modes/{studyMode}', [InstitutionAdminController::class, 'updateStudyMode']);
    Route::get('/intakes', [InstitutionAdminController::class, 'intakes']);
    Route::post('/intakes', [InstitutionAdminController::class, 'storeIntake']);
    Route::patch('/intakes/{intake}', [InstitutionAdminController::class, 'updateIntake']);
    Route::get('/calendar-events', [InstitutionAdminController::class, 'calendarEvents']);
    Route::post('/calendar-events', [InstitutionAdminController::class, 'storeCalendarEvent']);
    Route::get('/lookups/{type}', [InstitutionAdminController::class, 'lookups']);
    Route::post('/lookups/{type}', [InstitutionAdminController::class, 'storeLookup']);
    Route::patch('/lookups/{type}/{lookup}', [InstitutionAdminController::class, 'updateLookup']);
    Route::get('/reports/directory', [InstitutionAdminController::class, 'directoryReport']);
    Route::get('/reports/calendar', [InstitutionAdminController::class, 'calendarReport']);
});
