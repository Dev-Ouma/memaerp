<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add soft deletes to schools if not present
        if (Schema::hasTable('schools') && ! Schema::hasColumn('schools', 'deleted_at')) {
            Schema::table('schools', function (Blueprint $table): void {
                $table->softDeletes();
            });
        }

        // 2. Create academic_departments table
        if (! Schema::hasTable('academic_departments')) {
            Schema::create('academic_departments', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 30)->unique();
                $table->string('name', 190);
                $table->string('school', 190)->nullable();
                $table->string('hod', 190)->nullable();
                $table->unsignedInteger('programmes_count')->default(0);
                $table->unsignedInteger('staff_count')->default(0);
                $table->string('email', 190)->nullable();
                $table->string('phone', 50)->nullable();
                $table->text('description')->nullable();
                $table->string('status', 30)->default('Active');
                $table->timestamps();
                $table->softDeletes();
            });

            // Seed initial departments
            $now = now();
            DB::table('academic_departments')->insert([
                [
                    'code' => 'DEPT-CS',
                    'name' => 'Department of Computer Science & Software Engineering',
                    'school' => 'School of Science and Technology',
                    'hod' => 'Dr. Amina Hassan',
                    'programmes_count' => 5,
                    'staff_count' => 24,
                    'email' => 'hod.cs@mema.ac.ke',
                    'phone' => '+254 700 112 001',
                    'description' => 'Software engineering, AI, cybersecurity, and computer systems.',
                    'status' => 'Active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'DEPT-MATH',
                    'name' => 'Department of Mathematics & Statistics',
                    'school' => 'School of Science and Technology',
                    'hod' => 'Dr. Kikete Wabuya',
                    'programmes_count' => 4,
                    'staff_count' => 19,
                    'email' => 'hod.math@mema.ac.ke',
                    'phone' => '+254 700 112 002',
                    'description' => 'Pure and applied mathematics, statistics, actuarial science.',
                    'status' => 'Active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'DEPT-ECON',
                    'name' => 'Department of Economics & Financial Studies',
                    'school' => 'School of Business and Economics',
                    'hod' => 'Dr. Daniel Otieno',
                    'programmes_count' => 6,
                    'staff_count' => 22,
                    'email' => 'hod.econ@mema.ac.ke',
                    'phone' => '+254 700 223 001',
                    'description' => 'Applied economics, econometrics, public finance, monetary policy.',
                    'status' => 'Active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'DEPT-EDUC',
                    'name' => 'Department of Educational Leadership & Curriculum',
                    'school' => 'School of Education',
                    'hod' => 'Dr. Grace Njeri',
                    'programmes_count' => 7,
                    'staff_count' => 28,
                    'email' => 'hod.education@mema.ac.ke',
                    'phone' => '+254 700 334 001',
                    'description' => 'Technology education, educational psychology, curriculum development.',
                    'status' => 'Active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'DEPT-AGRI',
                    'name' => 'Department of Agricultural Technology & Food Systems',
                    'school' => 'School of Agriculture and Natural Resources',
                    'hod' => 'Prof. Timothy Wafula',
                    'programmes_count' => 3,
                    'staff_count' => 16,
                    'email' => 'hod.agri@mema.ac.ke',
                    'phone' => '+254 700 445 001',
                    'description' => 'Agri-tech, post-harvest systems, environmental biotechnology.',
                    'status' => 'Active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        // 3. Create academic_programmes table
        if (! Schema::hasTable('academic_programmes')) {
            Schema::create('academic_programmes', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 40)->unique();
                $table->string('title', 190);
                $table->string('school', 190)->nullable();
                $table->string('department', 190)->nullable();
                $table->string('award', 190)->nullable();
                $table->string('cue_code', 60)->nullable();
                $table->string('level', 60)->default('Undergraduate');
                $table->unsignedInteger('duration_semesters')->default(8);
                $table->unsignedInteger('total_credits')->default(140);
                $table->text('description')->nullable();
                $table->string('status', 30)->default('Active');
                $table->timestamps();
                $table->softDeletes();
            });

            // Seed initial programmes
            $now = now();
            DB::table('academic_programmes')->insert([
                [
                    'code' => 'MEMA-BCS',
                    'title' => 'Bachelor of Science in Computer Science',
                    'school' => 'School of Science and Technology',
                    'department' => 'Department of Computer Science & Software Engineering',
                    'award' => 'B.Sc. (Computer Science)',
                    'cue_code' => 'CUE/PRG/042',
                    'level' => 'Undergraduate',
                    'duration_semesters' => 8,
                    'total_credits' => 168,
                    'description' => '4-year foundational and applied computer science degree.',
                    'status' => 'Active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'MEMA-MDS',
                    'title' => 'Master of Data Science',
                    'school' => 'School of Science and Technology',
                    'department' => 'Department of Mathematics & Statistics',
                    'award' => 'M.Sc. (Data Science)',
                    'cue_code' => 'CUE/PRG/089',
                    'level' => 'Postgraduate',
                    'duration_semesters' => 4,
                    'total_credits' => 64,
                    'description' => 'Advanced statistical modelling, machine learning, and big data architecture.',
                    'status' => 'Active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'MEMA-PHD-CS',
                    'title' => 'PhD in Computer Science',
                    'school' => 'School of Science and Technology',
                    'department' => 'Department of Computer Science & Software Engineering',
                    'award' => 'Ph.D. (Computer Science)',
                    'cue_code' => 'CUE/PRG/104',
                    'level' => 'Doctoral',
                    'duration_semesters' => 6,
                    'total_credits' => 120,
                    'description' => 'Doctoral dissertation and original research in computational sciences.',
                    'status' => 'Active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'MEMA-BBA',
                    'title' => 'Bachelor of Business Administration',
                    'school' => 'School of Business and Economics',
                    'department' => 'Department of Economics & Financial Studies',
                    'award' => 'B.B.A.',
                    'cue_code' => 'CUE/PRG/018',
                    'level' => 'Undergraduate',
                    'duration_semesters' => 8,
                    'total_credits' => 154,
                    'description' => 'Corporate finance, strategic leadership, and digital enterprise management.',
                    'status' => 'Active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        // 4. Create academic_course_units table
        if (! Schema::hasTable('academic_course_units')) {
            Schema::create('academic_course_units', function (Blueprint $table): void {
                $table->id();
                $table->string('unit_code', 30)->unique();
                $table->string('unit_title', 190);
                $table->string('department', 190)->nullable();
                $table->unsignedInteger('credit_hours')->default(3);
                $table->unsignedInteger('lecture_hours')->default(35);
                $table->unsignedInteger('practical_hours')->default(0);
                $table->string('classification', 60)->default('Core Unit');
                $table->string('prerequisites', 190)->default('None');
                $table->text('description')->nullable();
                $table->string('status', 30)->default('Active');
                $table->timestamps();
                $table->softDeletes();
            });

            // Seed initial course units
            $now = now();
            DB::table('academic_course_units')->insert([
                [
                    'unit_code' => 'CSC 101',
                    'unit_title' => 'Introduction to Computer Programming with Python',
                    'department' => 'Department of Computer Science & Software Engineering',
                    'credit_hours' => 3,
                    'lecture_hours' => 35,
                    'practical_hours' => 15,
                    'classification' => 'Core Unit',
                    'prerequisites' => 'None',
                    'description' => 'Fundamental programming concepts, data structures, algorithms with Python.',
                    'status' => 'Active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'unit_code' => 'MAT 102',
                    'unit_title' => 'Calculus for Computer Scientists & Engineers',
                    'department' => 'Department of Mathematics & Statistics',
                    'credit_hours' => 3,
                    'lecture_hours' => 45,
                    'practical_hours' => 0,
                    'classification' => 'Core Unit',
                    'prerequisites' => 'KCSE Mathematics C+',
                    'description' => 'Differential and integral calculus, series, multivariate approximations.',
                    'status' => 'Active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'unit_code' => 'CYB 301',
                    'unit_title' => 'Ethical Hacking & Network Penetration Testing',
                    'department' => 'Department of Computer Science & Software Engineering',
                    'credit_hours' => 4,
                    'lecture_hours' => 30,
                    'practical_hours' => 30,
                    'classification' => 'Elective Track Unit',
                    'prerequisites' => 'CSC 202 Data Communication',
                    'description' => 'Hands-on offensive security, threat emulation, reconnaissance, and vulnerability assessment.',
                    'status' => 'Active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'unit_code' => 'DSC 800',
                    'unit_title' => 'Postgraduate Research Project & Methodology',
                    'department' => 'Department of Mathematics & Statistics',
                    'credit_hours' => 6,
                    'lecture_hours' => 20,
                    'practical_hours' => 60,
                    'classification' => 'Postgraduate Thesis Unit',
                    'prerequisites' => '100% Coursework Pass',
                    'description' => 'Empirical research proposal, data collection, and master thesis write-up.',
                    'status' => 'Active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        // 5. Create cohort_academic_years table
        if (! Schema::hasTable('cohort_academic_years')) {
            Schema::create('cohort_academic_years', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 30)->unique(); // e.g. 2026/2027
                $table->string('code', 30)->unique();
                $table->date('start_date');
                $table->date('end_date');
                $table->string('status', 30)->default('Active'); // Active, Closed, Upcoming
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            // Seed initial academic years
            $now = now();
            DB::table('cohort_academic_years')->insert([
                [
                    'name' => '2026/2027 Academic Year',
                    'code' => 'AY-2026-2027',
                    'start_date' => '2026-09-01',
                    'end_date' => '2027-08-31',
                    'status' => 'Active',
                    'description' => 'Current active university academic cycle.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => '2025/2026 Academic Year',
                    'code' => 'AY-2025-2026',
                    'start_date' => '2025-09-01',
                    'end_date' => '2026-08-31',
                    'status' => 'Closed',
                    'description' => 'Completed academic calendar cycle.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => '2027/2028 Academic Year',
                    'code' => 'AY-2027-2028',
                    'start_date' => '2027-09-01',
                    'end_date' => '2028-08-31',
                    'status' => 'Upcoming',
                    'description' => 'Future planned academic calendar cycle.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        // 6. Ensure Recycle Bin module is seeded in module_states
        if (Schema::hasTable('module_states')) {
            DB::table('module_states')->updateOrInsert(
                ['module_key' => 'recycle-bin'],
                [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cohort_academic_years');
        Schema::dropIfExists('academic_course_units');
        Schema::dropIfExists('academic_programmes');
        Schema::dropIfExists('academic_departments');
    }
};
