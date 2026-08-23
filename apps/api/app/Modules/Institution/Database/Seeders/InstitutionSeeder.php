<?php

declare(strict_types=1);

namespace App\Modules\Institution\Database\Seeders;

use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Department;
use App\Modules\Institution\Models\Faculty;
use App\Modules\Institution\Models\GradeBand;
use App\Modules\Institution\Models\GradingScale;
use App\Modules\Institution\Models\Institution;
use App\Modules\Institution\Models\Intake;
use App\Modules\Institution\Models\MasterLookup;
use App\Modules\Institution\Models\StudyMode;
use App\Modules\Institution\Models\Term;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

/**
 * Bootstraps Mema University: the institution row, its structure, the current academic year and
 * its terms, and the grading scale in force.
 *
 * The structure below is PLACEHOLDER pending decision D-005 (institutional scale) — real
 * faculties, departments and campuses come from the client. What is NOT placeholder is the
 * shape: effective-dated grading scale, terms carrying their own windows, everything hanging off
 * one institution row.
 */
final class InstitutionSeeder extends Seeder
{
    public function run(): void
    {
        $institution = Institution::query()->updateOrCreate(
            ['code' => 'MEMA'],
            [
                'name' => 'Mema University',
                'legal_name' => 'Mema University',
                'domain' => 'mema.ac.ke',
                'branding' => [
                    // Tokenised so a rebrand is a data change, not a front-end refactor (D-013).
                    'primary' => '#0A3E50',
                    'accent' => '#1E8449',
                ],
                'contact' => [
                    'email' => 'info@mema.ac.ke',
                    'phone' => '+254700000000',
                ],
                'is_active' => true,
            ],
        );

        $mainCampus = Campus::query()->updateOrCreate(
            ['institution_id' => $institution->id, 'code' => 'MAIN'],
            [
                'name' => 'Main Campus', 'town' => 'Nairobi', 'is_main_campus' => true,
                'is_active' => true, 'status' => 'ACTIVE', 'resolution_reference' => 'BOOTSTRAP-001',
            ],
        );

        $structure = [
            'FSCI' => [
                'name' => 'Faculty of Science and Technology',
                'departments' => [
                    'CS' => 'Department of Computer Science',
                    'MATH' => 'Department of Mathematics',
                    'BIO' => 'Department of Biological Sciences',
                ],
            ],
            'FBUS' => [
                'name' => 'Faculty of Business and Economics',
                'departments' => [
                    'ACC' => 'Department of Accounting and Finance',
                    'MGT' => 'Department of Management',
                ],
            ],
            'FEDU' => [
                'name' => 'Faculty of Education',
                'departments' => [
                    'EDF' => 'Department of Educational Foundations',
                ],
            ],
            'FHEA' => [
                'name' => 'Faculty of Health Sciences',
                'departments' => [
                    'NUR' => 'Department of Nursing',
                    'PUB' => 'Department of Public Health',
                ],
            ],
        ];

        foreach ($structure as $facultyCode => $faculty) {
            $facultyModel = Faculty::query()->updateOrCreate(
                ['institution_id' => $institution->id, 'code' => $facultyCode],
                [
                    'campus_id' => $mainCampus->id,
                    'name' => $faculty['name'],
                    'is_active' => true,
                    'status' => 'ACTIVE',
                    'resolution_reference' => 'BOOTSTRAP-001',
                ],
            );

            foreach ($faculty['departments'] as $departmentCode => $departmentName) {
                Department::query()->updateOrCreate(
                    ['institution_id' => $institution->id, 'code' => $departmentCode],
                    [
                        'faculty_id' => $facultyModel->id,
                        'name' => $departmentName,
                        'cost_centre' => $departmentCode,
                        'is_active' => true,
                        'status' => 'ACTIVE',
                        'resolution_reference' => 'BOOTSTRAP-001',
                    ],
                );
            }
        }

        $this->seedMasterData($institution);
        $this->seedCalendar($institution);
        $this->seedGradingScale($institution);

        $this->command->info('Mema University structure seeded.');
    }

