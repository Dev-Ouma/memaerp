<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Services\StudentRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AcademicController extends Controller
{
    public function __construct(private readonly StudentRegistrationService $registrations) {}

    public function students(): View
    {
        abort_if(auth()->user()->role === 'parent' || auth()->user()->role === 'student', 403);
        $students = Student::with(['user', 'course', 'academicSession']);
        if (auth()->user()->role === 'staff') {
            $students->where('course_id', auth()->user()->staffProfile?->course_id);
        }

        return view('academic.students', ['students' => $students->paginate(12), 'courses' => Course::orderBy('name')->get()]);
    }

    public function storeStudent(Request $request): RedirectResponse
    {
        $data = $request->validate(['first_name' => ['required', 'string', 'max:100'], 'last_name' => ['required', 'string', 'max:100'], 'course_id' => ['required', 'exists:courses,id'], 'gender' => ['nullable', 'in:M,F'], 'address' => ['nullable', 'string']]);
        $student = $this->registrations->register($data);

        return back()->with('success', "Student created as {$student->admission_number}. Login: {$student->user->email}");
    }

    public function destroyStudent(Student $student): RedirectResponse
    {
        $student->user()->delete();

        return back()->with('success', 'Student removed.');
    }

    public function courses(): View
    {
        return view('academic.courses', ['courses' => Course::withCount(['students', 'subjects'])->orderBy('name')->get()]);
    }

    public function storeCourse(Request $request): RedirectResponse
    {
        Course::create($request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9]+$/', 'unique:courses'],
            'next_student_serial' => ['required', 'integer', 'min:1', 'max:999999'],
        ]));

        return back()->with('success', 'Course created.');
    }

    public function destroyCourse(Course $course): RedirectResponse
    {
        $course->delete();

        return back()->with('success', 'Course removed.');
    }

    public function updateCourseSequence(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate(['next_student_serial' => ['required', 'integer', 'min:1', 'max:999999']]);
        $course->update($data);

        return back()->with('success', "The next {$course->code} student serial is ".str_pad((string) $course->next_student_serial, 3, '0', STR_PAD_LEFT).'.');
    }

    public function subjects(): View
    {
        abort_if(in_array(auth()->user()->role, ['parent', 'student']), 403);
        $subjects = Subject::with(['course', 'staff.user']);
        if (auth()->user()->role === 'staff') {
            $subjects->where('staff_id', auth()->user()->staffProfile?->id);
        }

        return view('academic.subjects', ['subjects' => $subjects->paginate(12)]);
    }

    public function results(): View
    {
        abort_if(auth()->user()->role === 'parent', 403);
        $results = StudentResult::with(['student.user', 'subject']);
        if (auth()->user()->role === 'student') {
            $results->where('student_id', auth()->user()->student?->id);
        }
        if (auth()->user()->role === 'staff') {
            $subjectIds = Subject::where('staff_id', auth()->user()->staffProfile?->id)->pluck('id');
            $results->whereIn('subject_id', $subjectIds);
        }

        return view('academic.results', ['results' => $results->paginate(15)]);
    }
}
