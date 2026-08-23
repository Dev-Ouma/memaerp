<?php

declare(strict_types=1);

use App\Modules\Course\Http\Controllers\CourseController;
use App\Modules\Course\Http\Controllers\TimetableController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/courses')->middleware(['auth:sanctum', 'iam.session'])->group(function (): void {
    Route::get('/', [CourseController::class, 'index'])->name('api.courses.index');
    Route::post('/', [CourseController::class, 'store'])->name('api.courses.store');
    Route::get('/dashboard', [CourseController::class, 'dashboard'])->name('api.courses.dashboard');
    Route::get('/lecturers', [CourseController::class, 'lecturers'])->name('api.courses.lecturers');
    Route::get('/report', [CourseController::class, 'catalogueReport'])->name('api.courses.report');
    Route::get('/offerings/active', [CourseController::class, 'activeOfferings'])->name('api.courses.offerings.active');
    Route::get('/offerings/report', [CourseController::class, 'offeringReport'])->name('api.courses.offerings.report');
    Route::get('/offerings', [CourseController::class, 'offerings'])->name('api.courses.offerings.index');
    Route::post('/offerings', [CourseController::class, 'storeOffering'])->name('api.courses.offerings.store');
    Route::post('/offerings/{offering}/assign-lecturer', [CourseController::class, 'assignLecturer'])->name('api.courses.offerings.assign-lecturer');
    Route::post('/offerings/{offering}/close', [CourseController::class, 'closeOffering'])->name('api.courses.offerings.close');
    Route::post('/offerings/{offering}/waitlist', [CourseController::class, 'joinWaitlist'])->name('api.courses.offerings.waitlist');
    Route::get('/{course}', [CourseController::class, 'show'])->name('api.courses.show');
    Route::patch('/{course}', [CourseController::class, 'update'])->name('api.courses.update');
    Route::post('/{course}/submit', [CourseController::class, 'submit'])->name('api.courses.submit');
    Route::post('/{course}/approve', [CourseController::class, 'approve'])->name('api.courses.approve');
    Route::post('/{course}/prerequisites', [CourseController::class, 'addPrerequisite'])->name('api.courses.prerequisites.store');
    Route::delete('/{course}/prerequisites/{requirement}', [CourseController::class, 'destroyPrerequisite'])->name('api.courses.prerequisites.destroy');
    Route::get('/{course}/syllabus', [CourseController::class, 'syllabus'])->name('api.courses.syllabus');
});

Route::prefix('v1/timetable')->middleware(['auth:sanctum', 'iam.session'])->group(function (): void {
    Route::get('/my-schedule', [TimetableController::class, 'mySchedule'])->name('api.timetable.my-schedule');
    Route::get('/export.ics', [TimetableController::class, 'exportIcs'])->name('api.timetable.export-ics');
    Route::post('/clash-check', [TimetableController::class, 'clashCheck'])->name('api.timetable.clash-check');
    Route::post('/slots', [TimetableController::class, 'storeSlot'])->name('api.timetable.slots.store');
    Route::get('/rooms', [TimetableController::class, 'rooms'])->name('api.timetable.rooms.index');
    Route::get('/offerings/{offering}/slots', [TimetableController::class, 'offeringSlots'])->name('api.timetable.offerings.slots');
});