    private function seedCalendar(Institution $institution): void
    {
        $year = AcademicYear::query()->updateOrCreate(
            ['institution_id' => $institution->id, 'code' => '2026/2027'],
            [
                'name' => 'Academic Year 2026/2027',
                'starts_on' => '2026-09-01',
                'ends_on' => '2027-08-31',
                'is_current' => true,
                'status' => 'ACTIVE',
                'senate_resolution_reference' => 'SENATE-2026-001',
                'senate_approved_at' => '2026-06-30 12:00:00',
                'published_at' => '2026-07-01 08:00:00',
            ],
        );

        $terms = [
            [
                'code' => '2026/2027-S1',
                'name' => 'Semester 1',
                'sequence' => 1,
                'starts_on' => '2026-09-07',
                'ends_on' => '2026-12-18',
                'registration_opens_at' => '2026-08-24 08:00:00',
                'registration_closes_at' => '2026-09-18 23:59:59',
                'add_drop_closes_at' => '2026-09-25 23:59:59',
                'marks_entry_opens_at' => '2026-11-30 08:00:00',
                'marks_entry_closes_at' => '2027-01-09 23:59:59',
                'study_mode_code' => 'FULL_TIME',
                'term_type' => 'SEMESTER',
                'status' => 'ACTIVE',
                'published_at' => '2026-07-01 08:00:00',
                'is_current' => true,
            ],
            [
                'code' => '2026/2027-S2',
                'name' => 'Semester 2',
                'sequence' => 2,
                'starts_on' => '2027-01-18',
                'ends_on' => '2027-04-30',
                'registration_opens_at' => '2027-01-04 08:00:00',
                'registration_closes_at' => '2027-01-29 23:59:59',
                'add_drop_closes_at' => '2027-02-05 23:59:59',
                'marks_entry_opens_at' => '2027-04-12 08:00:00',
                'marks_entry_closes_at' => '2027-05-21 23:59:59',
                'study_mode_code' => 'FULL_TIME',
                'term_type' => 'SEMESTER',
                'status' => 'DRAFT',
                'is_current' => false,
            ],
        ];

        foreach ($terms as $term) {
            Term::query()->updateOrCreate(
                ['institution_id' => $institution->id, 'code' => $term['code']],
                [...$term, 'academic_year_id' => $year->id],
            );
        }

        Intake::query()->updateOrCreate(
            ['institution_id' => $institution->id, 'code' => 'SEP-2026'],
            [
                'academic_year_id' => $year->id,
                'name' => 'September 2026 Intake',
                'opens_on' => '2026-01-05',
                'closes_on' => '2026-08-14',
                'reporting_on' => '2026-09-01',
                'status' => 'ACTIVE',
            ],
        );
    }

    private function seedMasterData(Institution $institution): void
    {
        foreach ([
            ['FULL_TIME', 'Full-Time'], ['PART_TIME', 'Part-Time'], ['EVENING', 'Evening'],
            ['WEEKEND', 'Weekend'], ['ODEL', 'Open, Distance and e-Learning'],
        ] as [$code, $name]) {
            StudyMode::query()->updateOrCreate(
                ['institution_id' => $institution->id, 'code' => $code],
                ['name' => $name, 'is_active' => true],
            );
        }

        $lookups = [
            'NATIONALITY' => [['KE', 'Kenyan'], ['UG', 'Ugandan'], ['TZ', 'Tanzanian']],
            'COUNTY' => [['001', 'Mombasa'], ['047', 'Nairobi']],
            'CURRENCY' => [['KES', 'Kenyan Shilling'], ['USD', 'US Dollar']],
            'PAYMENT_METHOD' => [['MPESA', 'M-Pesa'], ['BANK', 'Bank Transfer'], ['CARD', 'Card']],
            'BANK' => [['KCB', 'KCB Bank Kenya'], ['EQUITY', 'Equity Bank Kenya']],
            'STUDENT_CATEGORY' => [['GOK', 'Government Sponsored'], ['PSSP', 'Privately Sponsored']],
        ];

        foreach ($lookups as $type => $values) {
            foreach ($values as $order => [$code, $name]) {
                MasterLookup::query()->updateOrCreate(
                    ['institution_id' => $institution->id, 'type' => $type, 'code' => $code],
                    ['name' => $name, 'display_order' => $order, 'is_active' => true],
                );
            }
        }
    }

    /**
     * The grading scale in force. Placeholder pending decision D-009 — the client must supply
     * the real boundaries AND the historical versions, because a 2015 transcript has to reproduce
     * under 2015's rules. That is why this is effective-dated rather than a settings row.
     */
    private function seedGradingScale(Institution $institution): void
    {
        $scale = GradingScale::query()->updateOrCreate(
            [
                'institution_id' => $institution->id,
                'code' => 'UG-STANDARD',
                'effective_from' => CarbonImmutable::parse('2026-09-01'),
            ],
            ['name' => 'Undergraduate Standard Scale (2026-)', 'effective_to' => null],
        );

        $bands = [
            ['letter' => 'A', 'min_mark' => 70, 'max_mark' => 100, 'grade_point' => 4.00, 'is_pass' => true],
            ['letter' => 'B', 'min_mark' => 60, 'max_mark' => 69.99, 'grade_point' => 3.00, 'is_pass' => true],
            ['letter' => 'C', 'min_mark' => 50, 'max_mark' => 59.99, 'grade_point' => 2.00, 'is_pass' => true],
            ['letter' => 'D', 'min_mark' => 40, 'max_mark' => 49.99, 'grade_point' => 1.00, 'is_pass' => true],
            ['letter' => 'E', 'min_mark' => 0, 'max_mark' => 39.99, 'grade_point' => 0.00, 'is_pass' => false],
        ];

        foreach ($bands as $band) {
            GradeBand::query()->updateOrCreate(
                ['grading_scale_id' => $scale->id, 'letter' => $band['letter']],
                $band,
            );
        }
    }
}
