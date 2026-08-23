<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Admission\Models\Application;
use App\Modules\Course\Models\CourseOffering;
use App\Modules\Enrollment\Models\CourseEnrollment;
use App\Modules\Enrollment\Models\TermRegistration;
use App\Modules\Examination\Models\StudentMark;
use App\Modules\Examination\Models\TermGpa;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Institution;
use App\Modules\Institution\Models\Term;
use App\Modules\Student\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

final class StudentLifecycleSeeder extends Seeder
{
    public function run(): void
    {
        $institution = Institution::query()->where('code', 'MEMA')->firstOrFail();
        $mainCampus = Campus::query()->where('institution_id', $institution->id)->where('code', 'MAIN')->firstOrFail();
        $academicYear = AcademicYear::query()->where('institution_id', $institution->id)->where('is_current', true)->firstOrFail();
        $currentTerm = Term::query()->where('institution_id', $institution->id)->where('is_current', true)->firstOrFail();
        $adminUser = User::query()->where('email', 'admin@mema.ac.ke')->firstOrFail();

        // Get accepted applicant (Faith Mwangi)
        $acceptedApp = Application::query()
            ->where('institution_id', $institution->id)
            ->where('status', 'ACCEPTED')
            ->firstOrFail();

        // 1. Matriculate into Student record
        $student = Student::query()->firstOrCreate(
            [
                'institution_id' => $institution->id,
                'student_number' => 'MEMA/BSC-CS/2026/001',
            ],
            [
                'person_id' => $acceptedApp->person_id,
                'programme_id' => $acceptedApp->programme_id,
                'campus_id' => $mainCampus->id,
                'admission_year_id' => $academicYear->id,
                'current_year_level' => 1,
                'current_semester' => 1,
                'academic_standing' => 'GOOD_STANDING',
                'status' => 'ACTIVE',
                'matriculated_on' => Carbon::now()->subDays(30),
            ]
        );

        // Update application status to MATRICULATED
        $acceptedApp->update(['status' => 'MATRICULATED']);

        // 2. Register for current term
        $termReg = TermRegistration::query()->firstOrCreate(
            [
                'institution_id' => $institution->id,
                'student_id' => $student->id,
                'term_id' => $currentTerm->id,
            ],
            [
                'year_level' => 1,
                'semester' => 1,
                'financial_clearance_status' => true,
                'status' => 'REGISTERED',
                'registered_at' => Carbon::now()->subDays(20),
            ]
        );

        // 3. Enroll in 4 active course offerings
        $offerings = CourseOffering::query()
            ->with('course')
            ->where('institution_id', $institution->id)
            ->where('term_id', $currentTerm->id)
            ->where('campus_id', $mainCampus->id)
            ->get();

        // Marks data for each course
        $marksData = [
            ['cat' => 24.50, 'exam' => 52.00, 'grade' => 'B+', 'gp' => 3.50],
            ['cat' => 27.00, 'exam' => 61.00, 'grade' => 'A',  'gp' => 4.00],
            ['cat' => 22.00, 'exam' => 45.00, 'grade' => 'B',  'gp' => 3.00],
            ['cat' => 25.00, 'exam' => 50.00, 'grade' => 'B+', 'gp' => 3.50],
        ];

        $totalCreditsAttempted = 0;
        $totalCreditsEarned = 0;
        $totalGradePoints = 0;

        foreach ($offerings as $i => $offering) {
            // Increment enrolled count on offering
            $offering->increment('enrolled_count');

            $enrollment = CourseEnrollment::query()->firstOrCreate(
                [
                    'institution_id' => $institution->id,
                    'student_id' => $student->id,
                    'course_offering_id' => $offering->id,
                ],
                [
                    'term_registration_id' => $termReg->id,
                    'status' => 'ENROLLED',
                    'is_retake' => false,
                    'enrolled_at' => Carbon::now()->subDays(18),
                ]
            );

            // Enter marks
            $md = $marksData[$i] ?? $marksData[0];
            StudentMark::query()->firstOrCreate(
                [
                    'institution_id' => $institution->id,
                    'course_enrollment_id' => $enrollment->id,
                ],
                [
                    'cat_score' => $md['cat'],
                    'exam_score' => $md['exam'],
                    'total_score' => $md['cat'] + $md['exam'],
                    'letter_grade' => $md['grade'],
                    'grade_points' => $md['gp'],
                    'is_submitted' => true,
                    'submitted_by' => $adminUser->id,
                    'approval_status' => 'SENATE_RATIFIED',
                ]
            );

            $credits = $offering->course->credits;
            $totalCreditsAttempted += $credits;
            $totalCreditsEarned += $credits;
            $totalGradePoints += ($md['gp'] * $credits);
        }

        // 4. Compute Term GPA
        $gpa = $totalCreditsAttempted > 0 ? round($totalGradePoints / $totalCreditsAttempted, 2) : 0.00;

        TermGpa::query()->firstOrCreate(
            [
                'institution_id' => $institution->id,
                'student_id' => $student->id,
                'term_id' => $currentTerm->id,
            ],
            [
                'credits_attempted' => $totalCreditsAttempted,
                'credits_earned' => $totalCreditsEarned,
                'gpa' => $gpa,
                'cgpa' => $gpa, // First term so CGPA = GPA
                'academic_standing' => $gpa >= 2.0 ? 'GOOD_STANDING' : 'PROBATION',
            ]
        );
    }
}
