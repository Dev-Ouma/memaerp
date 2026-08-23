<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Course\Models\Course;
use App\Modules\Course\Models\CourseOffering;
use App\Modules\Course\Models\CoursePrerequisite;
use App\Modules\Curriculum\Models\CurriculumCourse;
use App\Modules\Curriculum\Models\CurriculumVersion;
use App\Modules\Curriculum\Models\Programme;
use App\Modules\Iam\Models\User;
use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Department;
use App\Modules\Institution\Models\Institution;
use App\Modules\Institution\Models\Term;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

final class CurriculumAndCourseSeeder extends Seeder
{
    public function run(): void
    {
        $institution = Institution::query()->where('code', 'MEMA')->firstOrFail();
        $csDept = Department::query()->where('institution_id', $institution->id)->where('code', 'CS')->firstOrFail();
        $mainCampus = Campus::query()->where('institution_id', $institution->id)->where('code', 'MAIN')->firstOrFail();
        $currentYear = AcademicYear::query()->where('institution_id', $institution->id)->where('is_current', true)->firstOrFail();
        $currentTerm = Term::query()->where('institution_id', $institution->id)->where('is_current', true)->firstOrFail();
        $adminUser = User::query()->where('institution_id', $institution->id)->where('email', 'admin@mema.ac.ke')->firstOrFail();

        // 1. Seed Programme
        $programme = Programme::query()->firstOrCreate(
            [
                'institution_id' => $institution->id,
                'code' => 'BSC-CS',
            ],
            [
                'department_id' => $csDept->id,
                'name' => 'Bachelor of Science in Computer Science',
                'award_level' => 'BACHELORS',
                'duration_years' => 4,
                'total_credits_required' => 128,
                'is_active' => true,
            ]
        );

        // 2. Seed Curriculum Version
        $version = CurriculumVersion::query()->firstOrCreate(
            [
                'institution_id' => $institution->id,
                'programme_id' => $programme->id,
                'version_code' => '2026-V1',
            ],
            [
                'effective_year_id' => $currentYear->id,
                'senate_approval_ref' => 'SEN/2026/RES-104',
                'is_approved' => true,
                'approved_at' => Carbon::now(),
            ]
        );

        // 3. Seed Master Courses
        $coursesData = [
            ['code' => 'CSC 101', 'title' => 'Introduction to Computer Systems', 'credits' => 3, 'lecture_hours' => 3, 'lab_hours' => 2],
            ['code' => 'CSC 102', 'title' => 'Structured Programming & Problem Solving', 'credits' => 4, 'lecture_hours' => 3, 'lab_hours' => 3],
            ['code' => 'CSC 201', 'title' => 'Data Structures and Algorithms', 'credits' => 4, 'lecture_hours' => 3, 'lab_hours' => 3],
            ['code' => 'CSC 202', 'title' => 'Database Systems & SQL Modeling', 'credits' => 3, 'lecture_hours' => 2, 'lab_hours' => 3],
            ['code' => 'MAT 101', 'title' => 'Differential and Integral Calculus I', 'credits' => 3, 'lecture_hours' => 3, 'lab_hours' => 0],
            ['code' => 'MAT 102', 'title' => 'Discrete Structures for Computing', 'credits' => 3, 'lecture_hours' => 3, 'lab_hours' => 0],
        ];

        $courseMap = [];
        foreach ($coursesData as $c) {
            $courseMap[$c['code']] = Course::query()->firstOrCreate(
                [
                    'institution_id' => $institution->id,
                    'code' => $c['code'],
                ],
                array_merge($c, ['department_id' => $csDept->id, 'is_active' => true])
            );
        }

        // 4. Seed Prerequisites
        CoursePrerequisite::query()->firstOrCreate([
            'course_id' => $courseMap['CSC 102']->id,
            'prerequisite_course_id' => $courseMap['CSC 101']->id,
            'requirement_type' => 'PREREQUISITE',
        ]);

        CoursePrerequisite::query()->firstOrCreate([
            'course_id' => $courseMap['CSC 201']->id,
            'prerequisite_course_id' => $courseMap['CSC 102']->id,
            'requirement_type' => 'PREREQUISITE',
        ]);

        // 5. Map into Curriculum Grid
        $curriculumMap = [
            ['course' => 'CSC 101', 'year' => 1, 'sem' => 1, 'type' => 'CORE'],
            ['course' => 'MAT 101', 'year' => 1, 'sem' => 1, 'type' => 'CORE'],
            ['course' => 'CSC 102', 'year' => 1, 'sem' => 2, 'type' => 'CORE'],
            ['course' => 'MAT 102', 'year' => 1, 'sem' => 2, 'type' => 'CORE'],
            ['course' => 'CSC 201', 'year' => 2, 'sem' => 1, 'type' => 'CORE'],
            ['course' => 'CSC 202', 'year' => 2, 'sem' => 1, 'type' => 'CORE'],
        ];

        foreach ($curriculumMap as $item) {
            CurriculumCourse::query()->firstOrCreate([
                'curriculum_version_id' => $version->id,
                'course_id' => $courseMap[$item['course']]->id,
            ], [
                'year_level' => $item['year'],
                'semester' => $item['sem'],
                'course_type' => $item['type'],
            ]);
        }

        // 6. Seed Course Offerings for Current Term
        $offeredCodes = ['CSC 101', 'MAT 101', 'CSC 201', 'CSC 202'];
        foreach ($offeredCodes as $code) {
            CourseOffering::query()->firstOrCreate([
                'institution_id' => $institution->id,
                'course_id' => $courseMap[$code]->id,
                'term_id' => $currentTerm->id,
                'campus_id' => $mainCampus->id,
                'section_code' => 'A',
            ], [
                'lecturer_id' => $adminUser->id,
                'max_capacity' => 75,
                'enrolled_count' => 0,
                'delivery_mode' => 'IN_PERSON',
                'is_open_for_enrollment' => true,
            ]);
        }
    }
}
