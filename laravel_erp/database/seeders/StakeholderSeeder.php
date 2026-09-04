<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\Course;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class StakeholderSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::firstOrCreate(['code' => 'DIT'], ['name' => 'Diploma in Information Technology']);
        $session = AcademicSession::firstOrCreate(
            ['start_date' => '2025-09-01', 'end_date' => '2026-08-31'],
        );

        $admin = $this->account('admin@mema.ac.ke', 'Grace', 'Wanjiku', 'admin');
        $teacher = $this->account('teacher@mema.ac.ke', 'Daniel', 'Otieno', 'staff');
        $studentUser = $this->account('dit0012026@student.mema.ac.ke', 'Amina', 'Kamau', 'student');
        $parent = $this->account('parent@mema.ac.ke', 'Mary', 'Kamau', 'parent');

        // Named operational desks — RBAC roles are attached by RbacCatalogueSeeder.
        $this->account('registrar@mema.ac.ke', 'Godfrey', 'Ouma', 'staff', 'Academic Registrar');
        $this->account('admissions.officer@mema.ac.ke', 'Faith', 'Chebet', 'staff', 'Senior Admissions Officer');
        $this->account('finance.officer@mema.ac.ke', 'Amina', 'Hassan', 'staff', 'Finance Officer');
        $this->account('curriculum.manager@mema.ac.ke', 'Peter', 'Kariuki', 'staff', 'Curriculum Manager');
        $this->account('hr.officer@mema.ac.ke', 'Grace', 'Atieno', 'staff', 'HR Officer');
        $this->account('registration.officer@mema.ac.ke', 'John', 'Mwangi', 'staff', 'Registration Officer');
        $this->account('transfers.officer@mema.ac.ke', 'Lucy', 'Wambui', 'staff', 'Transfers Officer');
        $this->account('lms.manager@mema.ac.ke', 'Brian', 'Otieno', 'staff', 'LMS Manager');
        $this->account('graduation.officer@mema.ac.ke', 'Helen', 'Njeri', 'staff', 'Graduation Officer');
        $this->account('student.affairs@mema.ac.ke', 'Ian', 'Otieno', 'staff', 'Student Affairs Officer');

        $staff = Staff::updateOrCreate(['user_id' => $teacher->id], ['course_id' => $course->id]);
        $student = Student::updateOrCreate(
            ['user_id' => $studentUser->id],
            ['course_id' => $course->id, 'academic_session_id' => $session->id, 'admission_number' => 'DIT/001/2026'],
        );
        $parent->children()->syncWithoutDetaching([
            $student->id => ['relationship' => 'Mother', 'is_primary' => true],
        ]);

        $subject = Subject::updateOrCreate(
            ['course_id' => $course->id, 'code' => 'ICT-101'],
            ['name' => 'Introduction to Computing', 'staff_id' => $staff->id],
        );
        StudentResult::updateOrCreate(
            ['student_id' => $student->id, 'subject_id' => $subject->id],
            ['test_score' => 26, 'exam_score' => 58],
        );

        foreach ([true, true, true, false] as $daysAgo => $present) {
            $attendance = Attendance::updateOrCreate(
                ['subject_id' => $subject->id, 'date' => now()->subDays($daysAgo)->toDateString()],
                ['academic_session_id' => $session->id],
            );
            AttendanceRecord::updateOrCreate(
                ['attendance_id' => $attendance->id, 'student_id' => $student->id],
                ['present' => $present],
            );
        }
    }

    private function account(string $email, string $firstName, string $lastName, string $role, ?string $title = null): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => "{$firstName} {$lastName}",
                'first_name' => $firstName,
                'last_name' => $lastName,
                'password' => Hash::make('password'),
                'role' => $role,
                'title' => $title,
                'gender' => null,
                'is_active' => true,
            ],
        );
    }
}
