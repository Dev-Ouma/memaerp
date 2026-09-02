<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PDO;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $source = dirname(__DIR__, 3).'/mema_erp/db.sqlite3';
        if (file_exists($source)) {
            $sqlite = new PDO('sqlite:'.$source);
            $sqlite->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $rows = static fn (string $table): array => $sqlite->query("SELECT * FROM {$table}")->fetchAll();
            foreach ($rows('main_app_customuser') as $row) {
                DB::table('users')->insertOrIgnore(['id' => $row['id'], 'legacy_id' => $row['id'], 'name' => trim($row['first_name'].' '.$row['last_name']), 'first_name' => $row['first_name'], 'last_name' => $row['last_name'], 'email' => $row['email'], 'password' => Hash::make('password'), 'role' => match ((string) $row['user_type']) {
                    '1' => 'admin','2' => 'staff',default => 'student'
                }, 'gender' => $row['gender'] ?: null, 'address' => $row['address'] ?: null, 'profile_photo' => $row['profile_pic'] ?: null, 'is_active' => (bool) $row['is_active'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']]);
            }
            foreach ($rows('main_app_course') as $row) {
                DB::table('courses')->insertOrIgnore(['id' => $row['id'], 'name' => $row['name'], 'code' => 'CRS-'.str_pad((string) $row['id'], 3, '0', STR_PAD_LEFT), 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']]);
            }
            foreach ($rows('main_app_session') as $row) {
                DB::table('academic_sessions')->insertOrIgnore(['id' => $row['id'], 'start_date' => $row['start_year'], 'end_date' => $row['end_year'], 'created_at' => now(), 'updated_at' => now()]);
            }
            foreach ($rows('main_app_staff') as $row) {
                DB::table('staff')->insertOrIgnore(['id' => $row['id'], 'user_id' => $row['admin_id'], 'course_id' => $row['course_id'], 'created_at' => now(), 'updated_at' => now()]);
            }
            foreach ($rows('main_app_student') as $row) {
                DB::table('students')->insertOrIgnore(['id' => $row['id'], 'user_id' => $row['admin_id'], 'course_id' => $row['course_id'], 'academic_session_id' => $row['session_id'], 'admission_number' => 'MEM/LEGACY/'.str_pad((string) $row['id'], 4, '0', STR_PAD_LEFT), 'created_at' => now(), 'updated_at' => now()]);
            }
            foreach ($rows('main_app_subject') as $row) {
                DB::table('subjects')->insertOrIgnore(['id' => $row['id'], 'name' => $row['name'], 'code' => 'SUB-'.str_pad((string) $row['id'], 3, '0', STR_PAD_LEFT), 'staff_id' => $row['staff_id'], 'course_id' => $row['course_id'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']]);
            }
            foreach ($rows('main_app_attendance') as $row) {
                DB::table('attendances')->insertOrIgnore(['id' => $row['id'], 'academic_session_id' => $row['session_id'], 'subject_id' => $row['subject_id'], 'date' => $row['date'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']]);
            }
            foreach ($rows('main_app_attendancereport') as $row) {
                DB::table('attendance_records')->insertOrIgnore(['id' => $row['id'], 'attendance_id' => $row['attendance_id'], 'student_id' => $row['student_id'], 'present' => (bool) $row['status'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']]);
            }
            foreach ($rows('main_app_studentresult') as $row) {
                DB::table('student_results')->insertOrIgnore(['id' => $row['id'], 'student_id' => $row['student_id'], 'subject_id' => $row['subject_id'], 'test_score' => $row['test'], 'exam_score' => $row['exam'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']]);
            }
            foreach ($rows('main_app_book') as $row) {
                DB::table('books')->insertOrIgnore(['id' => $row['id'], 'title' => $row['name'], 'author' => $row['author'], 'isbn' => (string) $row['isbn'], 'category' => $row['category'], 'copies' => 1, 'created_at' => now(), 'updated_at' => now()]);
            }
            foreach (['users', 'courses', 'academic_sessions', 'staff', 'students', 'subjects', 'attendances', 'attendance_records', 'student_results', 'books'] as $table) {
                DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE(MAX(id), 1), MAX(id) IS NOT NULL) FROM {$table}");
            }
        }

        $this->call(StakeholderSeeder::class);
        $this->call(RbacCatalogueSeeder::class);
        $this->call(InstitutionalIdentitySeeder::class);
        $this->call(AdmissionModuleSeeder::class);
        $this->call(AdminSetupsCatalogueSeeder::class);
    }
}
