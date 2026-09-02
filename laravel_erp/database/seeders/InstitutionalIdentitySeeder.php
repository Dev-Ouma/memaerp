<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use App\Services\AcademicYearService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class InstitutionalIdentitySeeder extends Seeder
{
    public function run(AcademicYearService $academicYears): void
    {
        DB::transaction(function () use ($academicYears): void {
            $codes = [
                'Computer Science & Engineering' => 'CS',
                'Electrical Engineering' => 'EE',
                'Mechanical Engineering' => 'ME',
                'IoT & Cyber Security' => 'ICS',
                'Data Science & Algorithms' => 'DS',
                'Civil Engineering' => 'CE',
                'Information Technology' => 'IT',
                'Diploma in Information Technology' => 'DIT',
            ];
            foreach ($codes as $name => $code) {
                Course::where('name', $name)->update(['code' => $code]);
            }

            $session = $academicYears->current();
            foreach (Course::orderBy('id')->get() as $course) {
                $serial = 1;
                foreach (Student::with('user')->where('course_id', $course->id)->orderBy('id')->get() as $student) {
                    $registration = sprintf('%s/%03d/%s', strtoupper($course->code), $serial, now()->format('Y'));
                    $student->update(['admission_number' => $registration, 'academic_session_id' => $session->id]);
                    $student->user->update(['email' => strtolower(str_replace('/', '', $registration)).'@student.mema.ac.ke']);
                    $serial++;
                }
                $course->update(['next_student_serial' => $serial]);
            }

            $reserved = [
                'admin@mema.test' => 'admin@mema.ac.ke',
                'teacher@mema.test' => 'teacher@mema.ac.ke',
                'parent@mema.test' => 'parent@mema.ac.ke',
            ];
            foreach ($reserved as $old => $new) {
                User::where('email', $old)->update(['email' => $new]);
            }
            foreach (User::whereIn('role', ['admin', 'staff'])->whereNotLike('email', '%@mema.ac.ke')->orderBy('id')->get() as $user) {
                $local = Str::slug($user->first_name.'.'.$user->last_name, '.');
                $email = $local.'@mema.ac.ke';
                if (User::where('email', $email)->whereKeyNot($user->id)->exists()) {
                    $email = $local.'.'.$user->id.'@mema.ac.ke';
                }
                $user->update(['email' => $email]);
            }
        });
    }
}
