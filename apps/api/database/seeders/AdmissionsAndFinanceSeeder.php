<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Admission\Models\Application;
use App\Modules\Admission\Models\ProgrammeCutoff;
use App\Modules\Admission\Models\Prospect;
use App\Modules\Curriculum\Models\Programme;
use App\Modules\Finance\Models\FeeStructure;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\Payment;
use App\Modules\Institution\Models\AcademicYear;
use App\Modules\Institution\Models\Campus;
use App\Modules\Institution\Models\Institution;
use App\Modules\Institution\Models\Intake;
use App\Modules\Institution\Models\Term;
use App\Modules\Student\Models\Person;
use App\Modules\Student\Models\PersonIdentity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

final class AdmissionsAndFinanceSeeder extends Seeder
{
    public function run(): void
    {
        $institution = Institution::query()->where('code', 'MEMA')->firstOrFail();
        $mainCampus = Campus::query()->where('institution_id', $institution->id)->where('code', 'MAIN')->firstOrFail();
        $programme = Programme::query()->where('institution_id', $institution->id)->where('code', 'BSC-CS')->firstOrFail();
        $academicYear = AcademicYear::query()->where('institution_id', $institution->id)->where('is_current', true)->firstOrFail();
        $currentTerm = Term::query()->where('institution_id', $institution->id)->where('is_current', true)->firstOrFail();
        $intake = Intake::query()->where('institution_id', $institution->id)->where('code', 'SEP-2026')->first();

        ProgrammeCutoff::query()->updateOrCreate(
            [
                'institution_id' => $institution->id,
                'programme_id' => $programme->id,
                'academic_year_id' => $academicYear->id,
            ],
            [
                'minimum_score' => 66.00,
                'minimum_mean_grade' => 'B',
                'is_active' => true,
            ],
        );

        Prospect::query()->updateOrCreate(
            [
                'institution_id' => $institution->id,
                'email' => 'prospect.wanjiku@example.com',
            ],
            [
                'full_name' => 'Wanjiku Prospect',
                'phone' => '+254700111222',
                'source' => 'OPEN_DAY',
                'campaign_code' => 'OD-2026',
                'programme_interest_id' => $programme->id,
                'status' => 'NEW',
                'notes' => 'Met at Nairobi open day.',
            ],
        );

        // 1. Seed Fee Structure
        $feeStructure = FeeStructure::query()->firstOrCreate(
            [
                'institution_id' => $institution->id,
                'programme_id' => $programme->id,
                'academic_year_id' => $academicYear->id,
                'year_level' => 1,
                'semester' => 1,
            ],
            [
                'name' => 'BSc. Computer Science - Year 1 Semester 1 Fee',
                'tuition_fee' => 65000.00,
                'statutory_fees' => 15000.00,
                'total_amount' => 80000.00,
                'currency' => 'KES',
                'is_active' => true,
            ],
        );

        // 2. Seed Sample Prospective Students / Applicants
        $applicants = [
            [
                'given_name' => 'Faith',
                'middle_name' => 'Wanjiku',
                'family_name' => 'Mwangi',
                'email' => 'faith.mwangi@example.com',
                'phone' => '+254711000001',
                'national_id' => '36881920',
                'app_no' => 'APP-2026-00101',
                'status' => 'ACCEPTED',
                'mean_grade' => 'A',
                'score' => 81.50,
                'school' => 'Kenya High School',
                'fee_paid' => true,
            ],
            [
                'given_name' => 'Erick',
                'middle_name' => 'Otieno',
                'family_name' => 'Ochieng',
                'email' => 'erick.ochieng@example.com',
                'phone' => '+254711000002',
                'national_id' => '37119283',
                'app_no' => 'APP-2026-00102',
                'status' => 'ADMITTED',
                'mean_grade' => 'A-',
                'score' => 76.00,
                'school' => 'Alliance High School',
                'fee_paid' => true,
            ],
            [
                'given_name' => 'Kevin',
                'middle_name' => 'Cheruiyot',
                'family_name' => 'Kiprop',
                'email' => 'kevin.kiprop@example.com',
                'phone' => '+254711000003',
                'national_id' => '38920192',
                'app_no' => 'APP-2026-00103',
                'status' => 'SUBMITTED',
                'mean_grade' => 'B+',
                'score' => 69.50,
                'school' => 'Moi High School Kabarak',
                'fee_paid' => true,
            ],
        ];

        foreach ($applicants as $appData) {
            $person = Person::query()->firstOrCreate(
                [
                    'institution_id' => $institution->id,
                    'primary_email' => $appData['email'],
                ],
                [
                    'given_name' => $appData['given_name'],
                    'middle_name' => $appData['middle_name'],
                    'family_name' => $appData['family_name'],
                    'date_of_birth' => Carbon::parse('2005-05-15'),
                    'gender' => $appData['given_name'] === 'Faith' ? 'FEMALE' : 'MALE',
                    'nationality' => 'KE',
                    'national_id' => $appData['national_id'],
                    'primary_phone' => $appData['phone'],
                    'address' => ['city' => 'Nairobi', 'country' => 'Kenya'],
                ],
            );

            PersonIdentity::query()->firstOrCreate(
                [
                    'institution_id' => $institution->id,
                    'person_id' => $person->id,
                    'identity_type' => 'applicant',
                    'identifier' => $appData['app_no'],
                ],
                [
                    'status' => 'active',
                    'started_on' => Carbon::now()->subMonths(2),
                ],
            );

            $application = Application::query()->firstOrCreate(
                [
                    'institution_id' => $institution->id,
                    'application_number' => $appData['app_no'],
                ],
                [
                    'person_id' => $person->id,
                    'programme_id' => $programme->id,
                    'campus_id' => $mainCampus->id,
                    'academic_year_id' => $academicYear->id,
                    'intake_id' => $intake?->id,
                    'status' => $appData['status'],
                    'is_fee_paid' => $appData['fee_paid'],
                    'qualification_score' => $appData['score'],
                    'secondary_school_name' => $appData['school'],
                    'mean_grade' => $appData['mean_grade'],
                    'kcse_index_number' => 'IDX-'.$appData['national_id'],
                    'entry_path' => 'DIRECT',
                    'submitted_at' => $appData['status'] === 'DRAFT' ? null : Carbon::now()->subDays(14),
                    'offer_letter_ref' => 'ADM/2026/CS-'.substr($appData['app_no'], -4),
                    'offer_issued_at' => in_array($appData['status'], ['ADMITTED', 'ACCEPTED'], true) ? Carbon::now()->subDays(10) : null,
                    'offer_expires_at' => in_array($appData['status'], ['ADMITTED', 'ACCEPTED'], true) ? Carbon::now()->addDays(20) : null,
                    'offer_accepted_at' => $appData['status'] === 'ACCEPTED' ? Carbon::now()->subDays(2) : null,
                ],
            );

            // If Accepted, generate Fee Invoice and Payment
            if ($appData['status'] === 'ACCEPTED') {
                $invoice = Invoice::query()->firstOrCreate(
                    [
                        'institution_id' => $institution->id,
                        'invoice_number' => 'INV-2026-00042',
                    ],
                    [
                        'person_id' => $person->id,
                        'fee_structure_id' => $feeStructure->id,
                        'term_id' => $currentTerm->id,
                        'amount_due' => 80000.00,
                        'amount_paid' => 80000.00,
                        'balance' => 0.00,
                        'status' => 'FULLY_PAID',
                        'due_date' => Carbon::now()->addDays(30),
                    ],
                );

                Payment::query()->firstOrCreate(
                    [
                        'institution_id' => $institution->id,
                        'receipt_number' => 'RCT-2026-00089',
                    ],
                    [
                        'invoice_id' => $invoice->id,
                        'person_id' => $person->id,
                        'payment_method' => 'MPESA',
                        'transaction_reference' => 'SJK9281920X',
                        'amount' => 80000.00,
                        'currency' => 'KES',
                        'status' => 'COMPLETED',
                        'paid_at' => Carbon::now()->subDays(1),
                    ],
                );
            }
        }
    }
}
