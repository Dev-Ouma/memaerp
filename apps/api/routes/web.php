<?php

declare(strict_types=1);

use App\Modules\Admission\Models\Application;
use App\Modules\Course\Models\Course;
use App\Modules\Course\Models\CourseOffering;
use App\Modules\Curriculum\Models\Programme;
use App\Modules\Enrollment\Models\CourseEnrollment;
use App\Modules\Enrollment\Models\TermRegistration;
use App\Modules\Examination\Models\StudentMark;
use App\Modules\Examination\Models\TermGpa;
use App\Modules\Finance\Models\FeeStructure;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\Payment;
use App\Modules\Iam\Http\Controllers\AuthController;
use App\Modules\Iam\Models\Role;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Department;
use App\Modules\Institution\Models\Faculty;
use App\Modules\Institution\Models\Term;
use App\Modules\Student\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check() ? redirect('/dashboard') : redirect('/login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', function () {
        $programmes = Programme::with('department.faculty')->get();
        $courses = Course::with(['department', 'prerequisites.prerequisiteCourse'])->get();
        $offerings = CourseOffering::with(['course', 'term', 'campus', 'lecturer.person'])->get();
        $applications = Application::with(['person', 'programme', 'campus'])->latest()->get();
        $feeStructures = FeeStructure::with('programme')->get();
        $invoices = Invoice::with(['person', 'feeStructure', 'term'])->get();
        $payments = Payment::with(['person', 'invoice'])->latest()->get();
        $roles = Role::with('permissions')->get();
        $campuses = Campus::all();
        $faculties = Faculty::with('departments')->get();
        $departments = Department::all();
        $terms = Term::with('academicYear')->get();
        $loginAttempts = DB::table('iam.login_attempts')->orderByDesc('attempted_at')->limit(10)->get();

        // Student & Enrollment data
        $students = Student::with(['person', 'programme', 'campus'])->get();
        $termRegistrations = TermRegistration::with(['student.person', 'term.academicYear', 'courseEnrollments.courseOffering.course'])->get();
        $courseEnrollments = CourseEnrollment::with(['student.person', 'courseOffering.course', 'mark'])->get();
        $studentMarks = StudentMark::with(['courseEnrollment.student.person', 'courseEnrollment.courseOffering.course'])->get();
        $termGpas = TermGpa::with(['student.person', 'student.programme', 'term'])->get();

        $stats = [
            'userCount' => User::count(),
            'roleCount' => Role::count(),
            'programmeCount' => Programme::count(),
            'courseCount' => Course::count(),
            'offeringCount' => CourseOffering::count(),
            'applicantCount' => Application::count(),
            'admittedCount' => Application::whereIn('status', ['ADMITTED', 'ACCEPTED', 'MATRICULATED'])->count(),
            'revenueCollected' => Payment::where('status', 'COMPLETED')->sum('amount'),
            'studentCount' => Student::where('status', 'ACTIVE')->count(),
            'enrollmentCount' => CourseEnrollment::where('status', 'ENROLLED')->count(),
        ];

        return view('dashboard', compact(
            'programmes',
            'courses',
            'offerings',
            'applications',
            'feeStructures',
            'invoices',
            'payments',
            'roles',
            'campuses',
            'faculties',
            'departments',
            'terms',
            'loginAttempts',
            'students',
            'termRegistrations',
            'courseEnrollments',
            'studentMarks',
            'termGpas',
            'stats'
        ));
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
