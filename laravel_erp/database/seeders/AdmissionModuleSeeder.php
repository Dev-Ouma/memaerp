<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AdmissionIntake;
use App\Models\Course;
use App\Models\ProgrammeOffering;
use Illuminate\Database\Seeder;

final class AdmissionModuleSeeder extends Seeder
{
    public function run(): void
    {
        $intake = AdmissionIntake::updateOrCreate(
            ['code' => 'SEP-2026'],
            ['name' => 'September 2026 Intake', 'opens_at' => '2026-06-01', 'closes_at' => '2026-09-20', 'acceptance_deadline' => '2026-09-30', 'is_published' => true],
        );

        Course::query()->orderBy('name')->each(function (Course $course) use ($intake): void {
            ProgrammeOffering::updateOrCreate(
                ['course_id' => $course->id, 'admission_intake_id' => $intake->id, 'campus' => 'Main Campus', 'study_mode' => 'Full-time'],
                ['capacity' => 60, 'application_fee' => 1000, 'requirements' => 'KCSE certificate or equivalent, identity document and one recent passport photograph. Programme-specific evidence may be requested during review.', 'is_published' => true],
            );
        });
    }
}
