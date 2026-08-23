<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Sample data for local development and demonstrations: accounts, a programme with courses, an
 * applicant progressing through admission, and a student with registrations and marks.
 *
 * Kept strictly separate from {@see DatabaseSeeder}, which holds reference data that production
 * genuinely needs. Mixing the two is how demo students end up on a live transcript.
 *
 * Run with: php artisan db:seed --class=DemoSeeder
 */
final class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('DemoSeeder must never run in production.');
        }

        $this->call([
            DemoUserSeeder::class,
            CurriculumAndCourseSeeder::class,
            AdmissionsAndFinanceSeeder::class,
            StudentLifecycleSeeder::class,
        ]);
    }
}
