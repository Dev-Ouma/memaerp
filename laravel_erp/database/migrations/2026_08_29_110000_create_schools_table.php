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
        Schema::create('schools', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 190);
            $table->string('dean', 190)->nullable();
            $table->unsignedInteger('departments_count')->default(0);
            $table->unsignedInteger('programmes_count')->default(0);
            $table->string('email', 190)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('building', 120)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 30)->default('Active');
            $table->timestamps();
        });

        // Seed initial schools
        $now = now();
        DB::table('schools')->insert([
            [
                'code' => 'SCH-SST',
                'name' => 'School of Science and Technology',
                'dean' => 'Dr. Beth Kiratu',
                'departments_count' => 4,
                'programmes_count' => 14,
                'email' => 'dean.sst@mema.ac.ke',
                'phone' => '+254 700 112 233',
                'building' => 'Science Complex, Block A',
                'description' => 'Computing, Mathematics, Data Science, and Pure Sciences.',
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SCH-SBE',
                'name' => 'School of Business and Economics',
                'dean' => 'Prof. David Ndetei',
                'departments_count' => 3,
                'programmes_count' => 11,
                'email' => 'dean.sbe@mema.ac.ke',
                'phone' => '+254 700 223 344',
                'building' => 'Commerce Towers, Level 3',
                'description' => 'Business Administration, Economics, Finance, and Entrepreneurship.',
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SCH-SOE',
                'name' => 'School of Education',
                'dean' => 'Dr. Grace Njeri',
                'departments_count' => 3,
                'programmes_count' => 8,
                'email' => 'dean.soe@mema.ac.ke',
                'phone' => '+254 700 334 455',
                'building' => 'Education Wing, Block C',
                'description' => 'Technology Education, Educational Leadership, Curriculum Studies.',
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SCH-SANR',
                'name' => 'School of Agriculture and Natural Resources',
                'dean' => 'Prof. Timothy Wafula',
                'departments_count' => 2,
                'programmes_count' => 5,
                'email' => 'dean.sanr@mema.ac.ke',
                'phone' => '+254 700 445 566',
                'building' => 'Agri-Tech Pavilion',
                'description' => 'Agribusiness, Food Security, Environmental Systems.',
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SCH-SPGS',
                'name' => 'School of Postgraduate Studies (Directorate)',
                'dean' => 'Prof. Patrick Ouma',
                'departments_count' => 4,
                'programmes_count' => 12,
                'email' => 'dean.spgs@mema.ac.ke',
                'phone' => '+254 700 556 677',
                'building' => 'Postgraduate Directorate, 4th Floor',
                'description' => 'Masters, PhD, Postgraduate Diplomas and Research Supervision.',
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SCH-SHS',
                'name' => 'School of Health Sciences & Nursing',
                'dean' => 'Dr. Miriam Wanjiku',
                'departments_count' => 2,
                'programmes_count' => 6,
                'email' => 'dean.shs@mema.ac.ke',
                'phone' => '+254 700 667 788',
                'building' => 'Health Sciences Complex',
                'description' => 'Public Health, Nursing, Health Informatics.',
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
